<?php

namespace admin\modules\credits\models;

use admin\modules\credits\events\CreditApprovedEvent;
use admin\modules\credits\events\CreditDoneEvent;
use admin\modules\credits\events\CreditDeclinedEvent;
use mcms\common\mgmp\MgmpClient;
use mcms\common\traits\Translate;
use mcms\payments\components\UserBalance;
use mcms\payments\models\UserPayment;
use mcms\user\models\User;
use Yii;
use rgk\utils\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Кредит
 *
 * @property integer $id
 * @property integer $external_id MGMP ID
 * @property integer $user_id
 * @property number $amount
 * @property string $currency
 * @property integer $status
 * @property string $percent
 * @property string $decline_reason Причина отклонения
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $activated_at
 * @property integer $closed_at
 *
 * @property CreditTransaction[] $transactions
 * @property User $user
 */
class Credit extends \yii\db\ActiveRecord
{
    use Translate;

    const LANG_PREFIX = 'credits.credit.';
    const CREDIT_SETTINGS_CACHE_KEY = 'CREDIT_SETTINGS';

    // TRICKY ID статусов должны совпадать с МП, иначе будут проблемы при синхронизации
    /** @const int Кредит в ожидании одобрения или активации */
    const STATUS_REQUESTED = 1;
    /** @const int Кредит одобрен, ожидается активация.
     * TRICKY Статус не используется в МКМС и сделан только для того, что бы было проще синхронизировать код */
//  const STATUS_APPROVED = 5;
    /** @const int Кредит активирован */
    const STATUS_ACTIVE = 2;
    /** @const int Кредит отклонен */
    const STATUS_DECLINED = 3;
    /** @const int Кредит погашен (бывший активный кредит) */
    const STATUS_DONE = 4;

    /** @const int[] Все возможные статусы кредита */
    const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_ACTIVE,
        self::STATUS_DECLINED,
        self::STATUS_DONE,
    ];

    /**
     * @var  float Виртуальное свойство, достаётся из БД при помощи джойна к таблице транзакций.
     * Остаток по кредиту. Из суммы кредита вычитаем сумму выплат
     * Чтобы получить это свойство в модель, надо вызвать `Credit::find()->withTransactionsSum()`
     */
    public $debtSum;
    /**
     * @var  float Виртуальное свойство, достаётся из БД при помощи джойна к таблице транзакций.
     * Сумма выплат по кредиту.
     * Чтобы получить это свойство в модель, надо вызвать `Credit::find()->withTransactionsSum()`
     */
    public $paySum;
    /**
     * @var  float Виртуальное свойство, достаётся из БД при помощи джойна к таблице транзакций.
     * Сумма начисленных процентов за кредит
     * Чтобы получить это свойство в модель, надо вызвать `Credit::find()->withTransactionsSum()`
     */
    public $feeSum;
    /**
     * @var  float Виртуальное свойство, достаётся из БД при помощи джойна к таблице транзакций.
     * TIMESTAMP последнего платежа
     * Чтобы получить это свойство в модель, надо вызвать `Credit::find()->withTransactionsSum()`
     */
    public $maxPayTime;
    /**
     * @var array кэш
     */
    private static $mappedStatuses;
    /** @var array кыш */
    private static $currencyList;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'credits';
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
            [['user_id', 'external_id', 'status', 'created_at', 'updated_at', 'activated_at'], 'integer'],
            [['amount', 'currency', 'status', 'percent'], 'required'],
            [['amount', 'percent'], 'number'],
            [['amount'], 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number'],
            [['percent'], 'compare', 'compareValue' => 0, 'operator' => '>=', 'type' => 'number'],
            [['percent'], 'number', 'max' => 100],
            [['amount'], 'number', 'max' => 9999999.99],
            [['currency'], 'string', 'max' => 3],
            [['decline_reason'], 'string', 'max' => 1024],
            ['status', 'in', 'range' => static::STATUSES],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return static::translateAttributeLabels([
            'id',
            'amount',
            'currency',
            'status',
            'percent',
            'decline_reason',
            'created_at',
            'updated_at',
            'closed_at',
            'activated_at',
            'debtSum',
            'feeSum',
            'paySum',
            'maxPayTime',
        ]);
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return ArrayHelper::getValue(self::getMappedStatuses(), $this->status);
    }

    /**
     * @return array
     */
    private static function getMappedStatuses()
    {
        if (!self::$mappedStatuses) {
            self::$mappedStatuses = ArrayHelper::map(self::statusNameList(), 'id', 'name');
        }

        return self::$mappedStatuses;
    }

    /**
     * @return array
     */
    public static function statusNameList()
    {
        return [
            [
                'id' => self::STATUS_REQUESTED,
                'name' => Yii::_t('credits.credit.status_new_requested'),
            ],
            [
                'id' => self::STATUS_ACTIVE,
                'name' => Yii::_t('credits.credit.status_active'),
            ],
            [
                'id' => self::STATUS_DECLINED,
                'name' => Yii::_t('credits.credit.status_declined'),
            ],
            [
                'id' => self::STATUS_DONE,
                'name' => Yii::_t('credits.credit.status_done'),
            ],
        ];
    }

    /**
     * @return CreditQuery|object
     */
    public static function find()
    {
        return Yii::createObject(CreditQuery::class, [get_called_class()]);
    }

    /**
     * @return array
     */
    public static function getCurrencyList()
    {
        if (static::$currencyList === null) {
            static::$currencyList = UserBalance::getCurrencies();
        }
        return static::$currencyList;
    }

    /**
     * @return string
     */
    public function getCurrencyName()
    {
        return ArrayHelper::getValue(self::getCurrencyList(), $this->currency);
    }

    /**
     * Настройки лимитов и процентов для реселлера
     * @return array
     */
    public static function getSettings()
    {
        // TODO УБРААААТЬ""""
//    return [
//      'limitRub' => 10000,
//      'limitUsd' => 10000,
//      'limitEur' => 10000,
//      'percentRub' => 15,
//      'percentUsd' => 15,
//      'percentEur' => 15,
//    ];

        if ($cached = Yii::$app->cache->get(self::CREDIT_SETTINGS_CACHE_KEY)) {
            return $cached;
        }

        try {
            $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_GET_CREDIT_SETTINGS);
        } catch (\Exception $e) {
            Yii::error('MGMP Api not work');
            return null;
        }

        if (!$mgmpResponse->getIsOk()) {
            Yii::error('MGMP Api not work');
            return null;
        }

        $data = $mgmpResponse->getData();

        if (!is_array($data)) {
            Yii::error('MGMP Api returned not array');
            return null;
        }
        if (!ArrayHelper::getValue($data, 'success')) {
            Yii::error('MGMP Api returned success=false');
            return null;
        }
        if (!$settings = ArrayHelper::getValue($data, 'data')) {
            Yii::error('MGMP Api returned data=false');
            return null;
        }
        if (!is_array($settings)) {
            Yii::error('MGMP Api returned data not array');
            return null;
        }
        if (!array_key_exists('percentRub', $settings)) {
            Yii::error('MGMP Api has no percentRub setting');
            return null;
        }
        if (!array_key_exists('percentUsd', $settings)) {
            Yii::error('MGMP Api has no percentUsd setting');
            return null;
        }
        if (!array_key_exists('percentEur', $settings)) {
            Yii::error('MGMP Api has no percentEur setting');
            return null;
        }
        if (!array_key_exists('limitRub', $settings)) {
            Yii::error('MGMP Api has no limitRub setting');
            return null;
        }
        if (!array_key_exists('limitUsd', $settings)) {
            Yii::error('MGMP Api has no limitUsd setting');
            return null;
        }
        if (!array_key_exists('limitEur', $settings)) {
            Yii::error('MGMP Api has no limitEur setting');
            return null;
        }

        Yii::$app->cache->set(self::CREDIT_SETTINGS_CACHE_KEY, $settings, 60);

        return $settings;
    }

    /**
     * Транзакции
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(CreditTransaction::class, ['credit_id' => 'id']);
    }

    /**
     * Задолженность
     * @param int|null $createdAtLimit Ограничение даты создания
     * @return false|null|string
     */
    public function getDebt($createdAtLimit = null)
    {
        // TODO TRICKY ЗАПРОС DEBT ДУБЛИРУЕТСЯ В CreditQuery::withTransactionsSum()
        return $this
            ->getTransactions()
            ->select(new Expression(
                'SUM(IF(type = ' . CreditTransaction::TYPE_ACCRUE_AMOUNT . ', amount, 0)) - ' .
                'SUM(IF(type IN (' . implode(',', CreditTransaction::PAYMENT_TYPES) . '), amount, 0))'
            ))
            ->andFilterWhere(['<=', 'created_at', $createdAtLimit])
            ->scalar();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->user_id = UserPayment::getResellerId();

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Уведомление об одобрении/отклонении кредита
        if ($insert && $this->status == static::STATUS_ACTIVE) {
            (new CreditApprovedEvent($this))->trigger();
        }

        if (isset($changedAttributes['status'])) {
            switch ($this->status) {
                case static::STATUS_ACTIVE:
                    (new CreditApprovedEvent($this))->trigger();
                    break;
                case static::STATUS_DECLINED:
                    (new CreditDeclinedEvent($this))->trigger();
                    break;
                case static::STATUS_DONE:
                    (new CreditDoneEvent($this))->trigger();
                    break;
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Получить баланс реселлера соотвуствующий валюте кредита
     * @return number
     */
    public function getBalance()
    {
        $balance = new UserBalance(['userId' => UserPayment::getResellerId(), 'currency' => $this->currency]);
        return $balance->getResellerBalance();
    }
}
