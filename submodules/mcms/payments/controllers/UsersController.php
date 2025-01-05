<?php
namespace mcms\payments\controllers;

use mcms\common\web\AjaxResponse;
use mcms\holds\models\PartnerHoldSearch;
use mcms\holds\Module;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserWallet;
use mcms\payments\components\controllers\BaseController;
use mcms\payments\components\UserBalance;
use mcms\payments\models\search\UserBalanceInvoiceSearch;
use mcms\payments\models\search\UserPaymentSearch;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\wallet\Wallet;
use mcms\promo\models\Country;
use rgk\utils\components\CurrenciesValues;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\widgets\ActiveForm;

class UsersController extends BaseController
{
  const DEPENDENT_PARAM = 'depdrop_all_params';
  const DEPEND_CURRENCY_PARAM = 'currency';
  const DEPEND_WALLET_PARAM = 'wallet_type';
  const DEPENDENT_OUTPUT_PARAM = 'output';

  public function beforeAction($action)
  {
    $this->layout = '@app/views/layouts/main.php';
    $this->controllerTitle = Yii::_t('menu.module');
    return parent::beforeAction($action);
  }

  /**
   * @inheritDoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete-wallet' => ['post'],
        ]
      ]
    ];
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function actionBalanceInvoice()
  {
    /** @var UserBalanceInvoiceSearch $searchModel */
    $searchModel = Yii::createObject(UserBalanceInvoiceSearch::class);

    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    /** @var Query $query */
    $query = $dataProvider->query;

    $footerQuery = ActiveQuery::create($query);

    $footerResult = $footerQuery
      ->select([
        'sum' => 'SUM(amount)',
        'currency',
      ])
      ->from(UserBalanceInvoice::tableName())
      ->groupBy('currency')
      ->all();

    $footerValues = CurrenciesValues::createByValues(ArrayHelper::map($footerResult, 'currency', 'sum'));

    return $this->render('invoices', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'footerValues' => $footerValues,
    ]);
  }

  /**
   * @param $id
   * @return mixed
   * @throws \Exception
   */
  public function actionView($id)
  {
    /** @var \mcms\payments\Module $modulePayments */
    $modulePayments = Yii::$app->getModule('payments');
    if (!$modulePayments::canUserHaveBalance($id)) {
      $this->flashFail('app.common.page_not_found');
      return $this->goBack();
    }

    $roles = Yii::$app->authManager->getRolesByUser($id);
    $user = $this->getUser($id, true);
    $userModule = $this->getUserModule();

    $userPaymentSetting = UserPaymentSetting::fetch($id);
    $balance = new UserBalance([
      'userId' => $id,
      'currency' => $userPaymentSetting->getCurrentCurrency()
    ]);

    $paymentsModel = new UserPaymentSearch(['user_id' => $id]);

    $paymentsDataProvider = $paymentsModel->search([]);

    return $this->render('view', [
      'user' => $user,
      'balance' => $balance,
      'paymentsDataProvider' => $paymentsDataProvider,
      'summaryToPayment' => $balance->getMain(),
      'userPaymentSettings' => $userPaymentSetting,
    ]);
  }

  /**
   * @param $id
   * @return string
   * @throws \Exception
   */
  public function actionProfit($id)
  {
    $this->layout = '@app/views/layouts/main';

    $searchModel = new PartnerHoldSearch(['userId' => $id]);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    /** @var Module $holdsModule */
    $holdsModule = Yii::$app->getModule('holds');

    return $this->render('profit-list', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'countries' => Country::getDropdownItems(),
      'canViewLastUnholdDate' => $holdsModule->canViewLastUnholdDate(),
    ]);
  }

  /**
   * @param $id
   * @return array|bool
   */
  public function actionUpdateSettings($id)
  {
    if (!Yii::$app->request->isAjax) $this->redirect(['index']);

    /** @var $model UserPaymentSetting */
    $model = UserPaymentSetting::fetch($id);
    $model->scenario = UserPaymentSetting::getUpdateSettingsScenario();

    if ($model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit')) {
        if ($model->save()) {
          return AjaxResponse::success();
        }
        return AjaxResponse::error();
      }
    }
    return ActiveForm::validate($model);
  }

  /**
   * @return array|bool
   */
  public function actionUpdatePartnerSettings()
  {
    $id = Yii::$app->getUser()->id;
    Yii::$app->response->format = Response::FORMAT_JSON;

    /** @var $model UserPaymentSetting */
    $model = UserPaymentSetting::fetch($id);
    if ($model->isNewRecord) {
      $model->setScenario(UserPaymentSetting::SCENARIO_ADMIN_CREATE);
    } else {
      $model->setScenario(UserPaymentSetting::SCENARIO_PARTNER_UPDATE);
    }

    if ($model->load(Yii::$app->request->post()) && Yii::$app->request->post('submit')) {
      return AjaxResponse::set($model->save());
    }

    return ActiveForm::validate($model, !$model->isEmptyWalletAccount() ? $model->wallet_account : null);
  }

  public function actionUpdate($id)
  {
    $userId = $id;

    $model = UserPaymentSetting::fetch($userId);
    return $this->render('update', compact('userId', 'model'));
  }

  public function actionUpdatePartner($isModal = false)
  {
    if (!Yii::$app->request->isAjax) $this->redirect(['index']);

    return $this->handleUpdateAjax($isModal);
  }

  private function handleUpdateAjax($isModal = false)
  {
    return $this->renderAjax('_user_settings', [
      'userId' => Yii::$app->getUser()->id,
      'currency' => Yii::$app->request->get('currency'),
      'wallet_type' => Yii::$app->request->get('wallet_type'),
      'getPartial' => true,
      'isModal' => $isModal,
    ]);
  }

  /**
   * Список кошельков
   * @return array
   * @throws Exception
   */
  public function actionDependentWallets()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $dep = Yii::$app->request->post(self::DEPENDENT_PARAM);
    if (!$dep) throw new Exception('Wrong request');

    return [
      self::DEPENDENT_OUTPUT_PARAM => Wallet::getByCurrency(
        ArrayHelper::getValue($dep, self::DEPEND_CURRENCY_PARAM),
        true
      )
    ];
  }

  public function actionAddPenalty($id)
  {
    $this->view->title = Yii::_t('users.penalty');

    return $this->addInvoice($id, ['scenario' => UserBalanceInvoice::SCENARIO_PENALTY]);
  }

  public function actionAddCompensation($id)
  {
    $this->view->title = Yii::_t('users.compensation');

    return $this->addInvoice($id, ['scenario' => UserBalanceInvoice::SCENARIO_COMPENSATION]);
  }

  private function addInvoice($userId, $invoiceParams)
  {
    if (!Yii::$app->request->isAjax) return $this->redirect(['index']);

    $model = new UserBalanceInvoice();
    $model->setAttributes(array_merge([
      'user_id' => $userId,
    ], $invoiceParams));

    $model->setScenario(ArrayHelper::getValue($invoiceParams, 'scenario'));
    if ($model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit')) {
        return AjaxResponse::set($model->save());
      }

      return ActiveForm::validate($model);
    }

    return $this->renderAjax('_invoice_modal', [
      'model' => $model,
      'userPaymentSettings' => UserPaymentSetting::fetch($userId),
    ]);
  }

  public function actionGetUserCurrency($userId)
  {
    if (!Yii::$app->request->isAjax) throw new BadRequestHttpException;

    if (!$userId) return AjaxResponse::error('userId not defined');

    $currencyCode = UserPaymentSetting::fetch($userId)->getCurrency();

    $promoCurrencies = Yii::$app->getModule('promo')->api('mainCurrencies')->getResult();

    $promoCurrencies = ArrayHelper::index($promoCurrencies, 'code');

    return AjaxResponse::success(ArrayHelper::getValue($promoCurrencies, $currencyCode));
  }

  /**
   * @param null $id
   * @param null $userId
   * @param null $currency
   * @return array|string
   * @throws ServerErrorHttpException
   */
  public function actionWalletModal($id = null, $userId = null, $currency = null)
  {
    // Инициализация модели
    if ($id) {
      $wallet = UserWallet::find(false)->andWhere(['id' => $id])->one();
    } else {
      $paymentSettings = UserPaymentSetting::fetch($userId);
      if (!$currency) $currency = $paymentSettings->getCurrentCurrency();
      $wallet = new UserWallet(['user_id' => $userId, 'currency' => $currency]);
    }

    // Заполнение данных о кошельке и реквизитов
    $walletLoad = $wallet->load(Yii::$app->request->post());
    $walletAccount = $wallet->getAccountObject();
    if ($walletAccount) {
      $walletAccount->scenario = $walletAccount::SCENARIO_ADMIN;
      $walletAccount->load(Yii::$app->request->post());
    }

    // Рендер формы
    if (!$walletLoad || Yii::$app->request->isPjax) {
      $wallets = ArrayHelper::map(Wallet::getByCurrency($wallet->currency), 'id', 'name');

      return $this->renderAjax('_wallet_modal', [
        'model' => $wallet,
        'walletAccount' => $walletAccount,
        'wallets' => $wallets,
      ]);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    // Валидация типа кошелька и валюты
    $walletFormErrors = ActiveForm::validate($wallet, ['wallet_type', 'currency']);
    if ($walletFormErrors) return $walletFormErrors;

    // Валидация реквизитов
    if (!$walletAccount) throw new ServerErrorHttpException('Неизвестная ошибка');
    if (!Yii::$app->request->post('submit')) return ActiveForm::validate($walletAccount);

    // Валидация кошелька, привязка реквизитов и сохранение
    if ($wallet->setAccount($walletAccount) && $wallet->save()) {
      return AjaxResponse::success();
    }

    Yii::$app->response->statusCode = 500;
    return AjaxResponse::error($wallet->hasErrors() ? $wallet->getOneError() : null);
  }

  public function actionDeleteWallet($id)
  {
    $wallet = UserWallet::findOne($id);
    if (!$wallet) throw new NotFoundHttpException;

    return AjaxResponse::set(
      Yii::$app->getModule('payments')->api('userWallet', ['walletId' => $wallet->id, 'user_id' => $wallet->user_id])->delete()
    );
  }

  public function actionDeleteWalletFiles()
  {
    $result = Yii::$app->getModule('payments')->api('userWallet', [
      'user_id' => Yii::$app->request->post('user_id'),
      'walletId' => Yii::$app->request->post('walletId'),
    ])->deleteFile(Yii::$app->request->post('key'));

    if (!$result) {
      return AjaxResponse::error(Yii::_t('payments.wallets.can_not_remove_file'));
    }
    return AjaxResponse::set($result);
  }
}
