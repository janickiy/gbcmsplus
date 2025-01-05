<?php

namespace mcms\payments\controllers;

use mcms\common\AdminFormatter;
use mcms\common\web\AjaxResponse;
use mcms\payments\components\controllers\BaseController;
use mcms\payments\components\mgmp\send\MgmpSenderInterface;
use mcms\payments\models\search\PaymentChunksSearch;
use mcms\payments\models\search\UserPaymentSearch;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentResellerForm;
use mcms\payments\models\UserWallet;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class ResellerCheckoutController
 * @package mcms\payments\controllers
 */
class ResellerCheckoutController extends BaseController
{
  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    $user = Yii::$app->getUser();
    $user->enableSession = false;

    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'create' => ['POST'],
          'view-modal' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * Выплаты реса
   * @return string
   */
  public function actionIndex()
  {
    $this->view->title = Yii::_t('payments.menu.reseller_balances_and_settlement');

    $search = new UserPaymentSearch([
      'user_id' => UserPayment::getResellerId()
    ]);
    $dataProvider = $search->search(Yii::$app->request->queryParams);

    /** @var PaymentQuery $query */
    $query = $dataProvider->query;
    $query->with('chunks');

    return $this->render('payments', [
      'canCreate' => Yii::$app->user->can('PaymentsResellerCheckoutCreate'),
      'dataProvider' => $dataProvider,
      'searchModel' => $search
    ]);
  }

  /**
   * @return array|string
   */
  public function actionCreate()
  {
    $model = new UserPaymentResellerForm(['scenario' => UserPaymentResellerForm::SCENARIO_CREATE_RESELLER_PAYMENT]);
    $model->user_id = UserPayment::getResellerId();
    $model->status = UserPayment::STATUS_AWAITING;

    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post("submit")) {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ActiveForm::validate($model);
      }
      return AjaxResponse::set($model->save());
    }

    $walletLimits = [];
    $isInvoiceFileShow = [];
    /** @var AdminFormatter $formatter */
    $formatter = Yii::$app->formatter;
    foreach (UserWallet::findByUser(UserPayment::getResellerId())->each() as $wallet) {
      $min = $wallet->walletType->getMinPayoutByCurrency($wallet->currency);
      $max = $wallet->walletType->getMaxPayoutByCurrency($wallet->currency);
      /* @var $wallet UserWallet */
      $monthLimit = $wallet->walletType->getPayoutLimitMonthlyByCurrency($wallet->currency);
      $dayLimit = $wallet->walletType->getPayoutLimitDailyByCurrency($wallet->currency);

      $walletLimits[$wallet->id]['min'] = $min ? Yii::_t('payments.wallets.min_payout_sum') . ': ' . $formatter->asPrice($min, $wallet->currency) : '';
      $walletLimits[$wallet->id]['max'] = $max ? Yii::_t('payments.wallets.max_payout_sum') . ': ' . $formatter->asPrice($max, $wallet->currency) : '';
      $walletLimits[$wallet->id]['dayLimit'] = $dayLimit
        ? Yii::_t('payments.wallets.payout_limit_daily') . ': ' .
        $formatter->asPrice($dayLimit - $wallet->getDailyLimitUse(), $wallet->currency) : '';
      $walletLimits[$wallet->id]['monthLimit'] = $monthLimit
        ? Yii::_t('payments.wallets.payout_limit_monthly') . ': ' .
        $formatter->asPrice($monthLimit - $wallet->getMonthlyLimitUse(), $wallet->currency) : '';
      $walletLimits[$wallet->id]['percent'] = Yii::_t('payments.payments.paysystem_commission', [
        'percent' => $wallet->walletType->getDefaultProfitPercent()
      ]);
      $isInvoiceFileShow[$wallet->id] = $wallet->walletType->is_invoice_file_show;
    }

    /** @var \mcms\payments\components\mgmp\send\MgmpSenderInterface $mgmp */
    $mgmp = Yii::createObject(MgmpSenderInterface::class);

    return $this->renderAjax('_create', [
      'model' => $model,
      'isInvoiceFileShow' => $isInvoiceFileShow,
      'walletLimits' => $walletLimits,
      'resellerPayPeriod' => $mgmp->getResellerPayPeriod(),
      'resellerPayPeriodEndDate' => $mgmp->getResellerPayPeriodEndDate(),
    ]);
  }

  /**
   * @param $id
   * @return array|string
   */
  public function actionViewModal($id)
  {
    $model = $this->findModel($id, true);

    $chunkDataProvider = (new PaymentChunksSearch(['paymentId' => $id]))->search([]);
    $chunkDataProvider->setPagination(false);

    return $this->renderAjax('view', [
      'model' => $model,
      'chunkDataProvider' => $chunkDataProvider
    ]);
  }

  /**
   * @param $id
   * @param bool $withChunks
   * @return UserPayment|ActiveRecord
   * @throws NotFoundHttpException
   */
  private function findModel($id, $withChunks = false)
  {
    $query = UserPayment::find()->andWhere(['id' => $id]);

    if ($withChunks) {
      $query->with('chunks');
    }
    $model = $query->one();
    if ($model !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

}