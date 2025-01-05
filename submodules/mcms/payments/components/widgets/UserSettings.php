<?php

namespace mcms\payments\components\widgets;

use mcms\payments\models\PartnerCompany;
use mcms\payments\models\search\UserWalletSearch;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class UserSettings extends Widget {

  const URL = '/payments/users/';

  /** @var bool Отображать как содержимое модального окна (только для options[getPartial]) */
  public $isModal = false;
  public $options;
  /**
   * @var bool|null Активность ПС
   * @see Wallet::find()
   */
  public $paysystemsActivity = true;

  private $searchModel;
  private $userId;
  /** @var bool показать дополнительные настройки выплат */
  private $showAddSettings;

  private $queryParams = [];

  private $pagination = false;

  private $_userPaymentSetting;

  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();
    ob_start();
  }

  /**
   * Получение модели UserPaymentSetting с актуальной валютой
   * Возвращается старая валюте в случае, если партнер сменил ее, но еще не вывел балланс со старого счета.
   *
   * @param integer $userId
   * @return UserPaymentSetting
   */
  private function getUserPaymentSetting($userId)
  {
    if ($this->_userPaymentSetting) return $this->_userPaymentSetting;

    $this->_userPaymentSetting = UserPaymentSetting::fetch($userId);
    $this->_userPaymentSetting->currency = $this->_userPaymentSetting->getCurrentCurrency();

    return $this->_userPaymentSetting;
  }

  /**
   * @inheritDoc
   */
  public function run()
  {
    if (!$this->userId = ArrayHelper::getValue($this->options, 'userId')) return null;
    /** @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');
    if (!$paymentsModule::canUserHaveBalance($this->userId)) return;

    $renderCreateButton = ArrayHelper::getValue($this->options, 'renderCreateButton', true);
    $this->showAddSettings = ArrayHelper::getValue($this->options, 'showAddSettings', true);
    $this->queryParams = Yii::$app->request->getQueryParams();
    $this->pagination = ArrayHelper::getValue($this->options, 'pagination', false);
    if (!$userModule = Yii::$app->getModule('users')) return null;
    if (!$user = ArrayHelper::getValue($userModule->api('user', [
      'skipCurrentUser' => true
    ])->search([['id' => $this->options['userId']]]), 0)) return null;
    if (!$promoModule = Yii::$app->getModule('promo')) return null;

    /** @var UserPaymentSetting $model */
    $model = $this->getUserPaymentSetting($this->userId);
    $model->scenario = UserPaymentSetting::getUpdateSettingsScenario();

    if ($currency = ArrayHelper::getValue($this->options, 'currency')) {
      $model->setAttribute('currency', $currency);
    }
    if (($walletId = ArrayHelper::getValue($this->options, 'walletId')) !== null) {
      $model->setAttribute('user_wallet_id', $walletId);
    }

    $canChangeWallet = $model->canChangeWallet($walletId);
    $canChangeCurrency = $model->canChangeCurrency($currency);
    $partnerCompany = $this->_userPaymentSetting->partner_company_id
      ? PartnerCompany::findOne(['id' => $this->_userPaymentSetting->partner_company_id])
      : null;
    $canViewPartnerCompany = PartnerCompany::isCanView();
    $canUpdatePartnerCompany = PartnerCompany::isCanManage();

    if (ArrayHelper::getValue($this->options, 'getPartial') === true) {
      return $this->render('_user_settings_form', [
        'model' => $model,
        'modal' => $this->isModal,
        'canChangeWallet' => $canChangeWallet,
      ]);
    }

    $dataProvider = $this->getDataProvider();
    $searchModel = $this->searchModel;

    // Если можно показывать кнопку добавления кошелька, проверяем, есть ли варианты
    $renderCreateButton = $renderCreateButton
      ? count($searchModel->getWalletDropDown('rub'))
        || count($searchModel->getWalletDropDown('usd'))
        || count($searchModel->getWalletDropDown('eur'))
      : false;

    return $this->render('user_settings', [
      'renderCreateButton' => $renderCreateButton,
      'model' => $model,
      'currencyList' => $promoModule->api('mainCurrencies', ['availablesOnly' => true])->setMapParams(['code', 'symbol'])->getMap(),
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'partnerCompany' => $partnerCompany,
      'canChangeWallet' => $canChangeWallet,
      'canChangeCurrency' => $canChangeCurrency,
      'canUpdatePartnerCompany' => $canUpdatePartnerCompany,
      'canViewPartnerCompany' => $canViewPartnerCompany,
      'canChangeCurrencyError' => $canChangeCurrency ? null : $model->canChangeCurrencyLastError(),
      'canViewAdditionalParameters' => Yii::$app->user->can('PaymentsCanViewAdditionalParameters'),
      'canCreatePaymentWithoutEarlyCommission' => $paymentsModule::canCreatePaymentWithoutEarlyCommission($model->user_id),
      'canViewResellerSettings' => Yii::$app->user->can('PaymentsResellerSettings'),
      'hasPaymentInAwaiting' => !!UserPayment::getAwaitingCount($model->user_id),
      'showAddSettings' => $this->showAddSettings,
      'canUserHaveWallets' => $paymentsModule::canUserHaveWallets($this->userId),
      'isAlternativePaymentsGridView' => Yii::$app->getModule('payments')->isAlternativePaymentsGridView(),
      'isPartner' => Yii::$app->getModule('users')->api('rolesByUserId', ['userId' => $model->user_id])->isPartner(),
    ]);
  }

  /**
   * @return \yii\data\ActiveDataProvider
   */
  protected function getDataProvider()
  {
    $this->searchModel = new UserWalletSearch([
      'user_id' => $this->userId,
      'paysystemsActivity' => $this->paysystemsActivity,
    ]);

    $dataProvider = $this->searchModel->search($this->queryParams);

    return $dataProvider;
  }

}
