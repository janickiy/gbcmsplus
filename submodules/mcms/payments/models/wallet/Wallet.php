<?php

namespace mcms\payments\models\wallet;

use mcms\common\multilang\MultiLangModel;
use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\Module;
use rgk\utils\helpers\Html;
use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\base\Exception;
use yii\widgets\DetailView;

/**
 * Class Wallet
 *
 * @property string $id
 * @property string $is_active
 * @property string $name
 * @property string $info
 * @property string $code
 * @property float $profit_percent
 * @property integer $is_check_file_required Квитанция обязательна
 * @property integer $is_check_file_show Возможность прикладывания квитанции
 * @property integer $is_invoice_file_required
 * @property integer $is_invoice_file_show
 * @property integer $is_mgmp_payments_enabled
 *
 * @property integer $is_rub доступность рубля
 * @property integer $is_usd доступность доллара
 * @property integer $is_eur доступность евро
 *
 * @property float $rub_min_payout_sum
 * @property float $usd_min_payout_sum
 * @property float $eur_min_payout_sum
 *
 * @property float $rub_max_payout_sum
 * @property float $usd_max_payout_sum
 * @property float $eur_max_payout_sum
 *
 * @property float $rub_payout_limit_daily
 * @property float $usd_payout_limit_daily
 * @property float $eur_payout_limit_daily
 *
 * @property float $rub_payout_limit_monthly
 * @property float $usd_payout_limit_monthly
 * @property float $eur_payout_limit_monthly
 *
 * @property string $rub_sender_api_id
 * @property string $usd_sender_api_id
 * @property string $eur_sender_api_id
 *
 * @property PaySystemApi $rubSenderApi
 * @property PaySystemApi $usdSenderApi
 * @property PaySystemApi $eurSenderApi
 */
class Wallet extends MultiLangModel
{
  const WALLET_TYPE_WEBMONEY = 1;
  const WALLET_TYPE_YANDEX = 2;
  const WALLET_TYPE_EPAYMENTS = 3;
  const WALLET_TYPE_PAYPAL = 5;
  const WALLET_TYPE_PAXUM = 6;
  const WALLET_WIRE_IBAN = 7;
//  const WALLET_WIRE_ACCOUNT = 8;
  const WALLET_TYPE_CARD = 10;
  const WALLET_TYPE_PRIVATE_PERSON = 11;
  const WALLET_TYPE_JURIDICAL_PERSON = 12;
  const WALLET_TYPE_QIWI = 13;
  const WALLET_TYPE_CAPITALIST = 14;
  const WALLET_TYPE_USDT = 15;

  private $_activeCurrencies;

  /**
   * @return array
   */
  public function getMultilangAttributes()
  {
    return [
      'name',
      'info'
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'wallets';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    $limitFieldsRub = [
      'rub_min_payout_sum', 'rub_max_payout_sum', 'rub_payout_limit_daily', 'rub_payout_limit_monthly',
    ];
    $limitFieldsUsd = [
      'usd_min_payout_sum', 'usd_max_payout_sum', 'usd_payout_limit_daily', 'usd_payout_limit_monthly',
    ];
    $limitFieldsEur = [
      'eur_min_payout_sum', 'eur_max_payout_sum', 'eur_payout_limit_daily', 'eur_payout_limit_monthly',
    ];

    return [
      [['is_active', 'is_mgmp_payments_enabled', 'is_rub', 'is_usd', 'is_eur'], 'boolean'],
      [['is_active'], 'required'],
      [['code'], 'string'],
      [['rub_sender_api_id', 'usd_sender_api_id', 'eur_sender_api_id'], 'checkSenderIsActive'],
      [['name'], 'validateArrayRequired'],
      [['info'], 'validateArrayString'],

      // Если оставили пустое поле, то берём процент из конфига
      ['profit_percent', 'default', 'value' => $this->getDefaultProfitPercent()],

      [['code', 'profit_percent', 'usd_min_payout_sum', 'eur_min_payout_sum', 'rub_min_payout_sum'], 'required'],
      [array_merge(['profit_percent'], $limitFieldsRub, $limitFieldsUsd, $limitFieldsEur), 'double'],
      [$limitFieldsRub, 'compare', 'compareValue' => 0, 'operator' => '>', 'when' => function ($model) {
        return in_array('rub', $model->getCurrencies());
      }],
      [$limitFieldsUsd, 'compare', 'compareValue' => 0, 'operator' => '>', 'when' => function ($model) {
        return in_array('usd', $model->getCurrencies());
      }],
      [$limitFieldsEur, 'compare', 'compareValue' => 0, 'operator' => '>', 'when' => function ($model) {
        return in_array('eur', $model->getCurrencies());
      }],
      [['is_check_file_required', 'is_check_file_show', 'rub_sender_api_id', 'usd_sender_api_id', 'eur_sender_api_id', 'is_invoice_file_required', 'is_invoice_file_show'], 'safe'],
      ['is_check_file_show', 'required', 'when' => function ($model) {
        return $model->is_check_file_required;
      }],
      ['is_invoice_file_show', 'required', 'when' => function ($model) {
        return $model->is_invoice_file_required;
      }],
    ];
  }

  public function beforeValidate()
  {
    $this->profit_percent = str_replace(',', '.', $this->profit_percent);

    return parent::beforeValidate();
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    if ($this->isUserCanEditAllWalletFields()) {
      return parent::scenarios();
    }

    return [self::SCENARIO_DEFAULT => ['is_active', 'profit_percent', 'is_check_file_required',
      'is_check_file_show', 'is_invoice_file_required', 'is_invoice_file_show', 'is_rub', 'is_usd', 'is_eur']];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'is_active' => Yii::_t('payments.wallets.is_active'),
      'name' => Yii::_t('payments.wallets.wallet_name'),
      'info' => Yii::_t('payments.wallets.wallet_info'),
      'profit_percent' => Yii::_t('payments.wallets.wallet_profit_percent'),

      'min_payout_sum' => Yii::_t('payments.wallets.min_payout_sum'),
      'usd_min_payout_sum' => Yii::_t('payments.wallets.min_payout_sum_usd'),
      'eur_min_payout_sum' => Yii::_t('payments.wallets.min_payout_sum_eur'),
      'rub_min_payout_sum' => Yii::_t('payments.wallets.min_payout_sum_rub'),

      'usd_sender_api_id' => Yii::_t('payments.wallets.sender_api_id_usd'),
      'eur_sender_api_id' => Yii::_t('payments.wallets.sender_api_id_eur'),
      'rub_sender_api_id' => Yii::_t('payments.wallets.sender_api_id_rub'),

      'max_payout_sum' => Yii::_t('payments.wallets.max_payout_sum'),
      'usd_max_payout_sum' => Yii::_t('payments.wallets.max_payout_sum_usd'),
      'eur_max_payout_sum' => Yii::_t('payments.wallets.max_payout_sum_eur'),
      'rub_max_payout_sum' => Yii::_t('payments.wallets.max_payout_sum_rub'),

      'payout_limit_daily' => Yii::_t('payments.wallets.payout_limit_daily'),
      'usd_payout_limit_daily' => Yii::_t('payments.wallets.payout_limit_daily_usd'),
      'eur_payout_limit_daily' => Yii::_t('payments.wallets.payout_limit_daily_eur'),
      'rub_payout_limit_daily' => Yii::_t('payments.wallets.payout_limit_daily_rub'),

      'payout_limit_monthly' => Yii::_t('payments.wallets.payout_limit_monthly'),
      'usd_payout_limit_monthly' => Yii::_t('payments.wallets.payout_limit_monthly_usd'),
      'eur_payout_limit_monthly' => Yii::_t('payments.wallets.payout_limit_monthly_eur'),
      'rub_payout_limit_monthly' => Yii::_t('payments.wallets.payout_limit_monthly_rub'),

      'is_check_file_required' => Yii::_t('payments.wallets.attribute-is_check_file_required'),
      'is_check_file_show' => Yii::_t('payments.wallets.attribute-is_check_file_show'),

      'is_invoice_file_required' => Yii::_t('payments.wallets.attribute-is_invoice_file_required'),
      'is_invoice_file_show' => Yii::_t('payments.wallets.attribute-is_invoice_file_show'),
      'is_rub' => Yii::_t('payments.wallets.attribute-is_rub'),
      'is_usd' => Yii::_t('payments.wallets.attribute-is_usd'),
      'is_eur' => Yii::_t('payments.wallets.attribute-is_eur'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributeHints()
  {
    return [
      'is_invoice_file_required' => Yii::_t('payments.wallets.hint_not_taken_when_creating_payment'),
      'is_check_file_required' => Yii::_t('payments.wallets.hint_not_taken_when_creating_payment'),
    ];
  }

  /**
   * @param bool|null $activity Активность ПС
   * @return ActiveQuery
   */
  public static function find($activity = null)
  {
    $query = parent::find();

    if (is_bool($activity)) {
      $query->andWhere(['or',
        ['is_rub' => 1],
        ['is_usd' => 1],
        ['is_eur' => 1],
        ]);
      $query->andWhere(['is_active' => $activity]);
    }

    return $query;
  }

  public function checkSenderIsActive($attribute)
  {
    if (!$this->getAttribute($attribute)) return true;

    $api = PaySystemApi::findOne($this->getAttribute($attribute));
    if (!$api) {
      $this->addError($attribute, Yii::_t('payments.wallets.error-paysystem-api-not-found'));
      return false;
    }

    if (!$api->isValidSettings()) {
      $this->addError($attribute, Yii::_t('payments.wallets.error-paysystem-api-not-configured'));
      return false;
    }

    return true;
  }

  /**
   * Сумма минимальной выплаты
   * @param string $currency rub, usd, eur
   * @return number
   */
  public function getMinPayoutByCurrency($currency)
  {
    return $this->{$currency . '_min_payout_sum'};
  }

  /**
   * Лимит на выплату
   * @param string $currency Валюта
   * @return number
   */
  public function getMaxPayoutByCurrency($currency)
  {
    return $this->{$currency . '_max_payout_sum'};
  }

  /**
   * Дневной лимит на выплату
   * @param string $currency Валюта
   * @return number
   */
  public function getPayoutLimitDailyByCurrency($currency)
  {
    return $this->{$currency . '_payout_limit_daily'};
  }

  /**
   * Месячный на выплату
   * @param string $currency Валюта
   * @return number
   */
  public function getPayoutLimitMonthlyByCurrency($currency)
  {
    return $this->{$currency . '_payout_limit_monthly'};
  }

  /**
   * Получить мап кошельков либо название одного кошелька по ID
   * @param null|int $id идентификатор кошелька
   * @param bool|null $activity @see find()
   * @return string[]|string
   */
  public static function getWallets($id = null, $activity = null)
  {
    $query = self::find($activity);
    if ($id !== null) {
      /** @var Wallet $paysystem */
      $paysystem = $query->andWhere(['id' => $id])->one();
      return $paysystem->name;
    }

    return ArrayHelper::map($query->all(), 'id', 'name');
  }

  /**
   * Получить список классов кошельков либо один класс по id
   * @param $type
   * @return mixed
   */
  public static function getWalletsClass($type = null)
  {
    //todo убрать хардкод getModule('payments')
    $wallets = ArrayHelper::getValue(Yii::$app->getModule('payments')->params, 'wallets', []);

    return $type === null ? $wallets : ArrayHelper::getValue($wallets, $type);
  }

  /**
   * Создание обьекта кошелька по id и валюте
   * @param $type
   * @param array $attributes
   * @return null|AbstractWallet
   */
  public static function getObject($type, $attributes = [], $userId = null)
  {
    if (!$className = self::getWalletsClass($type)) return null;
    if (!$attributes) $attributes = [];

    $presentAttributes = (new $className)->getAttributes();
    $attributes = array_intersect_key($attributes, $presentAttributes);

    $params = array_merge($attributes, ['class' => $className, 'type' => $type, 'userId' => $userId]);
    return Yii::createObject($params);
  }

  /**
   * Платежные системы по валюте
   * @param string|array $currency
   * @param bool|null $activity @see find()
   * @return array
   */
  public static function getByCurrency($currency, $activity = null)
  {
    $currency = is_array($currency) ? $currency : [$currency];
    $result = [];
    $paysystemsIds = Wallet::find($activity)->select('id')->column();
    /** @var AbstractWallet $walletClassName */
    foreach (self::getWalletsClass() as $walletId => $walletClassName) {
      $object = self::getObject($walletId);
      if (!$object) continue;
      if (!array_intersect($object->getActiveCurrencies(), $currency)) continue;
      if (!in_array($walletId, $paysystemsIds)) continue;

      $result[] = ['id' => $walletId, 'name' => $walletClassName::getName()];
    }

    return $result;
  }

  /**
   * @deprecated Метод устарел
   * Кошельки для автовыплат
   * @return array
   */
  public static function getAutoPayoutWallets()
  {
    return ArrayHelper::getValue(Yii::$app->controller->module->params, Module::PARAM_AUTO_PAYMENT_WALLETS);
  }

  /**
   * Массив валют для текущей системы оплаты
   * @param bool $active только активные
   * @return array
   */
  public function getCurrencies($active = true)
  {
    if (isset($this->_activeCurrencies[$active])) {
      return $this->_activeCurrencies[$active];
    }
    $object = self::getObject($this->id);
    if (!$object) {
      return [];
    }
    $this->_activeCurrencies[$active] =  $active
      ? $object->getActiveCurrencies()
      : $object::getCurrencies();

    return $this->_activeCurrencies[$active];
  }

  /**
   * Локальная ли валюта
   * @return array
   */
  public function isLocalityRu()
  {
    $class = self::getWalletsClass($this->id);
    return $class::isLocalityRu();
  }

  /**
   * @return AbstractWallet
   */
  public function getType()
  {
    return self::getWalletsClass($this->id);
  }

  /**
   * Запрещаю создание новой платежной системы
   * @inheritdoc
   */
  public function insert($runValidation = true, $attributes = null)
  {
    throw new Exception('Wallet insert forbiden');
  }

  /**
   * Запрещаю удаление новой платежной системы
   * @inheritdoc
   */
  public function delete()
  {
    throw new Exception('Wallet delete forbiden');
  }

  /**
   * Реквизиты в виде таблицы
   * @param AbstractWallet $wallet
   * @param array $options
   * @return string
   */
  public static function getAccountDetailView(AbstractWallet $wallet, $options = [])
  {
    Html::addCssClass($options, 'table table-bordered detail-view');
    Html::addCssStyle($options, 'min-height: 39px; background: none;');

    return DetailView::widget([
      'model' => $wallet,
      'attributes' => $wallet->getWalletDetailViewAttributes(),
      'options' => $options,
      'template' => '<tr><th {captionOptions}>{label}:</th><td {contentOptions}>{value}</td></tr>'
    ]);
  }

  /**
   * @param $currency
   * @return bool|PaySystemApi
   */
  public function getSender($currency)
  {
    $id = $this->{$currency . '_sender_api_id'};
    if (!$id) return false;

    $api = PaySystemApi::findOne($id);

    return $api ?: false;
  }

  public function isCard()
  {
    return $this->id == Wallet::WALLET_TYPE_CARD;
  }

  /**
   * Проксируем проверку пермишена
   * @see Module::isUserCanEditAllWalletFields()
   * @return bool
   */
  protected function isUserCanEditAllWalletFields()
  {
    /** @var Module $module */
    $module = Yii::$app->getModule('payments');
    return $module->isUserCanEditAllWalletFields();
  }


  /**
   * Дефолтный процент из конфига
   * @return float
   */
  // TODO Добавлено новое поле rgk_paysystem_percent, в котором хранится значение этого метода. Вероятнее всего во многих
  // местах нужно использовать именно новое поле, а не этот метод.
  // Вдруг проценты по умолчанию изменятся и при подсчете значений могут появится неверности
  public function getDefaultProfitPercent()
  {
    // TODO По-хорошему здесь должно быть или исключение, или 0 по умолчанию. Иначе если ПС будет отсутствовать в списке, мы не узнаем этого и будут всякие ворнинги в пыхе
    return ArrayHelper::getValue(Yii::$app->params['paysystem-percents'], $this->code);
  }

  /**
   * Дефолтный процент изменен
   * @return boolean
   */
  public function isDefaultProfitPercentChanged()
  {
    return floatval($this->profit_percent) !== $this->getDefaultProfitPercent();
  }

  /**
   * Возвращает разницу процентов профита
   * @return boolean
   */
  public function getProfitPercentDiff()
  {
    return floatval($this->profit_percent) - $this->getDefaultProfitPercent();
  }

  /**
   * @return ActiveQuery
   */
  public function getRubSenderApi()
  {
    return $this->getSenderApi('rub');
  }

  /**
   * @return ActiveQuery
   */
  public function getUsdSenderApi()
  {
    return $this->getSenderApi('usd');
  }

  /**
   * @return ActiveQuery
   */
  public function getEurSenderApi()
  {
    return $this->getSenderApi('eur');
  }

  /**
   * @param string $currency
   * @return ActiveQuery
   */
  public function getSenderApi($currency)
  {
    if (!in_array($currency, ['rub', 'usd', 'eur'])) throw new InvalidParamException;

    return $this->hasOne(PaySystemApi::class, ['id' => $currency . '_sender_api_id']);
  }

  /**
   * @param string $currency
   * @return int|null
   */
  public function getSenderApiId($currency)
  {
    if (!in_array($currency, ['rub', 'usd', 'eur'])) throw new InvalidParamException;

    return $this->{$currency . '_sender_api_id'};
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return !$this->is_active;
  }

  /**
   * Посчитать суммарный процент, который реселлер возьмет с партнера за создание выплаты
   * @param int $userId Пользователь для которого создается выплата
   * @return null|number
   */
  public function calcResellerPercent($userId)
  {
    $result = UserPayment::calcResellerPercentByValues(
      $this->profit_percent,
      UserPaymentSetting::fetch($userId)->getEarlyPercent()
    );

    return $result ? $result->percent : null;
  }
}