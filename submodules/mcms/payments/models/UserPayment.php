<?php

namespace mcms\payments\models;

use mcms\common\traits\Translate;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\payments\components\api\ExchangerPartnerCourses;
use mcms\payments\components\events\PaymentCreated;
use mcms\payments\components\events\PaymentStatusUpdated;
use mcms\payments\components\events\PaymentUpdated;
use mcms\payments\components\invoice\UserPaymentInvoiceGenerator;
use mcms\payments\components\RemoteWalletBalances;
use mcms\payments\components\UserBalance;
use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\search\dataproviders\UserPaymentDataProvider;
use mcms\payments\models\search\UserPaymentSearch;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\Module;
use mcms\user\models\User;
use rgk\utils\behaviors\FileUploadBehavior;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\ServerErrorHttpException;

/**
 * Выплата
 *
 * TRICKY Минималка должна округляться в большую сторону при отображении в ошибке
 * TRICKY Максималка и лимиты должны округляться в меньшую сторону при отображении в ошибке
 * TRICKY При сравнении с мин, макс и лимитами нужно использовать значения из справочника и сумму с наложенными процентами, а не мин, макс с наложенными процентами и оригинальную сумму
 * TRICKY Сумма должна округляться round()
 *
 * # ВОЗМОЖНЫЕ ПРИЧИНЫ РАСХОЖДЕНИЯ ЦИФР НА JS И PHP
 * - отличие работы методов round()/floor()
 * - разное количество цифр после запятой
 * - порядок наложения процентов/конвертации/округления не совпадает
 * Что бы определить причину, нужно выписать каждый шаг и значение обработки суммы на JS, на PHP и сравнить.
 * Не забывать о самой валидации
 *
 * TRICKY
 * - для создания выплаты нужно обязательно указать свойство invoice_amount
 * - если invoice_currency != currency, перед валидацией выплаты amount будет сконвертирован из invoice_currency в currency
 *
 * TRICKY При измении логики конвертации, нужно убедится, что все формы для выплат работают и при выводе на реселлера конвертация не применяется
 * TRICKY При измении логики конвертации, нужно убедится, что JS соотвествует новой логике
 * @see mcms/payments/assets/resources/js/payments-admin.js
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $wallet_type
 * @property number $invoice_amount Сумма для снятия с баланса (по умолчанию равно amount)
 * @property number $request_amount
 * @property number $invoice_currency Валюта баланса
 * TRICKY $invoice_amount и $invoice_currency - это денормализованные значения из таблицы user_balance_invoices,
 * они должны быть равны значениям из этой таблицы (есть исключение, например когда инвойс создается для реселлера,
 * там может накладываться процент; по итогу на одну выплату может быть два инвойса (для вычета баланса партнера и для баланса реселлера при выплате))
 * @property number $amount Сумма для выплаты
 * @property string $currency Валюта для выплаты (принудительно устанавливается в валюту кошелька)
 * @property float $rgk_paysystem_percent Процент РГК для платежной системы
 * @property float $rgk_processing_percent Процент при процессинге через РГК
 * @property float $reseller_paysystem_percent Процент реселлера для платежной системы
 * @property float $reseller_individual_percent Индивидуальный процент реселлера
 * @property float $early_payment_percent Процент за создание выплаты.
 * Исторически сложилось, что этот процент положительный, хотя и вычитается из суммы, которую получит реселлер
 * @see UserPaymentSetting::getEarlyPercent()
 * @see Module::getEarlyPercentSettingsValue()
 * @property integer $status
 * @property string $description TRICKY внутренний коммент для ресов. Партнер не видит, в апи не передается. На данный момент МГМП тоже игнорирует это поле
 * @property string $error_info
 * @property integer $pay_period_end_date Крайняя дата выполнения выплаты
 * - для реселлерских выплат заполняется при отправке в МП
 * - для партнерских выплат заполняется при откладывании выплаты до указанного срока
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $processed_by
 * @property integer $payed_at
 * @property string $response
 * @property integer $is_hold
 * @property integer $created_by
 * @property string $from_date
 * @property string $to_date
 * @property string $period
 * @property integer $type
 * @property integer $processing_type
 * @property PaymentInfo $info
 * @property integer $user_wallet_id
 * @property string $invoice_file
 * @property string $cheque_file
 * @property float $remainSum
 * @property string $generated_invoice_file_positive
 * @property string $generated_invoice_file_negative
 *
 * @property bool $isWalletVerified
 * @property bool $ignore_minimum_amount_check   Игнор проверки минимальной суммы выплаты.  Default:false
 * @property UserBalanceInvoice $userBalanceInvoices
 * @property UserBalanceInvoice $payeeInvoice
 * @property UserPaymentSetting $userPaymentSetting
 * @property Wallet $walletModel
 * @property UserWallet $userWallet
 * @property User $user
 * @property UserPaymentChunk[] $chunks
 *
 * @method getUploadedFileUrl($attribute)
 * @see FileUploadBehavior::getUploadedFileUrl()
 * @method getUploadedFilePath($attribute)
 * @see FileUploadBehavior::getUploadedFilePath()
 */
class UserPayment extends \yii\db\ActiveRecord
{
  const STATUS_CANCELED = 0;
  const STATUS_COMPLETED = 1;
  const STATUS_ANNULLED = 2;
  const STATUS_AWAITING = 3;
  const STATUS_ERROR = 4;
  const STATUS_PROCESS = 5;
  const STATUS_DELAYED = 6; // Аналогично AWAITING

  const TYPE_ADMIN_MANUAL = 0;
  const TYPE_MANUAL = 1;
  const TYPE_GENERATED = 2;
  const TYPE_RESELLER_MANUAL = 3;

  const PROCESSING_TYPE_SELF = 1;
  const PROCESSING_TYPE_API = 2;
  const PROCESSING_TYPE_EXTERNAL = 3;

  const SCENARIO_CREATE = 'create';
  /**
   * создать выплату ресу
   */
  const SCENARIO_CREATE_RESELLER_PAYMENT = 'create_reseller_payment';
  const SCENARIO_UPDATE_RESELLER_PAYMENT = 'update_reseller_payment';
  const SCENARIO_ADMIN_CREATE = 'admin_create';
  /**
   * создание выплаты от реса партнеру
   */
  const SCENARIO_RESELLER_CREATE = 'reseller_create';
  const SCENARIO_RESELLER_UPDATE = 'reseller_update';
  const SCENARIO_UPDATE = 'update';
  const SCENARIO_SEND_TO_EXTERNAL = 'send_to_external';
  const SCENARIO_AUTOPAY = 'autopay'; // TODO выпилить
  const SCENARIO_AUTOPAYOUT = 'autopayout'; // новые автовыплаты
  const SCENARIO_AUTO_INVOICE = 'auto_invoice';

  const CACHE_KEY_PAYABLE_SUMMARY_BY_TYPE = 'cache_key_payable_summary_grouped_by_type';
  const CACHE_KEY_PAYABLE_SUMMARY_BY_TYPE_DURATION = 3600;

  use Translate;
  const LANG_PREFIX = 'payments.user-payments.';
  const PERIOD_SEPARATOR = ' - ';

  /**
   * @var float Сумма уже выплаченных частей
   */
  private $_chunksSum;
  /** @var string Тип инвойса выплаты.
   * В данном классе используется для определения нужно ли накладывать проценты за досрочную выплату.
   * В UserPaymentForm тип инвойса используется при создании инвойса */
  protected $invoiceType;
  protected static $typeList;
  protected static $processingTypeList;
  protected static $processingTypeListShort;
  protected static $currencyList;
  protected static $statusList;
  private $lastError = null;
  private static $readonlyStatuses = [self::STATUS_COMPLETED];
  private static $payableStatuses = [self::STATUS_AWAITING, self::STATUS_DELAYED, self::STATUS_ERROR];
  private static $defaultSelectedPayableStatuses = [self::STATUS_AWAITING, self::STATUS_DELAYED];
  private static $partnerNotifyStatuses = [self::STATUS_COMPLETED, self::STATUS_CANCELED, self::STATUS_ANNULLED, self::STATUS_DELAYED];

  private static $_resellerId;

  private $_userBalance;

  public $chequeDirectoryPath = '@protectedUploadPath/payments/cheques';
  public $chequeDirectoryUrl = ['/payments/payments/download-cheque'];
  public $chequeUrlPartner = ['/partners/payments/download-cheque'];

  public $invoicesDirectoryPath = '@protectedUploadPath/uploads/payments/invoices';
  public $invoicesDirectoryUrl = ['/payments/payments/download-invoice'];
  public $invoicesUrlPartner = ['/partners/payments/download-invoice'];

  public $generatedInvoicesDirectoryPath = '@protectedUploadPath/payments/generated-invoices';
  public $positiveInvoiceDirectoryUrl = ['/payments/payments/download-positive-invoice'];
  public $positiveInvoiceUrlPartner = ['/partners/payments/download-positive-invoice'];

  public $negativeInvoiceDirectoryUrl = ['/payments/payments/download-negative-invoice'];
  public $negativeInvoiceUrlPartner = ['/partners/payments/download-negative-invoice'];

 

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    /** @var \mcms\user\Module $userModule */
    $userModule = Yii::$app->getModule('users');

    $isPartnerCabinet = Yii::$app->user->can($userModule::PERMISSION_CAN_VIEW_PARTNER_CABINET);

    $chequeUrl = $isPartnerCabinet ? $this->chequeUrlPartner : $this->chequeDirectoryUrl;
    $invoiceUrl = $isPartnerCabinet ? $this->invoicesUrlPartner : $this->invoicesDirectoryUrl;
    $positiveInvoicePdfUrl = $isPartnerCabinet ? $this->positiveInvoiceUrlPartner : $this->positiveInvoiceDirectoryUrl;
    $negativeInvoicePdfUrl = $isPartnerCabinet ? $this->negativeInvoiceUrlPartner : $this->negativeInvoiceDirectoryUrl;

    return [
      [
        'class' => FileUploadBehavior::class,
        'attribute' => 'cheque_file',
        // TRICKY Не менять шаблон названия файла. Используется в \mcms\payments\commands\UpdatePaymentStatusController::saveCheckFile
        'filePath' => $this->chequeDirectoryPath . '/[[filename]]-[[pk]].[[extension]]',
        'fileRoute' => $chequeUrl,
      ],
      [
        'class' => FileUploadBehavior::class,
        'attribute' => 'invoice_file',
        // TRICKY Не менять шаблон названия файла. Используется в \mcms\payments\commands\UpdatePaymentStatusController::saveCheckFile
        'filePath' => $this->invoicesDirectoryPath . '/[[filename]]-[[pk]].[[extension]]',
        'fileRoute' => $invoiceUrl,
      ],
      [
        'class' => FileUploadBehavior::class,
        'attribute' => 'generated_invoice_file_positive',
        'filePath' => $this->generatedInvoicesDirectoryPath . '/[[filename]].[[extension]]',
        'fileRoute' => $positiveInvoicePdfUrl,
      ],
      [
        'class' => FileUploadBehavior::class,
        'attribute' => 'generated_invoice_file_negative',
        'filePath' => $this->generatedInvoicesDirectoryPath . '/[[filename]].[[extension]]',
        'fileRoute' => $negativeInvoicePdfUrl,
      ],
      [
        'class' => TimestampBehavior::class,
      ],
      [
        'class' => BlameableBehavior::class,
        'createdByAttribute' => 'created_by',
        'updatedByAttribute' => false,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_payments';
  }

  /**
   * @return array
   */
  public static function getReadonlyStatuses()
  {
    return self::$readonlyStatuses;
  }

  /**
   * @return array
   */
  public static function getPayableStatuses()
  {
    return self::getStatuses(self::$payableStatuses);
  }

  /**
   * @return array
   */
  public static function getDefaultSelectedPayableStatuses()
  {
    return self::getStatuses(self::$defaultSelectedPayableStatuses);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    /** @var bool $scenariosSave Сценарии создания выплаты */
    $scenariosCreate = [self::SCENARIO_CREATE, self::SCENARIO_CREATE_RESELLER_PAYMENT, self::SCENARIO_RESELLER_CREATE, self::SCENARIO_ADMIN_CREATE,self::SCENARIO_AUTO_INVOICE];
    /** @var bool $scenariosSave Сценарии создания и изменения выплаты */
    $scenariosSave = array_merge($scenariosCreate, [self::SCENARIO_UPDATE]);


    return [
      [['user_wallet_id', 'user_id', 'wallet_type', 'currency', 'amount', 'invoice_currency', 'invoice_amount', 'status'], 'required'],
      [['user_id', 'wallet_type', 'status', 'created_at', 'type'], 'integer'],
      [['is_hold'], 'boolean'],
      [['is_hold'], 'default', 'value' => false],

      [['response', 'currency', 'invoice_currency', 'from_date', 'to_date'], 'string'],
      [['description'], 'string', 'max' => 255],
      [['wallet_type'], 'in', 'range' => array_keys(Wallet::getWallets())],

      [['amount', 'invoice_amount', 'request_amount'], 'double'],
      [['amount', 'invoice_amount'], 'compare', 'compareValue' => 0, 'operator' => '>'],
      [['invoice_amount'], 'checkAmount', 'on' => $scenariosSave],
      [['ignore_minimum_amount_check'], 'default', 'value' => '0', 'on' => $scenariosSave],
      [['ignore_minimum_amount_check'], 'boolean', 'on' => [self::SCENARIO_ADMIN_CREATE,self::SCENARIO_UPDATE]],
      // TRICKY При валидации min/max/limit используется не только поле amount, используемое поле смотри в самих методах
      [['amount'], 'checkMinAmount', 'on' => $scenariosSave],
      [['amount'], 'checkLimits', 'on' => $scenariosSave],
      [['to_date'], 'compare', 'compareAttribute' => 'from_date', 'operator' => '>='],
      [['type'], 'default', 'value' => self::TYPE_ADMIN_MANUAL, 'on' => self::SCENARIO_ADMIN_CREATE, 'skipOnEmpty' => false],
      [['type'], 'default', 'value' => self::TYPE_MANUAL, 'on' => self::SCENARIO_CREATE, 'skipOnEmpty' => false],
      [['type'], 'default', 'value' => self::TYPE_GENERATED, 'on' => [self::SCENARIO_AUTOPAYOUT,self::SCENARIO_AUTO_INVOICE], 'skipOnEmpty' => false],
      ['processing_type', 'required'],
      [['rgk_paysystem_percent', 'rgk_processing_percent', 'reseller_paysystem_percent'], 'number'],
      ['user_wallet_id', 'checkPaysystemActivity', 'on' => $scenariosCreate],
      ['invoice_file', 'file', 'extensions' => ['pdf', 'jpg', 'jpeg', 'png'], 'maxSize' => 10485760], // 10 мб
      ['cheque_file', 'file', 'extensions' => ['pdf', 'jpg', 'jpeg', 'png'], 'maxSize' => 10485760], // 10 мб
      ['invoiceType', 'in', 'range' => [UserBalanceInvoice::TYPE_PAYMENT, UserBalanceInvoice::TYPE_EARLY_PAYMENT]],
      [['generated_invoice_file_positive', 'generated_invoice_file_negative'], 'string'],
      ['processed_by', 'integer', 'on' => [self::SCENARIO_UPDATE]]
    ];
  }

  /**
   * Проверка активности ПС
   * @param string $attribute
   * @return bool
   */
  public function checkPaysystemActivity($attribute)
  {
    if (!$this->walletModel->is_active) {
      $this->addError($attribute, Yii::_t('payments.user-payments.error-inactive-paysystem'));
      return false;
    }

    return true;
  }

  /**
   * можно ли редактировать выплату.
   * @return bool
   */
  public function canEdit()
  {
    return true; // todo все равно редактировать можно только комментарий к выплате. Решили пока разрешить при любых статусах и типах.
//    return $this->processing_type !== self::PROCESSING_TYPE_API && $this->processing_type !== self::PROCESSING_TYPE_EXTERNAL;
  }

  /**
   * Проверить сумму выплаты на минимум
   * @return bool
   */
  public function checkMinAmount()
  {
    if (!($userWallet = $this->getWallet()) || !$this->walletModel) {
      return false;
    }
    
    if(!empty($this->ignore_minimum_amount_check)){
      return true;
    }

    // TRICKY При минималке в справочнике например 100, ни при каких обстоятельствах сумма, которая будет выведена, не должна быть меньше 100, ни на копейку
    $minSum = $this->walletModel->getMinPayoutByCurrency($this->currency);
    if ($this->amount < $minSum) {
      $minSumMessage = $this->isConvert() ? $this->reverseConvert($minSum, $this->invoice_currency, $this->currency) : $minSum;
      $minSumMessage = $this->ceil($this->modifyMinMaxToShow($minSumMessage, 'min'));
      $minSumMessage = Yii::$app->formatter->asPrice($minSumMessage, $this->invoice_currency);
      $this->addError('invoice_amount', Yii::_t('payments.user-payments.error-min-amount', ['min' => $minSumMessage]));
      return false;
    }
    return true;
  }

  /**
   * Проверить сумму выплаты на максимум, дневной и месячный лимиты
   * @return bool
   */
  public function checkLimits()
  {
    if (!($userWallet = $this->getWallet()) || !$this->walletModel) return false;

    $limits = [];

    list($walletId, $currency) = (new UserWallet())->getWalletsForLimits($this->user_wallet_id, $this->currency);

    // Максимальная сумма выплаты
    $limit = $this->walletModel->getMaxPayoutByCurrency($this->currency);
    if ($limit) $limits[] = [
      'code' => 'max',
      'limit' => $limit,
      'used_limit' => 0,
    ];

    // Дневной лимит
    $dailyLimit = $this->walletModel->getPayoutLimitDailyByCurrency($this->currency);
    if ($dailyLimit) {
      $limits[] = [
        'code' => 'daily',
        'limit' => $dailyLimit,
        'used_limit' => static::getDailyLimitUse($walletId, $currency),
      ];
    }

    // Месячный лимит
    $monthlyLimit = $this->walletModel->getPayoutLimitMonthlyByCurrency($this->currency);
    if ($monthlyLimit) {
      $limits[] = [
        'code' => 'monthly',
        'limit' => $monthlyLimit,
        'used_limit' => static::getMonthlyLimitUse($walletId, $currency),
      ];
    }

    // Проверка лимитов
    foreach ($limits as $limit) {
      if ($limit['used_limit'] + $this->amount > $limit['limit']) {
        $availableSum = $limit['limit'] - $limit['used_limit'];
        if ($availableSum < 0) $availableSum = 0;

        $availableSumMessage = $availableSum;
        $limitSumMessage = $limit['limit'];

        if ($this->isConvert()) {
          $availableSumMessage = $this->round($this->reverseConvert($availableSumMessage, $this->currency, $this->invoice_currency));
          $limitSumMessage = $this->reverseConvert($limitSumMessage, $this->currency, $this->invoice_currency);
        }

        if ($limit['code'] == 'max') $limitSumMessage = $this->modifyMinMaxToShow($limitSumMessage, 'max');
        $limitSumMessage = $this->floor($limitSumMessage);

        $this->addError(
          'invoice_amount',
          Yii::_t(
            'payments.user-payments.error-limit-' . $limit['code'],
            [
              'available' => Yii::$app->formatter->asPrice($availableSumMessage, $this->invoice_currency),
              'limit' => Yii::$app->formatter->asPrice($limitSumMessage, $this->invoice_currency),
            ]
          )
        );
        return false;
      }
    }
    return true;
  }

  /**
   * @return bool
   */
  public function checkAmount()
  {
    if (!$this->isNewRecord || !$this->user_id) return false;

    $balance = $this->getUserBalance($this->invoice_currency);
    $selectedBalance = $this->is_hold ? $balance->getHold() : $balance->getMain();

    // TRICKY Для создания/редактирования в админке баланс партнера не проверяем! Т.е. разрешаем уходить в минус
    if (in_array($this->scenario, [self::SCENARIO_ADMIN_CREATE, self::SCENARIO_UPDATE])) {
      return true;
    }

    if ($selectedBalance <= 0) {
      $this->addError('invoice_amount', Yii::_t('payments.user-payments.error-balance-main'));
      return false;
    }

    if ($selectedBalance - $this->invoice_amount < 0) {
      $this->addError('invoice_amount', Yii::_t('payments.user-payments.error-balance-insufficient'));
      return false;
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_AUTOPAY => [],
      self::SCENARIO_ADMIN_CREATE => [
        'user_id', 'wallet_type', 'user_wallet_id', 'currency', 'amount', 'invoice_currency', 'invoice_amount', 'status', 'type', 'created_at', 'is_hold',
        'from_date', 'to_date', 'description', 'ignore_minimum_amount_check'
      ],
      self::SCENARIO_AUTOPAYOUT => [
        'user_id', 'wallet_type', 'user_wallet_id', 'currency', 'amount', 'invoice_currency', 'invoice_amount', 'status', 'type', 'created_at', 'is_hold',
        'from_date', 'to_date', 'description', 'generated_invoice_file_positive', 'generated_invoice_file_negative',
      ],
      self::SCENARIO_CREATE => [
        'user_id', 'wallet_type', 'user_wallet_id', 'currency', 'amount', 'invoice_currency', 'invoice_amount', 'status', 'type', 'created_at', 'is_hold',
        'from_date', 'to_date', 'description'
      ],
      self::SCENARIO_AUTO_INVOICE => [
        'user_id', 'wallet_type', 'user_wallet_id', 'currency', 'amount', 'invoice_currency', 'invoice_amount', 'status', 'type', 'created_at', 'is_hold',
        'from_date', 'to_date', 'description'
      ],
      self::SCENARIO_CREATE_RESELLER_PAYMENT => [
        'user_id', 'wallet_type', 'user_wallet_id', 'currency', 'amount', 'invoice_currency', 'invoice_amount', 'status', 'type', 'created_at', 'is_hold',
        'from_date', 'to_date', 'description'
      ],
      self::SCENARIO_UPDATE_RESELLER_PAYMENT => [
        'description'
      ],
      self::SCENARIO_UPDATE => [
        'wallet_type', 'request_amount', 'invoice_amount', 'status', 'created_at', 'is_hold', 'from_date', 'to_date', 'description','reseller_paysystem_percent', 'ignore_minimum_amount_check'
      ],
      self::SCENARIO_SEND_TO_EXTERNAL => [
        'wallet_type', 'invoice_amount', 'status', 'created_at', 'is_hold', 'from_date', 'to_date', 'description'
      ],
      self::SCENARIO_RESELLER_CREATE => [
        'user_id', 'wallet_type', 'user_wallet_id', 'currency', 'amount', 'invoice_currency', 'invoice_amount', 'status', 'type', 'created_at', 'is_hold',
        'from_date', 'to_date', 'description'
      ],
      self::SCENARIO_RESELLER_UPDATE => [
        'status', 'from_date', 'to_date', 'description',
      ],
    ]);
  }


  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => self::translate('attribute-user'),
      'created_by' => self::translate('attribute-crated-by'),
      'user' => self::translate('attribute-user'),
      'wallet_type' => self::translate('attribute-wallet-type'),
//      'user_balance_invoice_id' => self::translate('attribute-user-balance-invoice-id'),
      'currency' => self::translate('attribute-currency'),
      'amount' => self::translate('attribute-amount'),
      'invoice_amount' => self::translate('attribute-amount'),
      'type' => self::translate('attribute-type'),
      'processing_type' => self::translate('attribute-processing_type'),
      'status' => self::translate('attribute-status'),
      'description' => self::translate('attribute-description'),
      'error_info' => self::translate('attribute-error_info'),
      'created_at' => self::translate('attribute-created-at'),
      'updated_at' => self::translate('attribute-updated-at'),
      'payed_at' => self::translate('attribute-payed-at'),
      'response' => self::translate('attribute-response'),
      'is_hold' => self::translate('attribute-is-hold'),
      'from_date' => self::translate('attribute-from_date'),
      'to_date' => self::translate('attribute-to_date'),
      'period' => self::translate('attribute-period'),
      'user_wallet_id' => self::translate('attribute-wallet-id'),
      'pay_period_end_date' => self::translate('attribute-pay_period_end_date'),
      'cheque_file' => self::translate('attribute-checkFile'),
      'invoice_file' => self::translate('attribute-invoiceFile'),
      'generated_invoice_file_positive' => self::translate('attribute-generated_invoice_file_positive'),
      'generated_invoice_file_negative' => self::translate('attribute-generated_invoice_file_negative'),
      'resellerCompany' => Yii::_t('payments.partner-companies.reseller_company'),
      'isWalletVerified' => Yii::_t('payments.user-payments.is_wallet_verified'),
      'request_amount' => Yii::_t('payments.user-payments.request_amount'),
      'ignore_minimum_amount_check' => Yii::_t('payments.user-payments.attribute-ignore_minimum_amount_check'),
    ];
  }

  /**
   * @param string|array|null $status
   * @return mixed
   */
  public static function getStatuses($status = null, $isLower = false)
  {

    $statusList = [
      self::STATUS_PROCESS => $isLower
        ? self::translate('status-process-lower')
        : self::translate('status-process'),
      self::STATUS_AWAITING => $isLower
        ? self::translate('status-awaiting-lower')
        : self::translate('status-awaiting'),
      self::STATUS_DELAYED => $isLower
        ? self::translate('status-delayed-lower')
        : self::translate('status-delayed'),
      self::STATUS_ERROR => $isLower
        ? self::translate('status-error-lower')
        : self::translate('status-error'),
      self::STATUS_COMPLETED => $isLower
        ? self::translate('status-completed-lower')
        : self::translate('status-completed'),
      self::STATUS_CANCELED => $isLower
        ? self::translate('status-canceled-lower')
        : self::translate('status-canceled'),
      self::STATUS_ANNULLED => $isLower
        ? self::translate('status-annulment-lower')
        : self::translate('status-annulment'),
    ];

    if (is_array($status)) {
      return array_intersect_key($statusList, array_flip($status));
    }
    return $status === null
      ? $statusList
      : ArrayHelper::getValue($statusList, $status);
  }

  /**
   * Получить название статуса
   * @param bool $isLower
   * @return string
   */
  public function getStatusLabel($isLower = false)
  {
    return $this->getStatuses($this->status, $isLower);
  }

  /**
   * Получить название статуса для партнера.
   * Для партнера статус Отложена должен отображаться как в процессе
   * @param bool $isLower
   * @return string
   */
  public function getStatusLabelPartner($isLower = false)
  {
    $status = $this->status == static::STATUS_ERROR ? static::STATUS_PROCESS : $this->status;

    // статус "в процессе" показываем партнеру как "в ожидании". Согласовано с d.murzin 01.08.2017
    $status = $status == static::STATUS_PROCESS ? static::STATUS_AWAITING : $status;

    return $this->getStatuses($status, $isLower);
  }

  /**
   * @param null $type
   * @return mixed
   */
  public static function getTypes($type = null)
  {
    if (static::$typeList === null) {
      static::$typeList = [
        self::TYPE_GENERATED => self::translate('type-generated'),
        self::TYPE_MANUAL => self::translate('type-manual'),
        self::TYPE_ADMIN_MANUAL => self::translate('type-admin-manual'),
      ];
    }

    return $type === null ? static::$typeList : ArrayHelper::getValue(static::$typeList, $type);
  }

  /**
   * @param null $type
   * @param bool $short
   * @return mixed
   */
  public static function getProcessingTypes($type = null, $short = false)
  {
    if (static::$processingTypeList === null) {
      static::$processingTypeList = [
        self::PROCESSING_TYPE_SELF => self::translate('processing-type-self'),
        self::PROCESSING_TYPE_API => self::translate('processing-type-api'),
        self::PROCESSING_TYPE_EXTERNAL => self::translate('processing-type-external'),
      ];
    }

    if (static::$processingTypeListShort === null) {
      static::$processingTypeListShort = [
        self::PROCESSING_TYPE_SELF => self::translate('processing-type-short-self'),
        self::PROCESSING_TYPE_API => self::translate('processing-type-short-api'),
        self::PROCESSING_TYPE_EXTERNAL => self::translate('processing-type-short-external'),
      ];
    }

    $list = $short ? static::$processingTypeListShort : static::$processingTypeList;

    return $type === null ? $list : ArrayHelper::getValue($list, $type);
  }

  /**
   * Тип выплаты в текстовом виде
   * @return string
   */
  public function getTypeLabel()
  {
    return self::getTypes($this->type);
  }

  /**
   * Тип процессинга выплаты в текстовом виде
   * @param bool $short
   * @return string
   */
  public function getProcessingTypeLabel($short = false)
  {
    return self::getProcessingTypes($this->processing_type, $short);
  }

  /**
   * @param integer|array|null $type
   * @return array|string
   */
  public static function getWalletTypes($type = null)
  {
    if (is_array($type)) {
      return array_intersect_key(Wallet::getWallets(), array_flip($type));
    }
    return Wallet::getWallets($type);
  }

  /**
   * Название ПС
   * @return array|string
   */
  public function getWalletTypeLabel()
  {
    $wallet = UserWallet::findOne($this->user_wallet_id);
    $this->wallet_type = $this->wallet_type ?: $wallet->wallet_type;
    return self::getWalletTypes($this->wallet_type);
  }

  /**
   * @param bool|null $paysystemActivity Активность ПС @see Wallet::find()
   * @return UserWallet|ActiveRecord|null
   */
  public function getWallet($paysystemActivity = null)
  {
    $query = UserWallet::find(false)->paysystemsActivity($paysystemActivity);
    if ($this->user_wallet_id) {
      return $query->andWhere([UserWallet::tableName() . '.id' => $this->user_wallet_id])->one();
    } else {
      return $query
        ->andWhere(['user_id' => $this->user_id, 'wallet_type' => $this->wallet_type, 'currency' => $this->currency])
        ->one();
    }
  }

  /**
   * tricky: Используется where([]), т.к. find() переопределен с добавлением andWhere(['is_deleted' => false])
   * @return ActiveQuery
   */
  public function getUserWallet()
  {
    return $this->hasOne(UserWallet::class, ['id' => 'user_wallet_id'])->where([]);
  }

  /**
   * Реквизиты получателя в виде таблицы
   * @param array $options
   * @return string
   */
  public function getAccountDetailView($options = [])
  {
    $paySystem = Wallet::getObject($this->wallet_type, $this->getUserWallet()->one()->getAccountAssoc(), $this->user_id);

    return Wallet::getAccountDetailView($paySystem, $options);
  }

  /**
   * Находится ли выплата в холде
   * @return int|bool
   */
  public function isHold()
  {
    return $this->is_hold;
  }

  /**
   * Находится ли выплата в холде. Текстовый вид
   * @return string
   */
  public function getIsHoldLabel()
  {
    return Yii::_t($this->isHold() ? 'app.common.Yes' : 'app.common.No');
  }

  /**
   * Варианты состояния холда в текстовом виде
   * @return array
   */
  public static function getIsHoldList()
  {
    return ['0' => yii::_t('app.common.No'), '1' => yii::_t('app.common.Yes')];
  }

  /**
   * @param null $currency
   * @return UserBalance
   */
  protected function getUserBalance($currency = null)
  {
    if ($balance = ArrayHelper::getValue($this->_userBalance, $currency)) {
      return $balance;
    }
    return $this->_userBalance[$currency] = new UserBalance(['userId' => $this->user_id, 'currency' => $currency]);
  }

  /**
   * Настройки выплат пользователя
   * @return ActiveQuery
   */
  public function getUserPaymentSetting()
  {
    return $this->hasOne(UserPaymentSetting::class, ['user_id' => 'user_id']);
  }

  /**
   * Настройки выплат пользователя.
   * В отличии от @see getUserPaymentSetting() использует кэширование и в случае отсутствия записи вернет модель по умолчанию
   * @return UserPaymentSetting
   */
  public function fetchUserPaymentSetting()
  {
    return UserPaymentSetting::fetch($this->user_id);
  }

  /**
   * Возможно метод устарел
   * @return ActiveQuery
   */
  public function getUserPaymentSettingsIsAutoPayoutEnable()
  {
    return $this->getUserPaymentSetting()->andWhere([
      UserPaymentSetting::tableName() . '.is_auto_payout_disabled' => 0
    ]);
  }

  /**
   * Инвойсы выплаты
   * TODO Метод дублирует getUserBalanceInvoices()
   * @return ActiveQuery
   */
  public function getInvoices()
  {
    return $this->hasMany(UserBalanceInvoice::class, ['user_payment_id' => 'id']);
  }

  /**
   * Инвойс получателя за создание выплаты
   * TRICKY Не кэшируется
   * @return UserBalanceInvoice|ActiveRecord
   */
  public function getPayeeInvoice()
  {
    return $this->getInvoicesPaymentAndEarlyPayment()->andWhere(['user_id' => $this->user_id])->one();
  }

  /**
   * Инвойсы типа Досрочная выплата
   * Возможно метод устарел
   * @return ActiveQuery
   */
  public function getInvoicesEarlyPayment()
  {
    return $this->getInvoices()->andWhere([UserBalanceInvoice::tableName() . '.type' => [
      UserBalanceInvoice::TYPE_EARLY_PAYMENT
    ]]);
  }

  /**
   * Инвойсы тип Досрочная выплата и просто выплата
   * @return ActiveQuery
   */
  public function getInvoicesPaymentAndEarlyPayment()
  {
    return $this->getInvoices()->andWhere([UserBalanceInvoice::tableName() . '.type' => [
      UserBalanceInvoice::TYPE_EARLY_PAYMENT, UserBalanceInvoice::TYPE_PAYMENT
    ]]);
  }

  /**
   * @return mixed
   */
  public function getInvoicesTypeId()
  {
    return ArrayHelper::getValue($this->invoices, [0, 'type']);
  }

  /**
   * @return array|string
   */
  public function getInvoicesTypeName()
  {
    return UserBalanceInvoice::getTypes($this->getInvoicesTypeId());
  }

  /**
   * @return array|string|\string[]
   */
  public function getWalletList()
  {
    return $this->currency
      ? ArrayHelper::map(Wallet::getByCurrency($this->currency), 'id', 'name')
      : Wallet::getWallets();
  }

  /**
   * @return ActiveQuery
   */
  public function getPartnerCompany()
  {
    return $this->hasOne(PartnerCompany::class, ['id' => 'partner_company_id'])
      ->via('userPaymentSetting');
  }

  /**
   * @return ActiveQuery
   */
  public function getResellerCompany()
  {
    return $this->hasOne(Company::class, ['id' => 'reseller_company_id'])
      ->via('partnerCompany');
  }

  /**
   * @inheritDoc
   */
  public function beforeValidate()
  {
    if (!$this->user_id) {
      return parent::beforeValidate();
    }

    if ($this->pay_period_end_date && !is_numeric($this->pay_period_end_date)) {
      $this->pay_period_end_date = strtotime($this->pay_period_end_date) ?: null;
    }

    if (!$this->isNewRecord) {
      $this->isAttributeChanged('request_amount') && $this->updateAmount();

      return parent::beforeValidate();
    }

    if ($this->scenario !== self::SCENARIO_DEFAULT) {
      $userSettings = $this->fetchUserPaymentSetting();
      $userWallet = $this->getWallet();

      if (!$this->wallet_type && !$userWallet) { // нефиг тут делать, если ещё даже кошелёк не ввведен в форму
        return parent::beforeValidate();
      }

      $this->wallet_type = $this->wallet_type ?: ($userWallet ? $userWallet->wallet_type : null);
      $this->rgk_paysystem_percent = $this->walletModel->getDefaultProfitPercent();
      $this->rgk_processing_percent = $this->getActualProcessingPercent();
      $this->reseller_paysystem_percent = $this->getPaysystemPercent();
      $this->reseller_individual_percent = $this->getResellerIndividualPercent();
      $this->early_payment_percent = $this->invoiceType == UserBalanceInvoice::TYPE_EARLY_PAYMENT
            ? $this->fetchUserPaymentSetting()->getEarlyPercent()
            : null;
      // Установка валюты инвойса по умолчанию
      if (!$this->invoice_currency) {
        // TODO Разобаться со странной логикой
        /* - по идее если !$model->canUseMultipleCurrenciesBalance(), то invoice_currency должен всегда равняться валюте баланса пользователя
         * - зачем здесь в условии Module::getSelectedCurrency()?
         * - при рефакторинге учесть реселлера и другие роли
         */
        if (
          !$userSettings->canUseMultipleCurrenciesBalance() ||
          !$currency = Module::getSelectedCurrency()
        ) {
          $this->invoice_currency = $userSettings->getCurrentCurrency(); // Основная валюта
        } else {
          $this->invoice_currency = $userWallet ? $userWallet->currency : null; // Валюта кошелька
        }
      }

      if ($this->amount) {
        throw new Exception('Сумма должна быть передана только в invoice_amount');
      }

      $this->updateAmount();
    }

    return parent::beforeValidate();
  }

  /**
   * TRICKY Метод вызывается при каждой валидации
   */
  private function updateAmount()
  {
    if (!$this->isNewRecord && !$this->canChangeAmounts()) {
      return;
    }

    $this->userWallet && $this->currency = $this->userWallet->currency;
    if (!$this->request_amount) {
      $this->request_amount = $this->isConvert()
        ? $this->convertInvoiceToPaymentCurrency()
        : $this->invoice_amount;
    }
    $this->amount = $this->request_amount;

    $isConvert = $this->isConvert();
    if (!$isConvert && $this->currency != $this->invoice_currency) {
      throw new ServerErrorHttpException('Неверная логика работы с валютами выплаты');
    }


    // Дополнение описания курсом конвертации
    $isConvert && $this->on(self::EVENT_BEFORE_INSERT, function () {
      $this->description .= ($this->description ? '; ' : null)
        . Yii::_t('payments.payments.modify_description_course', [
          'amount' => Yii::$app->getFormatter()->asDecimal($this->invoice_amount, 2),
          'currency' => $this->invoice_currency,
          'course' => $this->getConvertInvoiceToPaymentCourse(),
        ]);
    });

    // вычисляем сумму к выплате исходя из процента по кошельку
    $this->amount = (float)$this->amount;
    if ($this->wallet_type) {
      $amount = $this->amount;

      $profitPercent = (float)$this->reseller_paysystem_percent;
      // Комиссия за создание выплаты.
      // Списывается, если пользователь создающий выплату не имеет права создавать выплату без комиссии за создание
      // На данный момент подразумевается, что комиссия за создание снимается только при создании выплаты из партнерки
      if ($this->early_payment_percent) {
        $profitPercent -= (float)$this->early_payment_percent;
      }

      if ($profitPercent) {
        // Накладывание процента профита на сумму к выплате
        // TRICKY Если будет накладываться еще какие-то проценты, нужно узнать, накладывать на оригинальную сумму или на сумму с процентами
        // TRICKY Округление профита (не суммы в результате) может привести к проблемам с минималкой при конвертации. Например минималка 3000, а будет 2999.99

        $this->amount += $amount / 100 * $profitPercent;

      }

      $resellerPercent = $this->getResellerIndividualPercent();
      if ($resellerPercent && $this->scenario == UserPayment::SCENARIO_CREATE_RESELLER_PAYMENT) {
        $this->amount += $amount / 100 * $resellerPercent;
      }
    }

    // Явное округление сумм (раньше это делал MySQL)
    $this->request_amount = $this->round($this->request_amount);
    $this->amount = $this->round($this->amount);
  }

  /**
   * @inheritDoc
   * TODO В будущем порефакторить системы событий, избавится от ВЛОЖЕННОСТИ или хотя бы как-то упростить
   * Описание того что здесь может тригерится, дефисом обозначен уровень вложенности:
   * - PaymentStatusUpdated (внимательно: вызывается только для партнера, если статус изменен и только если новый статус не равен Отложено)
   * - PaymentUpdated (это событие скорее всего не отправляет событие, так как не зарегистрировано в БД)
   * -- RegularPaymentCreated (это событие скорее всего не отправляет событие, так как не увидел его триггер в PaymentUpdated)
   * -- EarlyPaymentCreated (это событие скорее всего не отправляет событие, так как не нашел его триггер в PaymentUpdated)
   *
   * Уведомления ниже генерятся в @see \mcms\payments\models\UserBalanceInvoice::afterSave() и самих в методе trigger самих событий, см. уровень вложенности
   * - UserBalanceInvoiceCreated @see \mcms\payments\models\UserBalanceInvoice::afterSave()
   * -- UserBalanceInvoiceCompensation
   * -- UserBalanceInvoiceMulct
   * - PaymentCreated @see \mcms\payments\models\UserBalanceInvoice::afterSave()
   * -- EarlyPaymentCreated
   * -- EarlyPaymentAdminCreated
   * -- RegularPaymentCreated
   */
  public function afterSave($insert, $changedAttributes)
  {
    if ($insert) {
      // Отправка уведомления о создании выплаты
      (new PaymentCreated($this))->trigger();
      parent::afterSave($insert, $changedAttributes);
      return;
    }
    if (ArrayHelper::getValue($changedAttributes, 'status')) {

      // Отправка уведомления о смене статуса партнеру
      // TRICKY MCMS-1225. Отправляем уведомление партнеру ТОЛЬКО при переключении статуса в: complete, cancel, annul, delay.
      if (in_array($this->status, self::$partnerNotifyStatuses)) {
        (new PaymentStatusUpdated($this))->trigger();
      }

      // TRICKY Внутри вызывается еще два события
      // Отправка уведомления о смене статуса ресу, админу, руту
      (new PaymentUpdated($this))->trigger();
    }
    parent::afterSave($insert, $changedAttributes);

    self::invalidateCache();
  }

  /**
   * @return bool
   */
  public function canChangeAmounts()
  {
    return Yii::$app->user->can(Module::PERMISSION_CAN_CHANGE_PAYMENT_AMOUNT);
  }

  /**
   * @return ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return User
   */
  public function getCreatedBy()
  {
    return Yii::$app->getModule('users')->api('user', ['getRelation' => true])->hasOne($this, 'created_by');
  }

  /**
   * TODO Метод дублирует getInvoices()
   * @return \yii\db\ActiveQuery
   */
  public function getUserBalanceInvoices()
  {
    return $this->hasMany(UserBalanceInvoice::class, ['user_payment_id' => 'id']);
  }

  /**
   * @return bool
   */
  public function getIsUserBlocked()
  {
    $statusesApi = Yii::$app->getModule('users')->api('statuses');
    return (int)$this->user->status === $statusesApi::STATUS_BLOCKED;
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
   * @return array
   */
  public function getReplacements()
  {
    return [
      'id' => [
        'value' => $this->id,
        'help' => [
          'label' => self::translate('attribute-id'),
        ]
      ],
      'user' => [
        'value' => $this->isNewRecord ? null : $this->getUser()->one()->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => self::translate('attribute-user')
        ]
      ],
      'created_by' => [
        'value' => $this->isNewRecord ? null : $this->getCreatedBy()->one()->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => self::translate('attribute-crated-by')
        ]
      ],
      'wallet_type' => [
        'value' => $this->isNewRecord ? null : $this->getWalletTypeLabel(),
        'help' => [
          'label' => self::translate('attribute-wallet-type')
        ]
      ],
      'currency' => [
        'value' => $this->isNewRecord ? null : $this->currency,
        'help' => [
          'label' => self::translate('attribute-currency')
        ]
      ],
      'amount' => [
        'value' => $this->isNewRecord ? null : $this->amount,
        'help' => [
          'label' => self::translate('attribute-amount')
        ]
      ],
      'date_from' => [
        'value' => $this->from_date,
        'help' => [
          'label' => self::translate('attribute-from_date')
        ]
      ],
      'date_to' => [
        'value' => $this->to_date,
        'help' => [
          'label' => self::translate('attribute-to_date')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getStatusLabel(true),
        'help' => [
          'label' => self::translate('attribute-status')
        ]
      ],
      'type' => [
        'value' => $this->isNewRecord ? null : $this->getTypeLabel(),
        'help' => [
          'label' => self::translate('attribute-type')
        ]
      ],
      'processing_type' => [
        'value' => $this->isNewRecord ? null : $this->getProcessingTypeLabel(),
        'help' => [
          'label' => self::translate('attribute-processing_type')
        ]
      ],
      'description' => [
        'value' => $this->isNewRecord ? null : $this->description,
        'help' => [
          'label' => self::translate('attribute-description')
        ]
      ],
      'created_at' => [
        'value' => $this->isNewRecord ? null : $this->created_at,
        'help' => [
          'label' => self::translate('attribute-created-at')
        ]
      ],
      'updated_at' => [
        'value' => $this->isNewRecord ? null : $this->updated_at,
        'help' => [
          'label' => self::translate('attribute-updated-at')
        ]
      ],
      'payed_at' => [
        'value' => $this->isNewRecord ? null : $this->payed_at,
        'help' => [
          'label' => self::translate('attribute-payed-at')
        ]
      ],
      'response' => [
        'value' => $this->isNewRecord ? null : $this->response,
        'help' => [
          'label' => self::translate('attribute-response')
        ]
      ],
      'is_hold' => [
        'value' => $this->isNewRecord ? null : $this->getIsHoldLabel(),
        'help' => [
          'label' => self::translate('attribute-is-hold')
        ]
      ],
    ];
  }

  /**
   * Проверка, доступна ли выплата для экспорта
   * @return boolean
   */
  public function isExportAvailable()
  {
    return !$this->isReadonly();
  }

  /**
   * @return bool
   */
  public function isAvailableUser()
  {
    $ignoreIds = Yii::$app->getModule('users')
      ->api('notAvailableUserIds', [
        'userId' => Yii::$app->user->id,
      ])
      ->getResult();

    return in_array($this->user_id, $ignoreIds);
  }

  /**
   * @return bool
   */
  public function isReadonly()
  {
    //в консоли не срабатывает getUser()
    return Yii::$app->user->can(Module::PERMISSION_CAN_EDIT_PAYED_PAYMENTS)
      ? false
      : in_array($this->status, self::$readonlyStatuses);
  }

  /**
   * @return bool
   */
  public function isAwaiting()
  {
    return in_array($this->status, [self::STATUS_AWAITING, self::STATUS_DELAYED]);
  }

  /**
   * @return bool
   */
  public function isPayable()
  {
    $result = true;
    $error = null;

    // Валидация выплаты
    if (!$this->validate()) {
      $result = false;
      $error = Yii::_t('payments.user-payments.error-payment-is-not-valid');
    }

    // Проверка статуса выплаты
    if (!in_array($this->status, self::$payableStatuses)) {
      $result = false;
      $error = Yii::_t('payments.user-payments.error-not-payable-status');
    }

    // Проверка наличия пользователя в блэк листе
    if ($this->isAvailableUser()) {
      $result = false;
      $error = Yii::_t('payments.user-payments.error-recipient-not-available');
    }

    // Проверка настроек пользователя
    if ($this->paymentsIsDisabled() && Yii::$app->user->id == $this->user_id) {
      $result = false;
      $error = Yii::_t('payments.user-payments.error-user-payments-disabled');
    }

    $this->lastError = $error;

    return $result;
  }

  /**
   * @param $userId
   * @return ActiveDataProvider
   */
  public static function getUserPayments($userId)
  {
    return new ActiveDataProvider([
      'query' => UserPayment::find()->where(['user_id' => $userId])->orderBy(['created_at' => SORT_DESC]),
      'sort' => false,
    ]);
  }

  /**
   * @return string
   */
  public function getPeriod()
  {
    //todo переделать после фикса https://www.wrike.com/open.htm?id=81470317
    // поля from_date и to_date не будут пустыми
    if ($this->from_date == $this->to_date || !$this->to_date) {
      return $this->from_date;
    }
    return $this->from_date . self::PERIOD_SEPARATOR . $this->to_date;
  }

  /**
   * Генерирует pdf файл инвойса
   * @return bool
   */
  public function generateInvoiceFile()
  {
    $generator = new UserPaymentInvoiceGenerator($this);

    return $generator->run();
  }

  /**
   * @param $userId
   * @return ActiveQuery
   */
  public static function UserPaymentsInvoices($userId)
  {
    return UserPayment::find()
      ->distinct(true)
      ->where([self::tableName() . '.user_id' => $userId])
      ->joinWith('invoices')
      ->orderBy(['created_at' => SORT_DESC]);
  }

  /**
   * @return bool
   */
  public function canView()
  {
    return Yii::$app->getUser()->can('PaymentsViewPaymentRule', ['payment' => $this]);
  }

  /**
   * TODO На страницах админа для проверки доступа редактирования используется $this->isReadonly(),
   * для реса используется этот метод. Возможно в этом методе нужно добавить проверку $this->isReadonly()
   * @deprecated Под вопросом
   * @return bool
   */
  public function canUpdate()
  {
    return Yii::$app->getUser()->can('PaymentsUpdatePaymentRule', ['payment' => $this]);
  }

  /**
   * @return bool
   */
  public function canAutoPayout()
  {
    return Yii::$app->getUser()->can('PaymentsAutoPayoutRule', ['payment' => $this]);
  }

  /**
   * @return bool
   */
  public function isManual()
  {
    return $this->type == self::TYPE_MANUAL;
  }

  /**
   * @return bool
   */
  public function isAdminManual()
  {
    return $this->type == self::TYPE_ADMIN_MANUAL;
  }

  /**
   * @return bool
   */
  public function isGenerated()
  {
    return $this->type == self::TYPE_GENERATED;
  }

  /**
   * @param integer $userId
   * @param integer $dateFrom
   * @return \yii\db\BatchQueryResult
   */
  public static function getCompletedPaymentsDateFrom($userId, $dateFrom)
  {
    return self::find()
      ->select(['user_id', 'currency', 'amount', 'payed_at'])
      ->where([
        'user_id' => $userId,
        'status' => self::STATUS_COMPLETED,
      ])
      ->andWhere(['>=', 'payed_at', $dateFrom])
      ->each();
  }

  /**
   * @param $userId
   * @param $currency
   * @return int
   */
  public static function getCompletePaymentSum($userId, $currency)
  {
    return (new Query())
      ->select(['SUM(amount) as amount'])
      ->from(UserPayment::tableName())
      ->where([
        'user_id' => $userId,
        'status' => UserPayment::STATUS_COMPLETED,
        'currency' => $currency,
      ])
      ->scalar()
      ?: 0;
  }

  /**
   * @param $user_id
   * @param $walletId
   * @return int|string
   */
  public static function userPaymentsCount($user_id, $walletId)
  {
    return self::find()->where(['user_id' => $user_id, 'user_wallet_id' => $walletId])->count();
  }

  /**
   * @param $userId
   * @return int|string
   */
  public static function getAwaitingCount($userId)
  {
    return self::find()
      ->where(['user_id' => $userId])
      ->andWhere(['status' => self::$payableStatuses])
      ->count();
  }

  public static function invalidateCache()
  {
    RemoteWalletBalances::invalidateCache();
    TagDependency::invalidate(Yii::$app->cache, self::CACHE_KEY_PAYABLE_SUMMARY_BY_TYPE);
  }


  /**
   * Сумма невыплаченных выплат всех пользователей
   * @return array
   */
  public static function getPayableSummaryGroupedByCurrency()
  {
    $data = (new Query())
      ->select(['SUM(p.amount) as amount', 'p.currency'])
      ->from(self::tableName() . ' p')
      ->innerJoin(
        User::tableName() . ' ut',
        'p.user_id = ut.id'
      )
      ->where(['in', 'p.status', [self::STATUS_AWAITING, self::STATUS_DELAYED, self::STATUS_ERROR]])
      ->groupBy(['p.currency']);

    if ($ignoreIds = Yii::$app->getModule('users')
      ->api('notAvailableUserIds', [
        'userId' => Yii::$app->user->id,
      ])->getResult()
    ) {
      $data->andWhere(['not in', 'p.user_id', $ignoreIds]);
    }

    $result = [];
    foreach ($data->each() as $item) {
      if (!isset($result[$item['currency']])) {
        $result[$item['currency']] = 0;
      }
      $result[$item['currency']] += $item['amount'];
    }

    return [
      'rub' => ArrayHelper::getValue($result, 'rub', 0),
      'usd' => ArrayHelper::getValue($result, 'usd', 0),
      'eur' => ArrayHelper::getValue($result, 'eur', 0),
    ];
  }

  /**
   * @param $userId
   * @param $currency
   * @return float
   */
  public static function getSuccessHoldEarlyPaymentsSum($userId, $currency)
  {
    $subQuery = (new Query())
      ->from(UserBalanceInvoice::tableName())
      ->distinct()
      ->select('user_payment_id')
      ->where([
        'currency' => $currency,
        'user_id' => $userId,
        'type' => UserBalanceInvoice::TYPE_EARLY_PAYMENT,
        'is_hold' => 1
      ]);

    return (float)self::find()
      ->select('SUM(amount)')
      ->innerJoin(['ubi' => $subQuery], 'ubi.user_payment_id = id')
      ->where(['status' => self::STATUS_COMPLETED])
      ->scalar();
  }

  /**
   * @return ActiveQuery
   */
  public function getWalletModel()
  {
    return $this->hasOne(Wallet::class, ['id' => 'wallet_type']);
  }

  /**
   * @return bool
   */
  public function paymentsIsDisabled()
  {
    $settings = UserPaymentSetting::fetch($this->user_id);

    return $settings && $settings->is_disabled ? true : false;
  }

  /**
   * Доступна ли автоматическая выплата для данной выплаты
   * @return bool
   */
  public function isAvailableAutopay()
  {
    $result = true;
    $error = null;

    // Проверка прав (реселлерам разрешено выплачивать только досрочные выплаты)
    if (!$this->canAutoPayout()) {
      $result = false;
      $error = Yii::_t('payments.user-payments.error-has-no-permissions');
    }

    // Проверка статуса выплаты
    if (!$this->isPayable()) {
      $result = false;
      $error = $this->lastError;
    }

    // Проверка наличия доступной платежной системы для отправки (выполнения) выплаты
    $sender = $this->walletModel->getSender($this->currency);
    if (!$sender) {
      $result = false;
      $error = Yii::_t('payments.user-payments.error-paysystem-has-no-sender');
    }

    if ($sender && !$sender->isActive()) {
      $result = false;
      $error = Yii::_t('payments.user-payments.error-paysystem-sender-invalid');
    }
    /** @var AbstractWallet $wallet */
    $wallet = $this->wallet->getAccountObject();
    if (!$wallet->validate()) {
      $allErrors = $wallet->getFirstErrors();
      // TRICKY: Если существует $allErrors['_cardData'] значит нет денег на счету.
      // Ошибку не пишем, т.к. сообщаем об этом во всплывающем окне. Выплату не запрещаем
      $result = isset($allErrors['_cardData']) ? $result : false;

      $error = isset($allErrors['_cardData'])
        ? $error
        : Yii::_t('payments.user-payments.wallet_validation_error') . ' ' . reset($allErrors);
    }

    $this->lastError = $error;

    return $result;
  }


  /**
   * Доступно ли отложение выплаты :)
   * @return bool
   */
  public function isAvailableDelay()
  {
    $result = true;
    $error = null;
    $this->lastError = null;

    // Проверка статуса выплаты
    if ($this->status !== self::STATUS_AWAITING && $this->status !== self::STATUS_DELAYED) {
      $result = false;
      $this->lastError = Yii::_t('payments.user-payments.error-not-delayable-status');
    }

    return $result;
  }

  /**
   * Получаем все кошельки пользователя
   * @param integer|null $user_id
   * @param string|null $currency
   * @param string|null $paysystemsActivity Активность ПС @see Wallet::find()
   * @return array
   */
  public static function getUserWallets($user_id = null, $currency = null, $paysystemsActivity = null)
  {
    if (is_null($user_id)) return [];

    $result = [];
    $userWallets = [];
    $userWalletTypesCount = [];

    /** @var UserWallet $userWallet */
    $userWalletsQuery = UserWallet::find()
      ->andWhere(['user_id' => $user_id, 'is_deleted' => false])
      ->andFilterWhere(['currency' => $currency])
      ->paysystemsActivity($paysystemsActivity);

    foreach ($userWalletsQuery->each() as $userWallet) {
      if (!$userWallet->currency || !$userWallet->wallet_type) continue;
      // Сбор данных пользовательских кошельков
      $userWallets[] = [
        'id' => $userWallet->id,
        'currency' => $userWallet->currency,
        'wallet_type' => $userWallet->wallet_type,
        'label' => $userWallet->getWalletTypeLabel(),
        'label_hint' => $userWallet->getAccountObject()->getUniqueValue(),
      ];

      // Подсчет типов кошельков
      if (!isset($userWalletTypesCount[$userWallet->wallet_type])) {
        $userWalletTypesCount[$userWallet->wallet_type] = 1;
      } else {
        $userWalletTypesCount[$userWallet->wallet_type]++;
      }
    }

    /** @var array $userWallet */
    foreach ($userWallets as $userWallet) {
      $result[] = [
        'id' => $userWallet['id'],
        'name' => $userWallet['currency'] . ' - ' . $userWallet['label']
          // Если в списке больше одного кошелька одного типа, к названию добавляем уникальное значение кошелька, что бы отличить кошельки с одинаковым названием
          . ($userWalletTypesCount[$userWallet['wallet_type']] > 1 ? ' (' . $userWallet['label_hint'] . ')' : null)
      ];
    }

    return $result;
  }

  /**
   * @return null | integer
   */
  public static function getResellerId()
  {
    if (self::$_resellerId) return self::$_resellerId;

    self::$_resellerId = null;
    if ($reseller = Yii::$app->getModule('users')->api('usersByRoles', ['reseller'])->getResult()) {
      self::$_resellerId = current($reseller)['id'];
    }
    return self::$_resellerId;
  }

  /**
   * @param bool|null $paysystemsActivity Активность ПС @see Wallet::find()
   * @return array
   */
  public function getWallets($paysystemsActivity = null)
  {
    return self::getUserWallets($this->user_id, $this->getUserPaymentSetting()->getCurrentCurrency(), $paysystemsActivity);
  }

  /**
   * Получить последнюю ошибку
   * @return null
   */
  public function getLastError()
  {
    $errors = $this->getErrors();
    $lastError = null;
    if (!empty($errors)) {
      $lastError = array_pop($errors)[0];
    }
    return $this->lastError ?: $lastError;
  }

  /**
   * @param $userWalletId
   * @param $currency
   * @return ActiveQuery
   */
  private function limitPaymentsBaseQuery($userWalletId, $currency)
  {
    return UserPayment::find()
      ->andWhere([
        'user_wallet_id' => $userWalletId,
        'currency' => $currency,
      ])
      ->andWhere(['NOT IN', 'status', [self::STATUS_CANCELED]])
      ->with('walletModel');
  }

  /**
   * @param $userWalletId
   * @param $currency
   * @return number
   */
  public function getDailyLimitUse($userWalletId, $currency)
  {
    $payments = $this->limitPaymentsBaseQuery($userWalletId, $currency)
      ->andWhere([
        'between',
        'created_at',
        (new \DateTime)->modify('00:00:00')->getTimestamp(),
        (new \DateTime)->modify('23:59:59')->getTimestamp(),
      ]);

    return $this->calcLimitUse($payments);
  }

  /**
   * @param $userWalletId
   * @param $currency
   * @return number
   */
  public function getMonthlyLimitUse($userWalletId, $currency)
  {
    $payments = $this->limitPaymentsBaseQuery($userWalletId, $currency)
      ->andWhere([
        'between',
        'created_at',
        (new \DateTime)->modify('first day of this month 00:00:00')->getTimestamp(),
        (new \DateTime)->modify('last day of this month 23:59:59')->getTimestamp(),
      ]);

    return $this->calcLimitUse($payments);
  }

  /**
   * Есть ли у партнера незавершенные выплаты
   * @param $userId
   * @param string $currency Валюта инвойса (баланса)
   * @return bool
   */
  public static function hasAwaitingPayments($userId, $currency)
  {
    return UserPayment::find()->where([
      'user_id' => $userId,
      'invoice_currency' => $currency,
      'status' => [self::STATUS_AWAITING, self::STATUS_DELAYED, self::STATUS_PROCESS, self::STATUS_ERROR],
    ])->exists();
  }

  /**
   * Выплата сконвертирована
   * TRICKY Для реселлера не должно быть конвертации, у него есть баланс на каждую валюту
   * @return bool
   * @throws Exception
   */
  public function isConvert()
  {
    if (!$this->currency || !$this->invoice_currency) {
      throw new Exception('Не удалось определить валюту выплаты. Логика приложения может быть нарушена');
    }

    return $this->scenario !== self::SCENARIO_CREATE_RESELLER_PAYMENT && $this->currency != $this->invoice_currency;
  }

  /**
   * Конвертировать сумму
   * В отличии от обычной конвертации, добавлены строгие проверки
   * @param $sum
   * @param $fromCurrency
   * @param $toCurrency
   * @return number
   */
  private function convert($sum, $fromCurrency, $toCurrency)
  {
    return $this->convertInternal($sum, $fromCurrency, $toCurrency, false);
  }

  /**
   * Обратная конвертация
   * В отличии от обычной обратной конвертации, добавлены строгие проверки
   * @see \mcms\payments\components\exchanger\CurrencyCourses::reverseConvert
   * @param $sum
   * @param $fromCurrency
   * @param $toCurrency
   * @return number
   */
  private function reverseConvert($sum, $fromCurrency, $toCurrency)
  {
    return $this->convertInternal($sum, $fromCurrency, $toCurrency, true);
  }

  /**
   * Конветрация/обратная конвертация со строгими проверками.
   * Не используется на прямую, что бы случайно не передать reverseConvert
   * @param number $sum
   * @param string $fromCurrency
   * @param string $toCurrency
   * @param bool $reverseConvert
   * @return number
   * @throws Exception
   */
  private function convertInternal($sum, $fromCurrency, $toCurrency, $reverseConvert)
  {
    /** @var Module $paymentModule */
    /** @var ExchangerPartnerCourses $exchanger */

    if (!$fromCurrency || !$toCurrency) {
      throw new Exception('Не указана валюта для конвертации');
    }
    if ($fromCurrency == $toCurrency) {
      throw new Exception("Нельзя конвертировать $fromCurrency в $toCurrency");
    }

    if ($reverseConvert) {
      return PartnerCurrenciesProvider::getInstance()
        ->getCurrencies()
        ->getCurrency($toCurrency)
        ->convert($sum, $fromCurrency);
    }

    return PartnerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency($fromCurrency)
      ->convert($sum, $toCurrency);
  }

  /**
   * Конвертация invoice_amount в currency
   * @param UserPayment $payment
   * @return number
   */
  public function convertInvoiceToPaymentCurrency($payment = null)
  {
    $payment = $payment ?: $this;
    return $payment->invoice_currency != $payment->currency
      ? $payment->convert($payment->invoice_amount, $payment->invoice_currency, $payment->currency)
      : $payment->invoice_amount;
  }

  /**
   * Конвертация amount в invoice_currency
   * @param UserPayment $payment
   * @param number $amount
   * @return number
   */
  public function convertAmountToInvoiceCurrency($payment = null, $amount = null)
  {
    $payment = $payment ?: $this;
    $amount = $amount ?: $payment->amount;
    return $payment->currency != $payment->invoice_currency
      ? $payment->convert($amount, $payment->currency, $payment->invoice_currency)
      : $amount;
  }

  /**
   * Округлить сумму
   * @param number $sum
   * @return number
   */
  public function round($sum)
  {
    return round($sum, 2);
  }

  /**
   * Округлить сумму в меньшую сторону
   * @param number $sum
   * @return number
   */
  public function floor($sum)
  {
    return floor($sum * 100) / 100;
  }

  /**
   * Округлить сумму в большую сторону
   * @param number $sum
   * @return number
   */
  private function ceil($sum)
  {
    return ceil($sum * 100) / 100;
  }

  /**
   * Курс конвертации инвойса в валюту выплаты
   * @return number
   */
  private function getConvertInvoiceToPaymentCourse()
  {
    return PartnerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency($this->invoice_currency)
      ->{'getTo' . lcfirst($this->currency)}();
  }

  /**
   * @param $payments
   * @return int|number
   */
  private function calcLimitUse(ActiveQuery $payments)
  {
    $limit = 0;
    /** @var UserPayment $payment */
    foreach ($payments->each() as $payment) {
      $limit += $payment->amount;
    }
    return $limit;
  }

  /**
   * Получить мин/макс для отображения в админке
   *
   * Пример использования:
   * - пользователь хочет вывести 100 RUB на webmoney
   * - в админке указана минимальная сумма вывода 100 RUB и комиссия 3%
   * - при валидации минимальной суммы вывода используется сумма с наложенными процентами, то есть сравниванивается
   * не 100 RUB, а 97 RUB
   * - что бы пользователю не пришлось самому считать какой будет сумма после наложения процентов, при выводе лимитов
   * мы прибавляем проценты
   * - например лимит в админке лимит 100 RUB, а мы отображаем 103.9 RUB
   * - теперь пользователю достаточно ввести в форму 103.9 RUB и введенная сумма будет соответствовать минималке указанной в админке (100 RUB)
   *
   * TRICKY Данная схема работает только если ПС имеет отрицательный профит
   * TRICKY В JS есть аналогичная хрень, нужно поддерживать её актуальность
   *
   * @param number $limit Сумма лимита
   * @param string $type Тип min или max
   * @return number
   */
  private function modifyMinMaxToShow($limit, $type)
  {
    if (!in_array($type, ['min', 'max'])) throw new InvalidParamException;

    $profitPercent = $this->getPaysystemPercent() - $this->early_payment_percent;

    if ($profitPercent < 0) {
      return $limit / (100 - abs($profitPercent)) * 100;
    } else if ($profitPercent > 0 && $type == 'max') {
      // Для ПС с положительным процентом для минималки модификация не производится для красоты
      // То есть в таком случае выводимая минималка - рекомендованная, настоящая минималка меньше
      return $limit / (100 + abs($profitPercent)) * 100;
    } else {
      return $limit;
    }
  }

  /**
   * TRICKY Для выплаты реса всегода накладывается только дефолтный процент ПС.
   * TRICKY Иначе накладываем обычный процент, который он может настроить и сам
   *
   * @return float
   */
  private function getPaysystemPercent()
  {
    if ($this->scenario === self::SCENARIO_CREATE_RESELLER_PAYMENT) {
      return ArrayHelper::getValue(Yii::$app->params['paysystem-percents'], $this->walletModel->code);
    }

    return $this->walletModel->profit_percent;
  }

  /**
   * Процент за процессинг
   * @return float|int
   */
  // TODO Добавлено поле rgk_processing_percent. Нужно использовать его вместо этого метода в местах, где нужно вывести процессинговый процент на момент создания выплаты
  public function getActualProcessingPercent()
  {
    return Module::getProcessingPercent();
  }

  /**
   * Индивидуальный процесс реселлера
   * @return float|int
   */
  public function getResellerIndividualPercent()
  {
    return Module::getResellerPercent();
  }

  /**
   * @return array
   */
  public static function getResellerAwaitingPaymentSums()
  {
    return (new Query())
      ->select([
        'rub' => new Expression("SUM(IF(currency = 'rub', amount, 0))"),
        'usd' => new Expression("SUM(IF(currency = 'usd', amount, 0))"),
        'eur' => new Expression("SUM(IF(currency = 'eur', amount, 0))")
      ])
      ->from(self::tableName())
      ->where([
        'user_id' => self::getResellerId(),
        'status' => [self::STATUS_AWAITING, self::STATUS_DELAYED, self::STATUS_PROCESS, self::STATUS_ERROR],
      ])
      ->one();
  }

  /**
   * хз как назвать покороче :(
   * @return array
   */
  public static function getPartnerAwaitingPaysumsByWalletType()
  {
    return (new Query())
      ->select([
        'wallet_type',
        'sum' => new Expression("SUM(amount)"),
        'currency',
      ])
      ->from(['pay' => self::tableName()])
      ->andWhere(['<>', 'user_id', self::getResellerId()])
      ->andWhere(['status' => [self::STATUS_AWAITING, self::STATUS_DELAYED, self::STATUS_ERROR]])
      ->groupBy(['wallet_type', 'currency'])
      ->all();
  }

  /**
   * Рассчет коммиссий для автоматической выплаты
   * @return \stdClass|null
   */
  public function calcAutoProcess()
  {
    // TODO Заменить stdClass объектом (extends Object)

    if (!$this->calcResellerCommission()) return null;

    $data = new \stdClass;
    $data->currency = $this->currency;

    // Реселлер получит (сумма, которую заплатит партнер реселлеру)
    $data->resellerProfit = -$this->calcResellerCommission()->partnerCost;

    // Полная стоимость для реселлера (сумма, которую реселлер должен отдать)
    $data->resellerFullCost = $this->amount;

    return $data;
  }

  /**
   * Рассчет коммиссий для выплаты через РГК
   * @return \stdClass|null
   */
  public function calcRgkProcess()
  {
    // TODO Заменить stdClass объектом (extends Object)
    if (
      !$this->rgk_paysystem_percent
      || !$this->rgk_processing_percent
      || !$this->calcResellerCommission()
    ) return null;

    $data = new \stdClass;
    $data->currency = $this->currency;

    // Комиссия ПС РГК
    $data->rgkPaysystemPercent = $this->rgk_paysystem_percent;
    $data->rgkPaysystemCommission = $this->request_amount * $data->rgkPaysystemPercent / 100;

    // Комиссия за процессинг РГК
    $data->rgkProcessingPercent = $this->rgk_processing_percent;
    $data->rgkProcessingCommission = $this->request_amount * $data->rgkProcessingPercent / 100;

    // Суммарная комиссия РГК
    $data->rgkPercent = $data->rgkPaysystemPercent + $data->rgkProcessingPercent;
    $data->rgkCommission = $data->rgkPaysystemCommission + $data->rgkProcessingCommission;

    // Стоимость для реселлера (комиссии)
    $data->resellerCostPercent = -($this->calcResellerCommission()->percent - ($data->rgkPaysystemPercent + $data->rgkProcessingPercent));
    $data->resellerCost = $this->request_amount * $data->resellerCostPercent / 100;

    // Полная стоимость для реселлера (сумма, которую реселлер должен отдать)
    $data->resellerFullCost = $this->amount - $data->rgkCommission;

    // Профит реселлера (разница между тем, сколько реселлер взял у партнера и отдал РГК)
    $data->resellerProfit = $data->rgkCommission - $data->resellerCost;

    return $data;
  }

  /**
   * Сумма, которую реселлер возьмет с партнера
   * @param bool $byInvoice Расчет в валюте инвойса или выплаты
   * TODO проверить нужен ли этот параметр, может имеет смысл везде поставить TRUE, вроде логично, но может и нет.
   *
   * @return null|\stdClass
   */
  public function calcResellerCommission($byInvoice = false)
  {
    // TODO Заменить stdClass объектом (extends Object)
    $data = static::calcResellerPercentByValues($this->reseller_paysystem_percent, $this->early_payment_percent);
    if (!$data) return null;

    $data->currency = $byInvoice ? $this->invoice_currency : $this->currency;
    // Комиссия реселлера (сумма, которую реселлер возьмет с партнера)
    $data->amount = ($byInvoice ? $this->invoice_amount : $this->request_amount) * $data->percent / 100;

    // Стоимость для партнера (алиасы для более простого понимания)
    $data->partnerCostPercent = $data->percent;
    $data->partnerCost = $data->amount;

    return $data;
  }

  /**
   * Процент, который реселлер возьмет с партнера
   * @param number $paysystemPercent
   * @param number $earlyPaymentPercent
   * @return null|\stdClass
   */
  public static function calcResellerPercentByValues($paysystemPercent, $earlyPaymentPercent)
  {
    if (!$paysystemPercent) return null;

    $data = new \stdClass;
    $data->paysystem_percent = $paysystemPercent;
    $data->early_percent = $earlyPaymentPercent;
    $data->percent = $data->paysystem_percent - $data->early_percent;

    return $data;
  }

  /**
   * @return array
   */
  public function getSelectFields()
  {
    $fields = [
      'invoice_currency' => 'invoice_currency',
      'currency' => 'currency',
      'amount' => 'SUM(amount)',
      'invoice_amount' => 'SUM(invoice_amount)',
    ];

    return $fields;
  }

  /**
   * @inheritdoc
   */
  public function getQuery()
  {

    $q = new UserPaymentSearch([
      'onlyPartners' => true,
      'ignore_user_id' => Yii::$app->user->id,
    ]);
    $q->load(Yii::$app->request->queryParams);

    $query = UserPaymentSearch::find();
    /* @var $query ActiveQuery */
    $query = $query
      ->distinct()
      ->joinWith([
        'user',
        'userPaymentSetting',
        'invoices'
      ]);

    $query = $q->handleFilters($query);

    return $query;
  }

  /**
   * @param $field string
   * @return array|bool получить строку ИТОГО
   */
  private function getResults($field)
  {
    $subQuery = '(' . $this->getQuery()
        ->createCommand()->getRawSql() . ')';
    $subQueryAlias = 'rows';
    $subQuerySelects = $this->getSelectFields();

    $querySelects = [];
    foreach ($subQuerySelects AS $fieldName => $expression) {

      switch ($fieldName) {
        case 'amount':
          $querySelects[$fieldName] = new Expression(
            'SUM(rows.amount)'
          );
          break;
        case 'invoice_amount':
          $querySelects[$fieldName] = new Expression(
            'SUM(rows.invoice_amount)'
          );
          break;
        case 'invoice_currency':
          $querySelects[$fieldName] = new Expression(
            'invoice_currency'
          );
          break;
        case 'currency':
          $querySelects[$fieldName] = new Expression(
            'currency'
          );
          break;
      }
    }

    $query = (new Query())
      ->select($querySelects)
      ->from([$subQueryAlias => $subQuery])
      ->groupBy($field === 'invoice_amount' ? ['invoice_currency'] : ['currency']);

    return $query->all();
  }

  /**
   * @param $field
   * @return mixed
   */
  public function getResultValue($field)
  {
    $values = [];
    foreach ($this->getResults($field) as $value) {
      $currency = ($field === 'invoice_amount'
        ? $value['invoice_currency']
        : $value['currency']
      );
      $values[$currency] = (float)ArrayHelper::getValue($value, $field, 0);
    }

    $result = '';
    foreach ($values as $currency => $value) {
      $result .= Yii::$app->getFormatter()->asPrice($value, $currency) . Html::tag('br');
    }

    return $result;
  }

  /**
   * @return PaySystemApi|ActiveRecord
   */
  public function getSenderApi()
  {
    return $this->walletModel->getSenderApi($this->currency)->one();
  }

  /**
   * @return int|null
   */
  public function getSenderApiId()
  {
    return $this->walletModel->getSenderApiId($this->currency);
  }

  /**
   * Текущий уровень предупреждения отложенной выплаты
   * null - либо дата не указана, либо ещё не скоро
   * 2 - скоро надо выплатить
   * 1 - ещё есть шанс успеть
   * 0 - просрочили
   * @return int|null
   */
  public function getCurrentDelayLevel()
  {
    if ($this->status != self::STATUS_DELAYED) return null;
    if (!$this->pay_period_end_date) return null;
    /** @var Module $module */
    $module = Yii::$app->getModule('payments');

    $now = Yii::$app->formatter->asDate('today', 'php:Y-m-d');
    $level2 = Yii::$app->formatter->asDate("today + {$module->getDelayLevel2()} days", 'php:Y-m-d');
    $level1 = Yii::$app->formatter->asDate("today + {$module->getDelayLevel1()} days", 'php:Y-m-d');
    $delayed = Yii::$app->formatter->asDate($this->pay_period_end_date, 'php:Y-m-d');

    if ($delayed < $now) {
      // просрочили
      return 0;
    }

    if ($delayed >= $now && $delayed < $level1) {
      // ещё есть шанс успеть
      return 1;
    }

    if ($delayed >= $level1 && $delayed < $level2) {
      // скоро надо выплатить
      return 2;
    }

    return null;
  }

  /**
   * Установить тип инвойса
   * @param string|null $value
   */
  public function setInvoiceType($value)
  {
    $this->invoiceType = $value;
  }

  /**
   * Получить тип инвойса
   * @return string|null
   */
  public function getInvoiceType()
  {
    return $this->invoiceType;
  }

  /**
   * @return int
   */
  public function getIsWalletVerified()
  {
    return $this->userWallet->is_verified;
  }

  /**
   * Сколько осталось выплатить
   * @param bool $refreshCache TRUE если нужно взять самое актуальное значение из БД
   * @return float
   */
  public function getRemainSum($refreshCache = false)
  {
    return (float)$this->amount - $this->getChunksSum($refreshCache);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getChunks()
  {
    return $this->hasMany(UserPaymentChunk::class, ['payment_id' => 'id']);
  }

  /**
   * Сумма оплаченных частей.
   * @param bool $refreshCache TRUE если нужно взять самое актуальное значение из БД
   * @return float
   */
  public function getChunksSum($refreshCache = false)
  {
    if (!$refreshCache && $this->_chunksSum !== null) return $this->_chunksSum;

    if ($refreshCache) {
      $this->_chunksSum = $this->getChunks()->sum('amount');
      return $this->_chunksSum;
    }

    $this->_chunksSum = 0;

    foreach ($this->chunks as $chunk) {
      $this->_chunksSum += (float)$chunk->amount;
    }

    return $this->_chunksSum;
  }
}