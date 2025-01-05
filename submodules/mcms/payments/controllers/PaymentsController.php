<?php

namespace mcms\payments\controllers;

use admin\widgets\mass_operation\WidgetAction;
use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\mcms\payments\models\MassAutoPayout;
use mcms\payments\components\api\ExchangerPartnerCourses;
use mcms\payments\components\RemoteWalletBalances;
use mcms\payments\components\UserBalance;
use mcms\payments\lib\payprocess\components\PayoutServiceProxy;
use mcms\payments\models\AutoPayout;
use mcms\payments\models\ExchangerCourse;
use mcms\payments\models\forms\MassPayoutForm;
use mcms\payments\models\forms\ProcessPaymentForm;
use mcms\payments\models\PartnerCompany;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPaymentForm;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\Module;
use rgk\utils\actions\DownloadFileAction;
use rgk\utils\actions\UpdateModalAction;
use Yii;
use mcms\payments\models\UserPayment;
use mcms\payments\models\search\UserPaymentSearch;
use mcms\payments\components\controllers\BaseController;
use mcms\payments\models\export\UserPaymentExport;
use mcms\payments\models\export\UserPaymentExportForm;
use yii\base\Exception;
use yii\data\ArrayDataProvider;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

/**
 * PaymentsController implements the CRUD actions for UserPayment model.
 */
class PaymentsController extends BaseController
{

  const DEPENDENT_PARAM = 'depdrop_all_params';
  const DEPENDENT_USER_PARAM = 'userpaymentform-user_id';
  const DEPENDENT_OUTPUT_PARAM = 'output';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete' => ['POST'],
          'get-summary' => ['POST'],
          'get-ballances' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return parent::actions() + [
        'process-manual' => [
          'class' => UpdateModalAction::class,
          'scenario' => UserPaymentForm::SCENARIO_MANUAL,
          'modelClass' => UserPaymentForm::class,
          'saveFunction' => function (UserPaymentForm $model) {
            return $model->updateProcessToManual();
          },
          'errorMessage' => function (UserPaymentForm $model) {
            return $model->getLastError() ?: Yii::_t('app.common.Save failed');
          },
        ],
        'process-delay' => [
          'class' => UpdateModalAction::class,
          'scenario' => UserPaymentForm::SCENARIO_DELAY,
          'modelClass' => UserPaymentForm::class,
          'saveFunction' => function (UserPaymentForm $model) {
            return $model->delay();
          },
          'errorMessage' => function (UserPaymentForm $model) {
            return $model->getLastError() ?: Yii::_t('app.common.Save failed');
          },
        ],
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
        'mass-payout' => [
          'class' => WidgetAction::class,
          'model' => new MassPayoutForm(),
        ]
      ];
  }

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    $this->layout = '@app/views/layouts/main.php';
    $this->controllerTitle = Yii::_t('menu.module');
    return parent::beforeAction($action);
  }

  /**
   * Lists all UserPayment models.
   * @param null $status
   * @param null $payedAtTo
   * @param null $payedAtFrom
   * @param null $createdAtFrom
   * @return mixed
   */
  public function actionIndex($status = null, $payedAtTo = null, $payedAtFrom = null, $createdAtFrom = null)
  {
    $this->controllerTitle = Yii::_t('menu.payments');

    // TRICKY если здесь добавляются параметры к модели, то не забудь добавить и для @see UserPayment::getQuery()
    $searchModel = new UserPaymentSearch([
      'ignore_user_id' => Yii::$app->user->id,
      'status' => $status,
      'payed_at_to' => $payedAtTo,
      'payed_at_from' => $payedAtFrom,
      'created_at_from' => $createdAtFrom,
      'onlyPartners' => true,
    ]);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    /** @var \mcms\promo\Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');
    /** @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');

    $isAlternativePaymentsGridView = $paymentsModule->isAlternativePaymentsGridView();

    $massPayoutCount = (new MassAutoPayout)->findAvailablePayments()->count();

    return $this->render('index', compact(
      'searchModel', 'dataProvider', 'balance', 'promoModule', 'massPayoutCount', 'isAlternativePaymentsGridView'
    ));
  }

  /**
   * Сумма балансов всех пользователей
   * @return array
   */
  public function actionGetBalances()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return UserBalance::getBallancesGroupedByCurrency();
  }

  /**
   * Displays a single UserPayment model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    if (!$model->canView()) {
      return $this->redirect(['index']);
    }
    $this->view->title = Yii::_t('payments.payments.info') . ' #' . $id;

    /* @var $paymentSetting UserPaymentSetting */
    $paymentSetting = Yii::$app->getModule('payments')->api('userSettingsData', [
      'userId' => $model->user_id
    ])->getResult();

    $partnerCompany = $paymentSetting->getPartnerCompany()->one();

    return $this->render('view', compact('model', 'partnerCompany'));
  }

  /**
   * Creates a new UserPayment model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @param null $userId
   * @return mixed
   */
  public function actionCreate($userId = null)
  {
    $model = new UserPaymentForm(['scenario' => UserPaymentForm::SCENARIO_ADMIN_CREATE]);
    $model->user_id = $userId ?: null;
    //tricky Используются для описания периода в массовых выплатах, совсем выпиливать нельзя
    $model->from_date = date('Y-m-d');
    $model->to_date = date('Y-m-d');

    if ($model->load(Yii::$app->request->post())) {
      if ($model->save()) {
        $this->flashSuccess('app.common.saved_successfully');
        return $this->redirect(['view', 'id' => $model->id]);
      }
    }

    return $this->render('create', [
      'formData' => [
        'model' => $model,
      ]
    ]);
  }

  /**
   * Updates an existing UserPayment model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   * @throws HttpException
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    if (!$model->canEdit()) {
      throw new HttpException(423, Yii::_t('payments.payments.locked'));
    }

    if ($model->isReadonly()) {
      return $this->redirect(['view', 'id' => $model->id]);
    }

    $model->setScenario(UserPayment::SCENARIO_UPDATE);
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('update', [
      'formData' => [
        'model' => $model,
      ]
    ]);
  }

  /**
   * Deletes an existing UserPayment model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id)
  {
    $this->findModel($id)->delete();

    return $this->redirect(['index']);
  }

  /**
   * Детальная информация о выплате
   * @param $userId
   * @param $walletId
   * @param null $paymentId
   * @return array
   * @see \mcms\payments\controllers\ResellerController::actionUserDetail()
   */
  public function actionUserDetail($userId, $walletId, $paymentId = null)
  {
    /** @var Module $payments */
    /** @var ExchangerPartnerCourses $exchanger */
    /** @var UserBalance $balance */

    try {
      $paymentForm = $this->findModel($paymentId);
      $paymentForm->user_wallet_id = $walletId;
    } catch (NotFoundHttpException $e) {
      $paymentForm = new UserPaymentForm(['user_id' => $userId, 'user_wallet_id' => $walletId]);
    }

    if ($paymentForm->paymentAccount === null) {
      return AjaxResponse::error(Yii::_t('payments.error-user-wallet-not-defined'));
    }

    // Информация для конфирма конвертации
    $wallet = $paymentForm->userWallet;
    $payments = Yii::$app->getModule('payments');
    $balance = $payments->api('userBalance', ['userId' => $wallet->user_id]);
    $balanceCurrency = $balance->getCurrency();

    /* @var $paymentSetting UserPaymentSetting */
    $paymentSetting = Yii::$app->getModule('payments')->api('userSettingsData', [
      'userId' => $paymentForm->user_id
    ])->getResult();
    $partnerCompany = $paymentSetting->getPartnerCompany()->one();

    return AjaxResponse::success([
      // Информация о кошельке
      'view' => $this->renderAjax('_user_payments_info', [
        'formModel' => $paymentForm,
        'partnerCompany' => $partnerCompany
      ]),
      'canUseMultipleCurrenciesBalance' => $paymentForm->userPaymentSetting->canUseMultipleCurrenciesBalance(),
      // Информация для конфирма о конвертации
      'wallet' => ['currency' => $wallet->currency],
      'balance' => ['currency' => $balanceCurrency, 'amount' => $balance->getMain()],
      'convertCourse' => $balanceCurrency != $wallet->currency
        ? PartnerCurrenciesProvider::getInstance()
          ->getCurrencies()
          ->getCurrency($balanceCurrency)
          ->{'getTo' . $wallet->currency}()
        : null,
      // Информация о настройках ПС для поля акта и квитанции
      'isInvoiceFieldShow' => $paymentForm->walletModel->is_invoice_file_show,
      'isChequeFieldShow' => $paymentForm->walletModel->is_check_file_show,
    ]);
  }

  /**
   * @param null $id
   * @return mixed
   */
  public function actionValidate($id = null)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    if (Yii::$app->getRequest()->isPost) {
      $formModel = $id ? UserPaymentForm::findOne($id) : new UserPaymentForm();
      $formModel->setScenario($id ? UserPaymentForm::SCENARIO_UPDATE : UserPaymentForm::SCENARIO_ADMIN_CREATE);
      $formModel->load(Yii::$app->getRequest()->post());

      return ActiveForm::validate($formModel);
    }

    return [];
  }

  /**
   * Finds the UserPayment model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return UserPaymentForm the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = UserPaymentForm::findOne($id)) !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }


  /**
   * @return array|Response
   */
  public function actionExportValidate()
  {
    if (!(Yii::$app->request->isAjax && Yii::$app->request->isPost)) {
      return $this->redirect(['index']);
    }

    $userPaymentExportForm = new UserPaymentExportForm();
    $userPaymentExportForm->load(Yii::$app->request->post());

    // Нужно проверить только поля формы
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($userPaymentExportForm);
  }

  /**
   * @return array|string|Response
   */
  public function actionExport()
  {
    if (!Yii::$app->request->isAjax) {
      return $this->redirect(['index']);
    }
    $this->view->title = Yii::_t('export.export-bt');
    $userPaymentExportForm = new UserPaymentExportForm();

    if ($userPaymentExportForm->load(Yii::$app->request->post())) {
      if (\Yii::$app->request->post('submit')) {
        if ($userPaymentExportForm->validate() && $userPaymentExportForm->checkExport()) {
          // Генерация архива и возврат ссылки пользователю
          return AjaxResponse::success([
            'link' => (new UserPaymentExport(['exportForm' => $userPaymentExportForm]))->export()
          ]);
        } else {
          return AjaxResponse::error();
        }
      } else {
        return ActiveForm::validate($userPaymentExportForm);
      }
    }

    $userPaymentExportForm->prepareDefaultValues();

    return $this->renderAjax('_export', ['exportModel' => $userPaymentExportForm]);
  }

  /**
   * @param $id
   * @param null $pjaxContainer
   * @return array|string
   */
  public function actionProcessPayoutModal($id, $pjaxContainer = null)
  {
    if (!Yii::$app->request->isAjax) $this->redirect(['index']);
    $this->view->title = Yii::_t('payments.payments.info') . ' #' . $id;
    $model = new ProcessPaymentForm();
    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      return ActiveForm::validate($model);
    }

    $paymentInfo = self::findModel($id);

    $balance = new UserBalance(['userId' => UserPayment::getResellerId(), 'currency' => $paymentInfo->currency]);
//    $canSendToMgmp = ($balance->getResellerBalance() - $paymentInfo->amount) > 0;
    $canSendToMgmp = false; // TRICKY было актуально при реселлинге партнерки, сейчас не нужно

    $mgmpErrorText = $canSendToMgmp ? '' : Yii::_t('payout-info.reseller_insuficient_funds');

    $financeStart = Yii::$app->getModule('payments')->getLeftBorderDate();

    if (!$financeStart) {
      $canSendToMgmp = false;
      $mgmpErrorText = Yii::_t('payout-info.mgmp_not_available_for_payment');
    }
    if ($financeStart && $financeStart > Yii::$app->formatter->asDate($paymentInfo->created_at, 'php:Y-m-d')) {
      $canSendToMgmp = false;
      $mgmpErrorText = Yii::_t('payout-info.mgmp_not_available_for_payment');
    }

    $rgkCommission = $paymentInfo->rgk_processing_percent;

    if (is_null($rgkCommission)) {
      $canSendToMgmp = false;
      $mgmpErrorText = Yii::_t('payout-info.mgmp_not_available_for_payment');
    }

    /** @var RemoteWalletBalances $remoteBalanceComponent */
    $remoteBalanceComponent = Yii::$container->get(RemoteWalletBalances::class);
    $apiId = $paymentInfo->getSenderApiId();
    if ($apiId) {
      $remoteWalletBalance = $remoteBalanceComponent->get($apiId);
      $remoteWalletInsufficientFunds = $remoteWalletBalance !== null && $remoteWalletBalance < $paymentInfo->amount;
    } else {
      $remoteWalletInsufficientFunds = false;
    }

    $pjaxContainer = $pjaxContainer ?: 'user-payments-grid';

    /* @var $paymentSetting UserPaymentSetting */
    $paymentSetting = Yii::$app->getModule('payments')->api('userSettingsData', [
      'userId' => $paymentInfo->user_id
    ])->getResult();
    $currencyIsCurrent = $paymentSetting->getCurrentCurrency() === $paymentInfo->invoice_currency;

    $delayPaymentInfo = clone $paymentInfo;
    $delayPaymentInfo->scenario = UserPaymentForm::SCENARIO_DELAY;// иначе валидация не норм
    // Включен ли альтернативный вид грида выплат
    $isAlternativePaymentsGridView = Yii::$app->getModule('payments')->isAlternativePaymentsGridView();

    $autoPaymentInfo = clone $paymentInfo;
    $autoPaymentInfo->scenario = UserPaymentForm::SCENARIO_AUTOPAY;// иначе валидация не норм

    $partnerCompany = $paymentSetting->getPartnerCompany()->one();

    return $this->renderAjax('process_payout', compact(
      'model',
      'paymentInfo',
      'canSendToMgmp',
      'pjaxContainer',
      'currencyIsCurrent',
      'remoteWalletInsufficientFunds',
      'delayPaymentInfo',
      'mgmpErrorText',
      'paymentSetting',
      'isAlternativePaymentsGridView',
      'autoPaymentInfo',
      'partnerCompany'
    ));
  }

  /**
   * @param $id
   * @return array
   */
  public function actionProcessPayout($id)
  {
    if (!Yii::$app->request->isAjax) $this->redirect(['index']);

    $model = self::findModel($id);

    // TODO Эта проверка должна быть внутри методов
    if (!$model->isPayable()) {
      return AjaxResponse::success(['success' => false, 'message' => $model->getLastError()]);
    }

    $success = false;
    $message = Yii::_t('payments.user-payments.error-process');
    switch (Yii::$app->request->post('type')) {
      case 'delay': // todo удалить, но проверить выполнение из форма с файлами и через конфирм
        $success = $model->delay();
        $message = Yii::_t('payments.user-payments.error-delay');
        if ($success) $message = Yii::_t('payments.user-payments.payment-delayed');
        break;
      case 'cancel':
        $success = $model->cancel(Yii::$app->request->post('message'));
        $message = Yii::_t('payments.user-payments.error-cancel');
        if ($success) $message = Yii::_t('payments.user-payments.payment-cancelled');
        break;
      case 'annul':
        $success = $model->annul(Yii::$app->request->post('message'));
        $message = Yii::_t('payments.user-payments.error-annul');
        if ($success) $message = Yii::_t('payments.user-payments.payment-annulled');
        break;
      case 'auto':
        $autoPayout = new AutoPayout($model);
        $success = $autoPayout->pay();
        $message = $autoPayout->getMessage();
        if ($success && empty($message)) $message = Yii::_t('payments.user-payments.auto-payment-complete');
        break;
      case 'manual': // todo удалить, но проверить выполнение из форма с файлами и через конфирм
        $success = $model->updateProcessToManual();
        if ($success) $message = Yii::_t('payments.user-payments.maually-processed');
        if (!$success) $message = $model->getLastError();
        break;
      case 'mgmp':
        $model->setScenario($model::SCENARIO_SEND_TO_EXTERNAL);
        $success = $model->sendProcessToExternal();
        if ($success) $message = Yii::_t('payments.user-payments.send-external');
        else $message = $model->getLastError();
        break;
      default:
    }

    if (!$success && empty($message)) $message = Yii::_t('payments.user-payments.autopayout-fail');

    return AjaxResponse::success([
      'message' => $message,
      'success' => $success,
    ]);
  }

  public function actionProcessAuto($id)
  {
    if (!Yii::$app->request->isAjax) {
      $this->redirect(['index']);
    }

    $model = $this->findModel($id);
    $model->scenario = UserPaymentForm::SCENARIO_AUTOPAY;
    $model->load(Yii::$app->request->post());

    // TODO Эта проверка должна быть внутри методов
    if (!$model->isPayable()) {
      return AjaxResponse::success(['success' => false, 'message' => $model->getLastError()]);
    }

    $autoPayout = new AutoPayout($model);
    $success = $autoPayout->pay();
    $message = $autoPayout->getMessage();
    if ($success && empty($message)) {
      $message = Yii::_t('payments.user-payments.auto-payment-complete');
    }

    if (!$success && empty($message)) {
      $message = Yii::_t('payments.user-payments.autopayout-fail');
    }

    if ($success) {
      return AjaxResponse::success($success, $message);
    }

    return AjaxResponse::error($message);
  }

  /**
   * @param $id
   * @return string
   */
  public function actionPayoutInfo($id)
  {
    $info = PayoutServiceProxy::getPayoutInfo($id);

    return $this->renderAjax('_payout_info', [
      'paymentInfo' => $info,
    ]);
  }

  /**
   * Список кошельков
   * @param bool $filterByCurrency
   * @return array
   */
  public function actionDependentWallets($filterByCurrency = true)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    $userPaymentSetting = UserPaymentSetting::fetch($this->getDependentUserId());

    return [
      self::DEPENDENT_OUTPUT_PARAM => UserPayment::getUserWallets(
        $userPaymentSetting->user_id, $filterByCurrency ? $userPaymentSetting->getCurrentCurrency() : null,
        true
      )
    ];
  }

  /**
   * @return mixed
   * @throws Exception
   */
  private function getDependentUserId()
  {
    $dep = Yii::$app->request->post(self::DEPENDENT_PARAM);
    if (!$dep) throw new Exception('Wrong request');

    $userId = ArrayHelper::getValue($dep, self::DEPENDENT_USER_PARAM, false);
    if (!$userId) throw new Exception('Wrong request');

    return $userId;
  }

  /**
   * Кошельки реселлера
   * @return string|Response
   */
  public function actionResellerSettings()
  {
    /** @var \mcms\payments\Module $modulePayments */
    $modulePayments = Yii::$app->getModule('payments');
    if (!$modulePayments::canUserHaveBalance(Yii::$app->user->id)) {
      $this->flashFail('app.common.page_not_found');
      return $this->goBack();
    }

    $this->controllerTitle = Yii::_t('menu.reseller-payout-settings');
    $this->setBreadcrumb('users.payments', ['index']);
    $this->setBreadcrumb('users.settings');

    $paymentsSettingsForm = Yii::$app->getModule('payments')->api('userSettings', [
      'userId' => Yii::$app->user->id,
      'showAddSettings' => false,
      'renderCreateButton' => false,
    ])->getResult();

    return $this->render('reseller-settings', [
      'paymentsSettingsForm' => $paymentsSettingsForm,
      'userId' => Yii::$app->user->id
    ]);
  }

  /**
   * Добавить компенсацию
   * @return array|string|Response
   */
  public function actionAddCompensation()
  {
    $this->view->title = Yii::_t('payments.users.compensation');
    return $this->addInvoice(['scenario' => UserBalanceInvoice::SCENARIO_RESELLER_COMPENSATION]);
  }

  /**
   * Добавить штраф
   * @return array|string|Response
   */
  public function actionAddPenalty()
  {
    $this->view->title = Yii::_t('payments.users.penalty');
    return $this->addInvoice(['scenario' => UserBalanceInvoice::SCENARIO_RESELLER_PENALTY]);
  }

  /**
   * @param $params
   * @return array|string|Response
   */
  private function addInvoice($params)
  {
    if (!Yii::$app->request->isAjax) return $this->redirect(['index']);

    $errorMessage = [];
    $model = new UserBalanceInvoice();
    $model->setAttributes($params);

    $model->setScenario(ArrayHelper::getValue($params, 'scenario'));

    if ($model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit')) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }

    $usersModule = Yii::$app->getModule('users');
    return $this->renderAjax('_invoice_modal', compact('errorMessage', 'model', 'usersModule'));
  }
}
