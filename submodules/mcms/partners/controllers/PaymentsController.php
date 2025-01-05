<?php

namespace mcms\partners\controllers;

use mcms\common\controller\SiteBaseController as BaseController;
use mcms\common\helpers\ArrayHelper;
use mcms\common\SystemLanguage;
use mcms\common\web\AjaxResponse;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\partners\components\api\UserWalletsManageAccess;
use mcms\partners\components\WalletsDisplayHandler;
use mcms\partners\components\widgets\FileApiWidget;
use mcms\partners\models\EarlyPaymentRequestForm;
use mcms\payments\components\api\UserBalanceConvertHandler;
use mcms\payments\components\api\UserPayments;
use mcms\payments\components\api\UserSettingsData;
use mcms\payments\components\AvailableCurrencies;
use mcms\payments\components\events\PaymentStatusUpdated;
use mcms\payments\components\events\RegularPaymentCreated;
use mcms\payments\components\events\UserBalanceInvoiceMulct;
use mcms\payments\components\UserBalance;
use mcms\payments\components\UserPaymentsSum;
use mcms\payments\models\PartnerCompany;
use mcms\payments\models\PartnerPaymentSettings;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserWallet;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\Module;
use rgk\utils\actions\DownloadFileAction;
use rgk\utils\components\CurrenciesValues;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * Class PaymentsController
 * @package mcms\partners\controllers
 */
class PaymentsController extends BaseController
{

  /**
   * @var null|Module
   */
  private $paymentsModule;
  /** @var UserBalanceConvertHandler */
  private $userBalanceConvertHandler;

  public $controllerTitle;
  public $theme = 'basic';

  // максимальное кол-во файлов для одного поля разрешенное для загрузки пользователем
  // если пользователь загрузит более 50 файлов для одного поля, получит сообщение об ошибке + будет записан error
  const MAX_UPLOAD_COUNT = 50;

  /**
   * @inheritDoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'requestEarlyPayment' => ['post'],
          'delete-wallet' => ['post'],
        ]
      ]
    ];
  }

  /**
   * @inheritdoc
   */
  public function actions()
  {
    // TODO сделать авторизацию для скачивания
    return parent::actions() + [
        'download-cheque' => [
          'class' => DownloadFileAction::class,
          'modelClass' => UserPayment::class,
          'attribute' => 'cheque_file',
        ],
        'download-invoice' => [
          'class' => DownloadFileAction::class,
          'modelClass' => UserPayment::class,
          'attribute' => 'invoice_file',
        ],
        'download-positive-invoice' => [
          'class' => DownloadFileAction::class,
          'modelClass' => UserPayment::class,
          'attribute' => 'generated_invoice_file_positive',
        ],
        'download-negative-invoice' => [
          'class' => DownloadFileAction::class,
          'modelClass' => UserPayment::class,
          'attribute' => 'generated_invoice_file_negative',
        ],
      ];
  }

  /**
   * @param \yii\base\Action $action
   * @return bool
   */
  public function beforeAction($action)
  {
    $this->menu = [
      [
        'label' => Yii::_t('payments.menu-payments'),
        'active' => $this->action->id === 'balance',
        'url' => '/partners/payments/balance/',
      ],
      [
        'label' => Yii::_t('payments.menu-settings'),
        'active' => $this->action->id === 'settings',
        'url' => '/partners/payments/settings/',
      ],
      [
        'label' => Yii::_t('payments.menu-partner-company'),
        'active' => $this->action->id === 'company',
        'url' => '/partners/payments/company/',
        'visible' => $this->getUserPaymentSettings()->partner_company_id !== null,
      ],
    ];
    $this->controllerTitle = Yii::_t('main.finance');

    return parent::beforeAction($action);
  }


  /**
   * PaymentsController constructor.
   * @param string $id
   * @param \yii\base\Module $module
   * @param array $config
   */
  public function __construct($id, $module, $config = [])
  {
    parent::__construct($id, $module, $config);
    $this->paymentsModule = Yii::$app->getModule('payments');
    $this->userBalanceConvertHandler = $this->paymentsModule->api('userBalanceConvertHandler', [
      'userId' => Yii::$app->user->id,
    ]);
  }

  /**
   * @return mixed
   */
  private function getUserBalanceApi()
  {
    return $this->userBalanceConvertHandler->getCurrentUserBalance();
  }

  /**
   * @return string
   */
  public function actionBalance()
  {
    // TODO Удалить костыль
    $userSettingsData = (new UserSettingsData(['userId' => Yii::$app->user->id]))->getResult();
    $fixBalance = new UserBalance(['userId' => Yii::$app->user->id, 'currency' => $userSettingsData->getCurrentCurrency()]);

    // TODO Порефакторить с Максом и Игорем
    /*
     * - Нужно определиться что такое newCurrency, oldCurrency и currentCurrency
     * - Описать эти опеделения где-нибудь в PHPDoc, например в классе UserPaymentSetting
     * Предлагаю описать это так:
     * newCurrency - валюта прописанная в настройках пользователя
     * oldCurrency - валюта не равная newCurrency, на балансе которой остались средства
     * currentCurrency - основная валюта используемая для заказа выплат (return $oldCurrency ? : $newCurrency)
     * - Провести рефакторинг этого экшена
     * - Провести рефакторинг компонента userBalanceConvertHandler
     * - Провести рефакторинг вьюшки balance.php
     *
     * Крайне желательно сосредоточить логику по определению валюты в одном месте, иначе трудно понять как это работает, трудно доработать.
     * Например недавно случайно сделали дублирование логики, что создало проблемы.
     *
     * Например переименовать метод $userSettingsData->getCurrentCurrency() в getCurrencyToPayments()
     * и создать второй метод getCurrencyToProfit().
     * Первый метод определяет валюту используемую для заказа выплат, второй метод определяет валюту для получения прибыли.
     * Реализовать эти методы в одном месте, например в UserPaymentSetting или UserBalance, или создать отдельный компонент UserCurrency.
     * Что бы было прозрачно видно когда какая валюта будет использоваться.
     * Это очень упростит поддержку этого функционала и сэкономит гору времени
     */

    $currentBalance = $this->userBalanceConvertHandler->getCurrentUserBalance();
    $oldBalance = $this->userBalanceConvertHandler->getOldUserBalance();

    $hasOldBalance = $this->userBalanceConvertHandler->getHasOldBalance();
    $hasOldPayments = $this->userBalanceConvertHandler->getHasOldPayments();
    /** @var double $newBalanceSum для отображения новой суммы в кнопке конвертирования */
    $convertedSum = $hasOldBalance || $hasOldPayments ? $this->userBalanceConvertHandler->getUserNewBalance() : 0;

    // Список выплат
    /** @var ActiveDataProvider $payments */
    $payments = $this->paymentsModule->api('userPayments', ['userId' => Yii::$app->user->id])->getResult();
    $payments->query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);

    $courses = PartnerCurrenciesProvider::getInstance()->getCoursesAsArray();

    $currency = $hasOldBalance ? $oldBalance->getCurrency() : $currentBalance->getCurrency();
    $paymentForm = new EarlyPaymentRequestForm;
    $paymentSystems = $this->getPaymentSystems($currency);
    $hasWalletDetailsAccess = $this->getWalletsManageAccess()->hasAccess();
    $userWallets = $this->paymentsModule->api('userWallet')->getUserWallets();
    $walletsHandler = new WalletsDisplayHandler([
      'userCurrency' => $currency,
      'userWallets' => $userWallets,
    ]);
    $showLocal = $walletsHandler->showLocal;
    $showLocalFirst = $walletsHandler->showLocalFirst;

    // Выплаты партнеру запрещены
    $paymentsIsDisabled = !$this->getUserPaymentSettings()->isPaymentsEnabled();

    list($totalPayed, $totalCharged) = $this->getPayedSums();
    
    $partnerPaymentSettings = $this->getPartnerPaymentSettingsModel();
  
    return $this->render('balance', compact(
      'fixBalance',
      'payments',
      'courses',
      'currentBalance',
      'oldBalance',
      'paymentForm',
      'paymentSystems',
      'hasOldBalance',
      'hasOldPayments',
      'convertedSum',
      'showLocal',
      'showLocalFirst',
      'hasWalletDetailsAccess',
      'paymentsIsDisabled',
      'totalPayed',
      'totalCharged',
      'partnerPaymentSettings',
      'userWallets'
    ));
  }
  
  public function actionAutoPaymentForm()
  {
    if (!Yii::$app->request->isAjax)
      return $this->redirect(['balance']);
    
    $model = $this->getPartnerPaymentSettingsModel();
    
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("ajax") && $model->save()) {
        // return $this->redirect(['balance']);
        return $this->asJson(['success' => true]);
      }
      
      return $this->asJson(['validation' => ActiveForm::validate($model)]);
    }
  }
  
  /**
   * @return array|Response
   */
  public function actionRequestEarlyPayment()
  {
    if (!Yii::$app->request->isAjax) return $this->redirect(['balance']);
    Yii::$app->response->format = Response::FORMAT_JSON;

    $form = new EarlyPaymentRequestForm;
    $form->load(Yii::$app->request->post());
    $form->balance = $this->getUserBalanceApi(Yii::$app->user->id)->getMain();

    if (!$form->validate()) return AjaxResponse::error();
    $paymentSetting = Yii::$app->getModule('payments')->api('userSettingsData', [
      'userId' => Yii::$app->user->id
    ])->getResult();

    if (!$this->paymentsModule->api('requestEarlyPayment', [
      'userId' => Yii::$app->user->id,
      'currency' => $paymentSetting->getCurrentCurrency(),
      'paymentRequests' => $form->getPaymentRequests()
    ])->getResult()
    ) return AjaxResponse::error();

    $this->flashSuccess('partners.payments.payment_requests_success');
    return $this->redirect(['balance']);
  }

  /**
   * @return array|Response
   */
  public function actionEnableAutoPayments()
  {
    return $this->asJson([]);
    //return $this->handleAutoPayments();
  }

  /**
   * @return array|Response
   */
  public function actionDisableAutoPayments()
  {
    $model = $this->getPartnerPaymentSettingsModel();
    if(!$model->isNewRecord){
      $model->delete();
    }
    return $this->asJson([]);
    //return $this->handleAutoPayments(false);
  }
  
  /**
   * @return PartnerPaymentSettings
   */
  private function getPartnerPaymentSettingsModel()
  {
    if (($partnerPaymentSettings = PartnerPaymentSettings::findOne(['user_id' => Yii::$app->user->getId()])) !== null) {
      return $partnerPaymentSettings;
    }
    return new PartnerPaymentSettings();
  }

  /**
   * @param bool $enable
   * @return array|Response
   */
  private function handleAutoPayments($enable = true)
  {
    if (!Yii::$app->request->isAjax) return $this->redirect(['balance']);

    Yii::$app->response->format = Response::FORMAT_JSON;
    return AjaxResponse::set($this->paymentsModule->api('userSettingsData', [
      'userId' => Yii::$app->user->id
    ])->setAutoPayments($enable));
  }

  /**
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionCompany()
  {
    $patnerCompanyId = $this->getUserPaymentSettings()->partner_company_id;

    if (!$patnerCompanyId) {
      throw new NotFoundHttpException('Page not found');
    }

    $company = PartnerCompany::findOne(['id' => $patnerCompanyId]);

    return $this->render('company', [
      'company' => $company
    ]);
  }

  /**
   * Получение соглашения
   * @param $id
   * @return \yii\console\Response|Response
   * @throws NotFoundHttpException
   * @throws ForbiddenHttpException
   */
  public function actionGetAgreement($id)
  {
    Yii::$app->response->format = Response::FORMAT_HTML;
    if ($this->getUserPaymentSettings()->partner_company_id !== (int)$id) {
      throw new ForbiddenHttpException();
    }
    $model = PartnerCompany::findOne(['id' => $id]);
    if (!$model) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
    return $model->getAgreementFile();
  }


  /**
   * @return string
   */
  public function actionSettings()
  {
    $currencies = Yii::$app->getModule('promo')
      ->api('mainCurrencies', ['availablesOnly' => true])
      ->setMapParams(['code', 'name'])->getMap();

    $availableCurrencies = (new AvailableCurrencies(Yii::$app->user->id))->getCurrencies();
Yii::debug($availableCurrencies);
    $walletsHandler = new WalletsDisplayHandler([
      'systemWallets' => $this->paymentsModule->api('walletTypes', ['activity' => true])->getResult(),
      'userWallets' => $this->paymentsModule->api('userWallet')->getUserWallets(),
      'userCurrency' => $this->getUserPaymentSettings()->currency,
    ]);

    $userPaymentSettings = $this->getUserPaymentSettings();
    /** @var \mcms\payments\components\api\UserBalance $userBalanceApi */
    $userBalanceApi = $this->paymentsModule->api('userBalance', [
      'userId' => Yii::$app->user->id
    ]);

    /** @var UserPayments $userPaymentsApi */
    $userPaymentsApi = $this->paymentsModule->api('userPayments', ['userId' => Yii::$app->user->id]);

    $balance = $userBalanceApi->getBalance();

    $haveMoney = $balance > 0 || $userPaymentsApi->hasAwaitingPayments($userPaymentSettings->currency);

    $isCurrencyChanged = $this->userBalanceConvertHandler->getHasOldBalance() ||
      $this->userBalanceConvertHandler->getHasOldBalance();

    $currency = $userPaymentSettings->currency;

    $partnerCurrenciesProvider = PartnerCurrenciesProvider::getInstance()->getCurrencies()->getCurrency($currency);
    $formatter = Yii::$app->formatter;

    return $this->render('settings', [
      'walletsHandler' => $walletsHandler,
      'currencies' => $currencies,
      'availableCurrencies' => $availableCurrencies,
      'userPaymentSettings' => $userPaymentSettings,
      'haveMoney' => $haveMoney,
      'isCurrencyChanged' => $isCurrencyChanged,
      'convertedBalance' => [
        'rub' => $formatter->asPrice($partnerCurrenciesProvider->convertToRub($balance), 'rub'),
        'usd' => $formatter->asPrice($partnerCurrenciesProvider->convertToUsd($balance), 'usd'),
        'eur' => $formatter->asPrice($partnerCurrenciesProvider->convertToEur($balance), 'eur')
      ],
      'balance' => $balance,
      'currency' => $currency,
      'hasWalletDetailsAccess' => $this->getWalletsManageAccess()->hasAccess(),
      'isLocked' => $userPaymentSettings->getIsWalletsManageDisabled(),
    ]);
  }

  public function actionSettingsWalletTypes($currency)
  {
    $walletsHandler = new WalletsDisplayHandler([
      'systemWallets' => $this->paymentsModule->api('walletTypes', ['activity' => true])->getResult(),
      'userWallets' => $this->paymentsModule->api('userWallet')->getUserWallets(),
      'userCurrency' => $currency,
    ]);

    return AjaxResponse::success([
      'showLocal' => $walletsHandler->showLocal,
      'showLocalFirst' => $walletsHandler->showLocalFirst,
    ]);
  }

  /**
   * Платежные системы для отображения в дропдауне формы заказа выплат
   * @param $currency
   * @return array
   * @internal param \array[] $walletTypes
   */
  private function getPaymentSystems($currency)
  {
    $paymentSystems = [];
    $availableCurrencies = (new AvailableCurrencies(Yii::$app->user->id))->getCurrencies();

    $userWallets = $this->paymentsModule->api('userWallet')->getUserWallets($availableCurrencies);
    $walletTypes = $this->paymentsModule->api('walletTypes', ['activity' => true])->getWallets();
    $userWalletsByType = [];
    $typeCurrencies = [];
    $userWalletCurrencies = [];
    /** @var UserWallet $userWallet */
    foreach ($userWallets as $userWallet) {
      $userWalletCurrencies[$userWallet->getAccountObject()->getUniqueValue()][] = $userWallet->currency;
    }
    foreach ($userWallets as $userWallet) {

      if (!isset($userWalletsByType)) $userWalletsByType[$userWallet->wallet_type] = [];
      $dayLimit = $monthLimit = $minPayout = $maxPayout = false;
      foreach ($walletTypes as $walletType) {
        /** @var Wallet $walletType */
        if ($walletType->id != $userWallet->wallet_type) continue;
        $dayLimit = $walletType->getPayoutLimitDailyByCurrency($userWallet->currency);
        $monthLimit = $walletType->getPayoutLimitMonthlyByCurrency($userWallet->currency);
        $minPayout = $walletType->getMinPayoutByCurrency($userWallet->currency);
        $maxPayout = $walletType->getMaxPayoutByCurrency($userWallet->currency);
      }

      //Для карт ставим флаг группировки лимитов для usd,eur кошельков
      $isRelatedLimits = false;
      $walletCurrencies = $userWalletCurrencies[$userWallet->getAccountObject()->getUniqueValue()];
      if ($userWallet->walletType->isCard()
        && count(array_intersect(['usd', 'eur'], $walletCurrencies)) == 2
        && count($walletCurrencies) == 2
      ) {
        $isRelatedLimits = true;
      }

      // TRICKY ЕСЛИ ДОБАВЛЯЕШЬ НОВЫЕ ПАРАМЕТРЫ СЮДА, ТО ОБЯЗАТЕЛЬНО ДОБАВЬ ИХ В PaymentsController::actionWalletForm()
      $userWalletsByType[$userWallet->wallet_type][] = [
        'id' => $userWallet->id,
        'address' => $userWallet->getAccountObject()->getUniqueValueProtected(),
        'uniqueValue' => md5(Json::encode($userWallet->getAccountObject()->toArray())),
        'usedDay' => number_format($userWallet->getDailyLimitUse(), 2, '.', ''),
        'usedMonth' => number_format($userWallet->getMonthlyLimitUse(), 2, '.', ''),
        'currency' => $userWallet->currency,
        'dayLimit' => $dayLimit ?: false,
        'monthLimit' => $monthLimit ?: false,
        'min' => $minPayout ?: 0,
        'max' => $maxPayout ?: false,
        'isRelatedLimits' => $isRelatedLimits,
        'icon' => $userWallet->getAccountObject()->getIconSrc(),
      ];
      $typeCurrencies[$userWallet->wallet_type][] = $userWallet->currency;
    }
    foreach ($typeCurrencies as $key => $type) {
      $typeCurrencies[$key] = array_unique($type);
    }
    $partnerCurrenciesProvider = PartnerCurrenciesProvider::getInstance();
    /** @var Wallet $walletType */
    foreach ($walletTypes as $walletType) {
      $userWallets = ArrayHelper::getValue($userWalletsByType, $walletType->id, []);
      $walletTypeClass = $walletType->getType();

      /**
       * Если у партнера только один кошелёк для данного типа (но, возможно, в разных валютах),
       * то показывать иконку для этого типа кошелька, иначе дефолтную
       */
      $uniqueWallets = array_unique(ArrayHelper::getColumn($userWallets, 'uniqueValue'));
      $icon = count($uniqueWallets) == 1
        ? $userWallets[0]['icon']
        : $walletTypeClass::getDefaultIconSrc();
      $typeActiveCurrencies = ArrayHelper::getValue($typeCurrencies, $walletType->id, []);
      $typeActiveCurrencies = array_values($typeActiveCurrencies);
      $minPayoutByCurrency = $maxPayoutByCurrency = $payoutLimitDailyByCurrency = $payoutLimitMonthlyByCurrency = [];
      $walletCurrencies = $payoutLimitDailyDefault = $payoutLimitMonthlyDefault = [];
      foreach ($userWallets as $userWallet) {
        $userWalletId = $userWallet['id'];
        $userWalletCurrency = $userWallet['currency'];
        $walletCurrencies[$userWalletCurrency] = $userWalletCurrency;
        $course = $partnerCurrenciesProvider
          ->getCurrencies()
          ->getCurrency($currency)
          ->{'getTo' . lcfirst($userWalletCurrency)}();
        $minPayoutByCurrency[$userWalletId] = (float)$walletType->getMinPayoutByCurrency($userWalletCurrency) / $course;
        $maxPayoutByCurrency[$userWalletId] = (float)$walletType->getMaxPayoutByCurrency($userWalletCurrency) / $course;
        $payoutLimitDailyByCurrency[$userWalletId] = (float)$walletType->getPayoutLimitDailyByCurrency($userWalletCurrency) * $course;
        $payoutLimitMonthlyByCurrency[$userWalletId] = (float)$walletType->getPayoutLimitMonthlyByCurrency($userWalletCurrency) * $course;
        $payoutLimitDailyDefault[$userWalletId] = (float)$walletType->getPayoutLimitDailyByCurrency($userWalletCurrency);
        $payoutLimitMonthlyDefault[$userWalletId] = (float)$walletType->getPayoutLimitMonthlyByCurrency($userWalletCurrency);
      }

      // TRICKY Не округлять min/max, иначе при вводе минималки в форму сумма после конвертации может стать меньше минималки
      $minPayout = $minPayoutByCurrency ? min($minPayoutByCurrency) : 0;
      $maxPayout = $maxPayoutByCurrency ? max($maxPayoutByCurrency) : false;

      $payoutLimitDaily = $payoutLimitDailyByCurrency
        ? $payoutLimitDailyDefault[current(array_keys($payoutLimitDailyByCurrency, max($payoutLimitDailyByCurrency)))]
        : false;
      $payoutLimitMonthly = $payoutLimitMonthlyByCurrency
        ? $payoutLimitMonthlyDefault[current(array_keys($payoutLimitMonthlyByCurrency, max($payoutLimitMonthlyByCurrency)))]
        : false;

      $paymentSystems[] = [
        'id' => $walletType->id,
        'name' => (string)$walletType->name,
        'icon' => $icon,
        'bonus' => $walletType->calcResellerPercent(Yii::$app->user->id),
        'min' => $minPayout ?: 0,
        'max' => $maxPayout ?: false,
        'dayLimit' => $payoutLimitDaily ?: false,
        'monthLimit' => $payoutLimitMonthly ?: false,
        'active' => (bool)$userWallets,
        'wallets' => $userWallets,
        'activeCurrency' => $typeActiveCurrencies,
        'currency' => $availableCurrencies,
        'local' => $walletType->isLocalityRu(),
      ];
    }

    return $paymentSystems;
  }

  /**
   *
   * TODO: возможно какие-то данные в этом методе уже не нужны, надо сравнить с тем что принимает и использует вьюха.
   *
   * @param $type
   * @param $walletId
   * @param $currency
   * @param $new
   * @return string|array
   * @throws ForbiddenHttpException
   */
  public function actionWalletForm($type = null, $walletId = null, $currency = null, $new = null)
  {
    if ($walletId && !$this->getWalletsManageAccess()->hasAccess()) throw new ForbiddenHttpException;

    $userPaymentSettings = $this->getUserPaymentSettings();
    $isWalletsManageDisabled = $userPaymentSettings->getIsWalletsManageDisabled();

    $availableCurrencies = (new AvailableCurrencies(Yii::$app->user->id))->getCurrencies();

    /** @var UserWallet $userWallet */
    $userWallets = $this->getWalletModels($type, $availableCurrencies, $walletId, $new);
    $userWallet = is_array($userWallets) ? reset($userWallets) : $userWallets;
    $walletAccount = $userWallet->getAccountObject();

    $savedWallets = [];

    $walletsHandler = new WalletsDisplayHandler([
      'userWallets' => $this->paymentsModule->api('userWallet')->getUserWallets(),
      'userCurrency' => $userPaymentSettings->currency,
    ]);

    if (Yii::$app->request->isPost && $walletAccount->load(Yii::$app->request->post())) {
      // нельзя редактировать кошель
      if ($isWalletsManageDisabled) return AjaxResponse::error();

      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($walletAccount);
      }

      if (!$walletAccount->validate()) {
        return AjaxResponse::error();
      }

      $newWalletAccount = (string)$walletAccount;
      /** @var  $userWalletApi \mcms\payments\components\api\UserWallet */
      $userWalletApi = $this->paymentsModule
        ->api('userWallet', [
          'wallet_old_account' => $userWallet->wallet_account,
          'wallet_new_account' => $newWalletAccount,
          'wallet_type' => $userWallet->wallet_type,
          'user_id' => Yii::$app->user->id,
        ]);

      $currencies = is_array(Yii::$app->request->post('currency'))
        ? Yii::$app->request->post('currency')
        : [Yii::$app->request->post('currency')];

      $groupCurrencies = $userWalletApi->findWalletsGroup()->select('currency')->column();
      // TRICKY Если карта становится мультивалютной или наоборот, то кошельки карты пересоздаются во всех валютах для сброса лимитов usd и eur
      $isCardMultiCurrency = $userWallet->walletType->isCard()
        && (!array_diff(['usd', 'eur'], $currencies) || !array_diff(['usd', 'eur'], $groupCurrencies));

      // Обработка только измененных кошельков (основная задача, что бы не сбрасывались лимиты привязанные к кошелькам)
      if ($userWallet->wallet_account == $newWalletAccount && !$isCardMultiCurrency) {
        // Реквизиты кошелька прежние
        $currenciesToDelete = array_diff($groupCurrencies, $currencies); // Удалить существующие кошельки, валюты которых не указаны в форме
        $currenciesToSave = array_diff($currencies, $groupCurrencies); // Создать кошельки в валютах, которые отсутствуют в БД
      } else {
        // Реквизиты кошелька изменены
        $currenciesToDelete = null; // Удалить все кошельки
        $currenciesToSave = $currencies; // Создать кошельки на все указанные валюты
      }

      $userWalletApi->deleteGroupWallets($currenciesToDelete);

      $transaction = Yii::$app->db->beginTransaction();
      try {
        foreach ($currenciesToSave as $currency) {
          $result = $userWalletApi->createWallet($currency);
          if (!$result['result']) {
            $transaction->rollBack();
            return AjaxResponse::error($result['model']->getFirstError('wallet_account'));
          }
          // возвращаем актуальную модель (с заполненным id и прочим)
          // TRICKY Не ЗАБЫВАЕМ СЮДА ДОБАВИТЬ НОВЫЕ ПАРАМЕТРЫ КОШЕЛЬКОВ ЕСЛИ ОНИ ПОЯВЛЯЮТСЯ В PaymentsController::actionBalance()
          /** @var UserWallet $newModel */
          $newModel = $result['model'];
          $savedWallets[] = [
            'walletId' => $newModel->id,
            'walletType' => $newModel->wallet_type,
            'walletUniqueValue' => $newModel->getAccountObject()->getUniqueValueProtected(),
            'currency' => $currency,
            'dayLimit' => $newModel->walletType->getPayoutLimitDailyByCurrency($currency) ?: false,
            'monthLimit' => $newModel->walletType->getPayoutLimitMonthlyByCurrency($currency) ?: false,
            'min' => $newModel->walletType->getMinPayoutByCurrency($currency) ?: 0,
            'max' => $newModel->walletType->getMaxPayoutByCurrency($currency) ?: false,
            'isRelatedLimits' => $isCardMultiCurrency
          ];
        }

        $transaction->commit();
      } catch (\Exception $e) {
        $transaction->rollBack();
        return AjaxResponse::error();
      }

      return AjaxResponse::success(['wallets' => $savedWallets, 'walletType' => $type]);
    }

    return $this->renderAjax('wallet-form', [
      'availableCurrencies' => $availableCurrencies,
      'model' => $walletAccount,
      'userWallet' => $userWallet,
      'walletType' => $type,
      'wallets' => $this->getWalletModels($type),
      'walletsHandler' => $walletsHandler,
      'isLocked' => $isWalletsManageDisabled,
    ]);
  }

  /**
   * Отображение списка кошельков партнера
   * TODO: возможно какие-то данные в этом методе не нужны, надо сравнить с тем что принимает и использует вьюха.
   * @param int $type
   * @return string
   */
  public function actionWalletsList($type)
  {
    $userPaymentSettings = $this->getUserPaymentSettings();
    $isWalletsManageDisabled = $userPaymentSettings->getIsWalletsManageDisabled();

    /** @var UserWallet $userWallet */
    $userWallets = $this->getWalletModels($type);
    $userWallet = is_array($userWallets) ? reset($userWallets) : $userWallets;
    $walletAccount = $userWallet->getAccountObject();

    return $this->renderAjax('wallet-list', [
      'availableCurrencies' => explode(',', $userWallet->currency),
      'model' => $walletAccount,
      'userWallet' => $userWallet,
      'walletType' => $type,
      'wallets' => $userWallets,
      'isLocked' => $isWalletsManageDisabled,
    ]);
  }

  /**
   * Смена валюты. Можно передать convert=1 чтобы после этого ещё и сразу конвертнуть баланс  новую валюту.
   * @return array|Response
   */
  public function actionChangeCurrency()
  {
    $userPaymentSettings = $this->getUserPaymentSettings();
    $currency = Yii::$app->request->post('newCurrency');
    $convert = (bool)Yii::$app->request->post('convert');

    if (!$currency) return AjaxResponse::error();

    if (!$userPaymentSettings->canChangeCurrency($currency)) return AjaxResponse::error();

    $result = $this->paymentsModule->api('setUserCurrency', [
      'userId' => Yii::$app->user->id,
      'currency' => $currency,
    ])->getResult();

    if (!$result) return AjaxResponse::error();

    // Надо сразу сконвертировать старый баланс
    if ($convert && !$this->convertBalance()) {
      return AjaxResponse::error();
    }

    return $this->redirect(['settings']);
  }

  /**
   * @param $walletId
   * @return string
   */
  public function actionDeleteWallet($walletId = null)
  {
    $userPaymentSettings = $this->getUserPaymentSettings();

    $isWalletsManageDisabled = $userPaymentSettings->getIsWalletsManageDisabled();

    if ($isWalletsManageDisabled) return false;

    // получаю модель кошелька для удаления
    $userWallet = $this->paymentsModule->api('userWallet', ['walletId' => $walletId, 'user_id' => Yii::$app->user->id])->getResult();

    // Удаляю все кошельки пользователя с этими реквизитами
    /** @var  $userWalletApi \mcms\payments\components\api\UserWallet */
    $userWalletApi = $this->paymentsModule
      ->api('userWallet', [
        'wallet_old_account' => $userWallet->wallet_account,
        'wallet_type' => $userWallet->wallet_type,
        'user_id' => Yii::$app->user->id,
      ]);
    $userWalletApi->deleteGroupWallets();

    $userWallets = $this->paymentsModule->api('userWallet')->getUserWallets(false);
    return json_encode(ArrayHelper::map(is_array($userWallets) ? $userWallets : [], 'currency', 'wallet_account', 'wallet_type'));
  }

  /**
   * @return mixed
   */
  public function getUserPaymentSettings()
  {
    return $this->paymentsModule
      ->api('userSettingsData', ['userId' => Yii::$app->user->id])
      ->getResult();
  }

  /**
   * @param $walletId
   * @param $type
   * @param $currency
   * @param $new
   * @return UserWallet|null
   */
  public function getWalletModels($type, $currency = null, $walletId = null, $new = false)
  {
    return $this->paymentsModule->api('userWallet', [
      'walletId' => $walletId,
      'wallet_type' => $type,
      'user_id' => Yii::$app->user->id,
      'currency' => $currency,
      'new' => $new,
    ])->getGroupResult();
  }

  // TODO Есть куча экшенов аналогичных этому. Нужно вынести их в один класс-экшен, что бы избежать дублирования
  // TODO защита от типов файлов сделана на скорую руку.
  // Будет круто если появится универсальное решение.
  // Ну ли как минимум просто отрефакторить бы
  public function actionUploadWalletFiles()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    $attribute = Yii::$app->request->post('attribute');
    $formName = Yii::$app->request->post('formName');

    $filename = UserWallet::getFilename($formName, $attribute);

    if (!$filename) {
      Yii::error('Партнер с user_id = ' . Yii::$app->user->id . ' пытается загрузить файл с неверными значениями attribute (' . $attribute . ') или formName (' . $formName . ')', __METHOD__);
      return ['error' => Yii::_t('partners.payments.cant_upload_file')];
    }

    $path = Yii::getAlias('@uploadPath' . $filename . Yii::$app->user->id . '/');
    $url = $filename . Yii::$app->user->id . '/';

    FileHelper::createDirectory($path);
    $file = UploadedFile::getInstanceByName($attribute);

    // защита по MimeType
    if (!$this->validateMimeType($file, ['image/*', 'application/pdf'])) {
      return ['error' => 'Not allowed file type'];
    }

    $model = new DynamicModel(compact('file'));
    // допустимые расширения
    $extensions = FileApiWidget::IMAGE_EXTENSIONS;
    $extensions[] = 'pdf';
    $model->addRule('file', 'image', ['extensions' => implode(', ', $extensions)])->validate();

    if ($model->hasErrors()) {
      return ['error' => $model->getFirstError('file')];
    }

    $files = FileHelper::findFiles($path);
    if (count($files) >= self::MAX_UPLOAD_COUNT) {
      Yii::error('Партнер с user_id = ' . Yii::$app->user->id . ' пытается загрузить более ' . self::MAX_UPLOAD_COUNT . ' файлов ' . $attribute, __METHOD__);
      return ['error' => Yii::_t('partners.payments.file_upload_limit_error')];
    }

    $model->file->name = md5(Yii::$app->user->id) . time() . '.' . $model->file->extension;
    if ($model->file->saveAs($path . $model->file->name)) {
      return ['url' => $url . $model->file->name];
    }
    return ['error' => Yii::_t('partners.payments.cant_upload_file')];
  }

  public function actionDeleteWalletFiles($walletId)
  {
    // TODO Удалить этот экшен когда найдем решение как заставить плагин не отправлять запрос на сервер
    // @see \mcms\payments\models\wallet\WalletForm::imageInput
    return AjaxResponse::set(true);
  }

  /**
   * @param $fromCurrency
   * @param $toCurrency
   * @return int
   */
  protected function getCourse($fromCurrency, $toCurrency)
  {
    if ($fromCurrency === $toCurrency) {
      return 1;
    }
    return PartnerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency($fromCurrency)
      ->{'getTo' . lcfirst($toCurrency)}();
  }

  /**
   * показывать ли локальные кошельки
   * @return array
   */
  private function getLocalWalletSettings()
  {
    $localCurrency = 'rub';
    $localLang = 'ru';

    $hasLocalWallets = false;
    $userWallets = $this->paymentsModule->api('userWallet')->getUserWallets();

    foreach ($userWallets as $userWallet) {
      if ($userWallet->currency === $localCurrency) {
        $hasLocalWallets = true;
        break;
      }
    }

    return [
      'showLocal' => (new SystemLanguage)->getCurrent() == $localLang
        || $hasLocalWallets
        || $this->getUserPaymentSettings()->currency === $localCurrency,
      'localInTheEnd' => (new SystemLanguage)->getCurrent() !== $localLang,
    ];
  }

  /**
   * @return Response
   */
  public function actionConvertBalance()
  {
    if ($this->convertBalance()) {
      $this->flashSuccess('main.operation_success');
    } else {
      $this->flashFail('main.operation_fail');
    }

    return $this->redirect(['balance']);
  }

  /**
   * Конвертировать баланс со старой валюты в новую
   * @return bool
   */
  private function convertBalance()
  {
    $convertApi = $this->paymentsModule->api('userBalanceConvertHandler', [
      'userId' => Yii::$app->user->id,
    ]);
    return $convertApi->getResult();
  }

  /**
   * Предоставить доступ к управлению кошельками по паролю
   * @param bool $denyAccess
   * @return array
   */
  public function actionPasswordCheck($denyAccess = false)
  {
    // TRICKY Идеальное решение для удобства тестирования подтверждения пароля. Можно удалить
    if ($denyAccess) {
      return $this->getWalletsManageAccess()->denyAccess();
    }

    if ($this->getWalletsManageAccess()->provideAccess(Yii::$app->request->post('password'))) {
      return AjaxResponse::success();
    } else {
      return AjaxResponse::error(Yii::_t('partners.payments.wrong_password'));
    }
  }

  /**
   * Получить компонент для управления доступом к кошелькам
   * @return UserWalletsManageAccess
   */
  private function getWalletsManageAccess()
  {
    return new UserWalletsManageAccess;
  }

  /**
   * спёр отсюда:
   * @see FileValidator::validateMimeType()
   * TODO грохнуть этот метод после появления другого более разумного решения
   * @param UploadedFile $file
   * @param array $allowedMimeTypes
   * @return bool
   */
  protected function validateMimeType($file, $allowedMimeTypes)
  {
    $fileMimeType = FileHelper::getMimeType($file->tempName);
    foreach ($allowedMimeTypes as $mimeType) {
      if ($mimeType === $fileMimeType) {
        return true;
      }
      if (strpos($mimeType, '*') !== false && preg_match($this->buildMimeTypeRegexp($mimeType), $fileMimeType)) {
        return true;
      }
    }
    return false;
  }

  /**
   * спёр отсюда:
   * @see FileValidator::buildMimeTypeRegexp()
   * TODO грохнуть этот метод после появления другого более разумного решения
   * @param $mask
   * @return string
   */
  private function buildMimeTypeRegexp($mask)
  {
    return '/^' . str_replace('\*', '.*', preg_quote($mask, '/')) . '$/';
  }

  /**
   * @return CurrenciesValues[] [$payed, $charged]
   */
  private function getPayedSums()
  {
    $sumFetcher = new UserPaymentsSum(Yii::$app->user->id);
    $payedSums = $sumFetcher->getGroupedByPaymentCurrency();
    $payed = new CurrenciesValues();
    foreach ($payedSums as $dbValues) {
      $payed->setValue($dbValues['currency'], $dbValues['payCompletedSum']);
    }

    $chargedSums = $sumFetcher->getGroupedByInvoiceCurrency();
    $charged = new CurrenciesValues();
    foreach ($chargedSums as $dbValues) {
      $charged->setValue($dbValues['currency'], $dbValues['chargedSum']);
    }

    return [$payed, $charged];
  }
}
