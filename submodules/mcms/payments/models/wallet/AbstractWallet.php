<?php

namespace mcms\payments\models\wallet;

use mcms\common\traits\Translate;
use mcms\payments\models\wallet\wire\iban\Wire;
use mcms\payments\Module;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use mcms\payments\models\UserPayment;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * TRICKY По умолчанию все аттрибуты (@see AbstractWallet::getWalletDetailViewAttributes() ) модели отображаются в детальной инфмормации о кошельке в админке
 * TRICKY Порядок объявления свойств в модели влияет на их порядок отображения в детальной информации о кошельке в админке
 * TRICKY Модель применяется фильтрацию html для всех свойств
 * Class AbstractWallet
 * @package mcms\payments\models\wallet
 */
abstract class AbstractWallet extends Model
{
  use Translate;
  const LANG_PREFIX = 'payments.wallets.';
  const SCENARIO_RUB = 'scenario_rub';
  const SCENARIO_EUR = 'scenario_eur';
  const SCENARIO_USD = 'scenario_usd';
  const SCENARIO_ADMIN = 'scenario_admin';
  const EXPORT_DELIMITER = ';';
  const EXPORT_ENCLOSURE = '"';
  const FORMATTER_PROTECTED_STRING = 'protectedString';

  /**
   * Список доступных для этой ПС валют. Какие-то из валют можно отключить в админке
   * @var array
   */
  public static $currency = [];

  public static $isSingleCurrency = false;

  /**
   * Является ли тип кошелька локальным для RU.
   * @var bool
   */
  protected static $isLocalityRu = false;

  private $selectedCurrency;

  private $type;

  private $userId;

  private $isProtectedView = false;

  /** @var Module */
  protected $module;

  /** @var Wallet */
  private $walletModel;

  /**
   * @inheritDoc
   */
  public function __construct($config = [])
  {
    parent::__construct($config);
    $this->module = Yii::$app->getModule('payments');
    $this->walletModel = Wallet::findOne($this->type);
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    // TODO Перенести в beforeValidate, так как вызов parent::rules() можно забыть сделать
    $attributes = array_keys($this->getAttributes());
    return [
      [$attributes, 'filter', 'filter' => 'strip_tags'],
      [$attributes, 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    $parentScenarios = parent::scenarios();
    $parentScenarios[self::SCENARIO_ADMIN] = array_keys($this->getAttributes());

    return $parentScenarios;
  }

  /**
   * Проверить существование кошелька.
   * Чаще всего проверка производится с помощью стороннего сервиса
   * @return bool
   */
  final public function validateExistence()
  {
    //TRICKY отключаем валидацию в админке при добавлении/редактировании кошельков
    $disabledAdminValidateWallets = ArrayHelper::getValue(Yii::$app->params, 'adminPaysystemsValidateDisable', []);
    if ($this->scenario === self::SCENARIO_ADMIN && in_array($this->getWallet()->code, $disabledAdminValidateWallets, true)) {
      return true;
    }

    try {
      return $this->validateExistenceInternal() !== false;
    } catch (\Exception $exception) {
      // Если при валидации было выброшено исключение, кошелек все равно должен быть создан
      Yii::error('Не удалось провести валидацию существования кошелька', __METHOD__);
      return true;
    }
  }

  /**
   * Проверить существование кошелька
   * TRICKY Если ПС не настроена или недоступна, нужно возвращать true, что бы управление кошельками не зависило от внешних сервисов!
   * TRICKY Метод должен возвращать true или false, все остальные значения считаются true
   * @see validateExistence()
   * @return bool
   * @throws Exception
   */
  protected function validateExistenceInternal()
  {
    // TODO Порефакторить: блок с получением настроек дублируется во всех подобных методах. Надо вынести в общий и если не удалось получить настройки, или возвращать true

    throw new Exception('Валидация существования кошелька не поддерживается');
  }

  /**
   * TRICKY не доставайте этот метод через магию!!! только напрямую через метод! Например в яндекс кошельке есть одноименное поле wallet
   * по неосторожности назвали этот геттер так как и поля в кошельках, а теперь просто дофига где исправлять чтобы избежать будущих проблем
   * @return Wallet
   */
  public function getWallet()
  {
      return $this->walletModel;
  }

  /**
   * @var string $language
   * @return string
   */
  public static function getName($language = null)
  {

  }

  /**
   * Получить значение отличающие кошельки одинакового типа.
   * Например номер кошелька для webmoney или email для яндекс денег
   * @return string
   */
  abstract public function getUniqueValue();

  /**
   * Получить значение отличающие кошельки одинакового типа в защищенном виде
   * То есть часть значения заменяется на звездочки
   * @return string
   */
  public function getUniqueValueProtected()
  {
    return $this->getUniqueValue();
  }

  /**
   * Список полей для отображения в детальной информации о кошельке
   * TRICKY Для изменения списка полей полей нужно переопределять метод @see handleDetailViewAttribute()
   * Метод handleDetailViewAttribute сделан для того, что бы не потерять проверку canViewDetailWalletInfo
   * @return array Массив полей в формате @see DetailView::$attributes
   */
  final public function getWalletDetailViewAttributes()
  {
    if (!$this->canViewDetailWalletInfo()) {
      return [];
    }

    $attributes = [];
    $modelAttributes = $this->getAttributes();
    foreach ($modelAttributes as $key => $value) {
      // Список аттрибутов специально возвращается в виде массива проиндексированного по коду аттрибута,
      // что бы проще было удалять ненужные аттрибуты в переопределенном методе
      $attributes[$key] = ['attribute' => $key];
    }

    return $this->handleDetailViewAttribute($attributes);
  }

  /**
   * Изменение списка полей для отображения в детальной информации о кошельке
   * @param array $attributes
   * @return array
   */
  protected function handleDetailViewAttribute($attributes)
  {
    return $attributes;
  }

  public function __toString()
  {
    return json_encode($this->getAttributes());
  }

  public function getType()
  {
    return $this->type;
  }

  /**
   * @param integer $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  public function getUserId()
  {
    return $this->userId;
  }

  /**
   * @param integer $type
   */
  public function setUserId($userId)
  {
    $this->userId = $userId;
  }


  /**
   * @return array
   */
  public function getFormFields()
  {
    return array_keys($this->getAttributes());
  }

  public function getAdminFormFields()
  {
    return $this->getFormFields();
  }

  /**
   * Кастомизация полей кошелька в партнерке
   * @param ActiveForm $form
   * @param array $options
   * @param string $submitButtonSelector
   * @return array Массив вида:
   * [
   *     'АТТРИБУТ' => $this->getForm($form)->{МЕТОД_КЛАССА_WalletForm}(),
   *     'wallet' => $this->getForm($form)->maskedTextInput('wallet', [], ['mask' => '[RZE]999999999999']),
   * ]
   * @see WalletForm
   * @see getForm()
   * @see getAdminCustomFields()
   */
  public function getCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    return [];
  }

  /**
   * Кастомизация полей кошелька в админке.
   * По умолчанию аналогично кастомным полям в партнерке
   * @param ActiveForm $form
   * @param array $options
   * @return array
   * @see getCustomFields()
   */
  public function getAdminCustomFields(ActiveForm $form, $options = [])
  {
    return $this->getCustomFields($form, $options);
  }

  private $walletForm;

  /**
   * @param ActiveForm $form
   * @param int|null $userWalletId
   * @return WalletForm
   */
  public function getForm(ActiveForm $form, $userWalletId = null)
  {
    if (!$this->walletForm) {
      $this->walletForm = new WalletForm([
        'form' => $form,
        'wallet' => $this,
        'userWalletId' => $userWalletId,
      ]);
    }
    return $this->walletForm;
  }


  /**
   * @return array
   */
  public static function getCurrencies()
  {
    return is_array(static::$currency) ? static::$currency : [static::$currency];
  }

  /**
   * Активные валюты платежной системы
   * @return array
   */
  public function getActiveCurrencies()
  {
    $module = $this->module;
    $result = [];

    if ($this->walletModel->is_rub) {
      $result[] = $module::RUB;
    }
    if ($this->walletModel->is_usd) {
      $result[] = $module::USD;
    }
    if ($this->walletModel->is_eur) {
      $result[] = $module::EUR;
    }

    return $result;
  }

  /**
   * @param $attribute
   * @return string
   */
  public function attributePlaceholder($attribute)
  {
    return ArrayHelper::getValue($this->attributePlaceholders(), $attribute)
      ?: ArrayHelper::getValue($this->attributePlaceholders(), [$this->getScenario(), $attribute]);
  }

  /**
   * @return array
   */
  public function attributePlaceholders()
  {
    return [];
  }

  /**
   * Получение строки для экспорта
   * @return string
   */
  public function getExportString()
  {
    return implode(', ', array_map(function ($attribute) {
      return $this->getAttributeLabel($attribute) . ': "' . $this->$attribute . '"';
    }, $this->formFields));
  }

  /**
   * @return string
   */
  public function getSelectedCurrency()
  {
    return $this->selectedCurrency;
  }


  /**
   * Получение списка параметров для экспорта в csv: данных, разделителя и кавычек
   * @param UserPayment $payment
   * @return array
   */
  public static function getExportRowParameters(UserPayment $payment)
  {
    return [
      [],
      self::EXPORT_DELIMITER,
      self::EXPORT_ENCLOSURE
    ];
  }

  public function canViewDetailWalletInfo()
  {
    return Yii::$app->user->can('PaymentsWalletDetailView');
  }

  /**
   * Атрибуты, для которых будет применяться FORMATTER_PROTECTED_STRING форматтер
   *
   * @return array
   */
  public function protectedAttributes()
  {
    return [];
  }

  public function getViewAttributes()
  {
    $protectedAttributes = $this->isProtectedView() ? array_flip($this->protectedAttributes()) : [];
    return array_map(function ($item) use ($protectedAttributes) {
      return [
        'attribute' => $item,
        'format' => array_key_exists($item, $protectedAttributes) ? self::FORMATTER_PROTECTED_STRING : null
      ];
    }, array_combine($this->attributes(), $this->attributes()));
  }

  public function setProtectedView()
  {
    $this->isProtectedView = true;
  }

  public function setNotProtectedView()
  {
    $this->isProtectedView = false;
  }

  public function isProtectedView()
  {
    return $this->isProtectedView;
  }

  public function isEmpty()
  {
    foreach ($this->attributes() as $attribute) {
      if (!empty($this->{$attribute})) {
        return false;
      }
    }

    return true;
  }

  /**
   * Получить минимальную сумму по выплатам
   * @return mixed|null
   */
  public function getMinPayoutSum()
  {
    $module = $this->module;
    switch ($this->selectedCurrency) {
      case $module::RUB:
      default:
        return $this->getMinPayoutSumRub();
      case $module::EUR:
        return $this->getMinPayoutSumEur();
      case $module::USD:
        return $this->getMinPayoutSumUsd();
    }
  }

  /**
   * @return mixed|null
   */
  public function getMinPayoutSumRub()
  {
    return $this->walletModel->rub_min_payout_sum;
  }

  /**
   * @return mixed|null
   */
  public function getMinPayoutSumEur()
  {
    return $this->walletModel->eur_min_payout_sum;
  }

  /**
   * @return mixed|null
   */
  public function getMinPayoutSumUsd()
  {
    return $this->walletModel->usd_min_payout_sum;
  }

  public function getMaxPayoutByCurrency($currency)
  {
    return $this->walletModel->getMaxPayoutByCurrency($currency);
  }

  public function getMaxPayout()
  {
    return $this->walletModel->getMaxPayoutByCurrency($this->getSelectedCurrency());
  }

  public function getPayoutLimitDaily()
  {
    return $this->walletModel->getPayoutLimitDailyByCurrency($this->getSelectedCurrency());
  }

  public function getPayoutLimitMonthly()
  {
    return $this->walletModel->getPayoutLimitMonthlyByCurrency($this->getSelectedCurrency());
  }

  public function getPayoutLimitDailyByCurrency($currency)
  {
    return $this->walletModel->getPayoutLimitDailyByCurrency($currency);
  }

  public function getPayoutLimitMonthlyByCurrency($currency)
  {
    return $this->walletModel->getPayoutLimitMonthlyByCurrency($currency);
  }

  /**
   * @return float
   */
  public function getProfitPercent()
  {
    return $this->walletModel->profit_percent;
  }

  /**
   * Процент реселлера с учетом процента за досрочную выплату
   * @see Wallet::calcResellerPercent()
   * @param int $userId
   * @return number
   */
  public function calcResellerPercent($userId)
  {
    return $this->walletModel->calcResellerPercent($userId);
  }

  /**
   * @return string
   */
  public function getInfo()
  {
    return $this->walletModel->info;
  }

  public function getFileUrl($field)
  {
    return '/uploads' . $this->{$field};
  }

  /**
   * @return bool
   */
  public static function isLocalityRu()
  {
    return static::$isLocalityRu;
  }

  public function getIcon()
  {
    return Yii::$app->paysystemIcons->getIcon(self::class, $this->getUniqueValue());
  }

  public function getIconSrc()
  {
    return Yii::$app->paysystemIcons->getIconSrc(self::class, $this->getUniqueValue());
  }

  public static function getDefaultIcon()
  {
    return Yii::$app->paysystemIcons->getDefaultIcon(self::class);
  }

  public static function getDefaultIconSrc()
  {
    return Yii::$app->paysystemIcons->getDefaultIconSrc(self::class);
  }

  /**
   * Возвращает дополнительную инфу по комиссии wire
   * @return string
   */
  public function getWalletCommissionInfo()
  {
    return $this instanceof Wire
      ? Yii::_t('partners.payments.only_bank_fees')
      : '';
  }
}