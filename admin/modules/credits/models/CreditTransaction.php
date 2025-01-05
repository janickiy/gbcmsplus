<?php

namespace admin\modules\credits\models;

use admin\modules\credits\events\CreditExternalPaymentEvent;
use admin\modules\credits\models\credit\CreditClose;
use LogicException;
use mcms\common\traits\Translate;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use rgk\utils\exceptions\ModelNotSavedException;
use Yii;
use rgk\utils\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "credit_transactions".
 *
 * @property integer $id
 * @property integer $external_id MGMP ID
 * @property integer $credit_id
 * @property number $amount
 * @property integer $type
 * @property string $fee_date Дата за которую списана комиссия
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Credit $credit
 * @property UserBalanceInvoice $invoice
 */
class CreditTransaction extends \yii\db\ActiveRecord
{
    use Translate;

    const LANG_PREFIX = 'credits.credit_transaction.';

    /** @const int Зачисление кредитных средств на баланс реселлера */
    const TYPE_ACCRUE_AMOUNT = 1;
    /** @var int Списание ежемесячной кредитной комиссии */
    const TYPE_MONTHLY_FEE = 2;
    /** @var int Частичое погашение кредита в виде ручной оплаты */
    const TYPE_MANUAL_PAYMENT = 3;
    /** @var int Частичое погашение кредита в виде списания с баланса реса */
    const TYPE_BALANCE_PAYMENT = 4;
    /** @const int[] Все типы */
    const TYPES = [self::TYPE_MONTHLY_FEE, self::TYPE_ACCRUE_AMOUNT, self::TYPE_MANUAL_PAYMENT, self::TYPE_BALANCE_PAYMENT];
    /** типы транзакций, которые влияют на кредитный баланс (по идее все кроме списаний процентов за пользование кредитом) */
    const CREDIT_BALANCE_TYPES = [self::TYPE_ACCRUE_AMOUNT, self::TYPE_MANUAL_PAYMENT, self::TYPE_BALANCE_PAYMENT];
    /** типы транзакций по выплатам. Ручные и "из баланса" */
    const PAYMENT_TYPES = [self::TYPE_MANUAL_PAYMENT, self::TYPE_BALANCE_PAYMENT];
    /** @const string[] Типы транзакций комиссии */
    const FEE_TYPES = [self::TYPE_MONTHLY_FEE];
    /**
     * @var array кэш
     */
    protected static $mappedTypes;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'credit_transactions';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'skipOnChanged' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['external_id', 'credit_id', 'created_at', 'updated_at'], 'integer'],
            [['credit_id', 'amount'], 'required'],
            [['amount'], 'number'],
        ];
    }

    /**
     * Инвойсы транзакций.
     * Здесь должны быть перечислены типы транзакций для которых будут созданы инвойсы.
     * $transactionType => [ // Тип транзакции
     *    'type' => $invoiceType, // Тип инвойса
     *    'isPositive' => $invoiceIsPositive, // Положительный или отрицательный инвойс
     * ]
     */
    private function invoicesParams()
    {
        return [
            static::TYPE_ACCRUE_AMOUNT => [
                'type' => UserBalanceInvoice::TYPE_CREDIT_ACCRUE_AMOUNT,
                'isPositive' => true,
            ],
            static::TYPE_MONTHLY_FEE => [
                'type' => UserBalanceInvoice::TYPE_CREDIT_MONTHLY_FEE,
                'isPositive' => false,
            ],
            static::TYPE_BALANCE_PAYMENT => [
                'type' => UserBalanceInvoice::TYPE_CREDIT_BALANCE_PAYMENT,
                'isPositive' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return static::translateAttributeLabels([
            'id',
            'credit_id',
            'amount',
            'currency',
            'created_at',
            'updated_at',
            'type',
        ]);
    }

    /**
     * @return ActiveQuery
     */
    public function getCredit()
    {
        return $this->hasOne(Credit::class, ['id' => 'credit_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(UserBalanceInvoice::class, ['id' => 'invoice_id'])
            ->viaTable('credit_transactions_invoices', ['transaction_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return ArrayHelper::getValue(self::getMappedTypes(), $this->type);
    }

    /**
     * @param array $types
     * @return array
     */
    public static function getMappedTypes($types = [])
    {
        if (!self::$mappedTypes) {
            self::$mappedTypes = ArrayHelper::map(self::typeNameList([], $types), 'id', 'name');
        }

        return self::$mappedTypes;
    }

    /**
     * @param array $exclude Исключить типы
     * @param array $filter Отфильтровать по типам
     * @return array
     */
    public static function typeNameList($exclude = [], $filter = [])
    {
        $types = [
            [
                'id' => self::TYPE_ACCRUE_AMOUNT,
                'name' => static::t('type_accrue_amount'),
            ],
            [
                'id' => self::TYPE_MONTHLY_FEE,
                'name' => static::t('type_monthly_fee'),
            ],
            [
                'id' => self::TYPE_MANUAL_PAYMENT,
                'name' => static::t('type_manual_payment'),
            ],
            [
                'id' => self::TYPE_BALANCE_PAYMENT,
                'name' => static::t('type_balance_payment'),
            ],
        ];

        return array_filter($types, function ($element) use ($exclude, $filter) {
            if (in_array($element['id'], $exclude)) {
                return false;
            }
            if ($filter && !in_array($element['id'], $filter)) {
                return false;
            }
            return true;
        });
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$this->isNewRecord && $this->isAttributeChanged('type', false)) {
            throw new LogicException('Сохранение и синхронизация инвойсов не поддерживает удаление инвойсов');
        }

        // TODO Придумать можно ли это делать в before/afterSave, так как это не сработает при прямом вызове insert/update
        $isNewRecord = $this->isNewRecord;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Транзакция
            if (!parent::save($runValidation, $attributeNames)) {
                throw new ModelNotSavedException('Не удалось сохранить выплату');
            }

            // Инвойс
            if (!$this->saveInvoice()) {
                throw new ModelNotSavedException('Не удалось сохранить инвойс');
            }

            // Закрытие кредита
            $creditClose = new CreditClose($this->credit);
            if ($creditClose->isAvailable() && !$creditClose->execute()) {
                throw new ModelNotSavedException('Не удалось закрыть кредит');
            }

            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return false;
        }

        // Уведомление о добавлении выплаты из MGMP
        if ($isNewRecord && $this->external_id && in_array($this->type, static::PAYMENT_TYPES)) {
            (new CreditExternalPaymentEvent($this))->trigger();
        }

        return true;
    }

    /**
     * Сохранить инвойс.
     * Если инвойса нет, будет создан.
     *
     * @return bool
     * true - инвойс успешно создан или инвойс не требуется
     * false - не удалось создать инвойс
     */
    private function saveInvoice()
    {
        if ($this->amount < 0) {
            throw new LogicException('Отрицательные транзакции не поддерживаются');
        }

        $invoiceParams = ArrayHelper::getValue($this->invoicesParams(), $this->type);
        if (!$invoiceParams) {
            return true; // Транзакция не требует создания инвойса
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $invoice = $this->invoice ?: new UserBalanceInvoice;
            $isNewInvoice = $invoice->isNewRecord;
            $invoice->user_id = UserPayment::getResellerId();
            $invoice->type = $invoiceParams['type'];
            $invoice->currency = $this->credit->currency;
            $invoice->amount = $invoiceParams['isPositive'] ? $this->amount : -$this->amount;
            $invoice->date = new Expression('CURDATE()');
            if (!$invoice->save()) {
                throw new ModelNotSavedException('Не удалось сохранить инвойс');
            }

            if ($isNewInvoice) {
                $this->link('invoice', $invoice);
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return false;
        }

        return true;
    }
}
