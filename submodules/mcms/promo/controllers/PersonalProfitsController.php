<?php

namespace mcms\promo\controllers;

use mcms\common\traits\Translate;
use mcms\common\web\AjaxResponse;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\promo\components\PersonalProfitsActualizeCourses;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\Module;
use Yii;
use mcms\promo\models\PersonalProfit;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * PersonalProfitsController implements the CRUD actions for PersonalProfit model.
 */
class PersonalProfitsController extends AdminBaseController
{

  use Translate;
  const LANG_PREFIX = 'promo.personal-profits.';

  public $layout = '@app/views/layouts/main';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete' => ['post'],
        ],
      ],
    ];
  }

  /**
   * @param \yii\base\Action $action
   * @return bool
   */
  public function beforeAction($action)
  {
    $this->getView()->title = self::translate('main');

    return parent::beforeAction($action);
  }

  /**
   * грид правил
   * @return string
   */
  public function actionIndex()
  {
    return $this->render('index', [
      'ignoreIds' => $this->getUserSelect2IgnoreIds(),
    ]);
  }

  /**
   * обработчик формы
   * @param PersonalProfit $model
   * @param $isPersonal
   * @return array|string
   */
  protected function handleForm(PersonalProfit $model, $isPersonal)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit')) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }

    return $this->renderAjax('form_modal', [
      'model' => $model,
      'isPersonal' => $isPersonal,
      'select2InitValues' => $this->getSelect2InitValues($model),
      'getUserCurrencyLinkParams' => Yii::$app->getModule('payments')->api('userSettingsData', ['userId' => 1])
        ->getUserCurrencyLinkParams(),
      'ignoreIds' => $this->getUserSelect2IgnoreIds(),
      'exchangeCourses' => PartnerCurrenciesProvider::getInstance()->getCoursesAsArray(),
    ]);
  }


  /**
   * @param null $userId
   * @return array|string
   */
  public function actionCreateModal($userId = null)
  {
    return $this->handleForm(new PersonalProfit(['user_id' => $userId]), (bool)$userId);
  }

  /**
   * Updates an existing PersonalProfit model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $user_id
   * @param integer $landing_id
   * @param integer $operator_id
   * @param $country_id
   * @param bool $isPersonal
   * @return mixed
   * @throws NotFoundHttpException
   */
  public function actionUpdateModal($user_id, $landing_id, $operator_id, $country_id, $provider_id, $isPersonal = false)
  {
    return $this->handleForm($model = $this->findModel($user_id, $landing_id, $operator_id, $country_id, $provider_id), $isPersonal);
  }

  /**
   * Deletes an existing PersonalProfit model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @Role({"root", "admin"})
   * @param integer $user_id
   * @param integer $landing_id
   * @param integer $operator_id
   * @param $country_id
   * @return mixed
   * @throws NotFoundHttpException
   * @throws \Throwable
   * @throws \yii\db\StaleObjectException
   */
  public function actionDelete($user_id, $landing_id, $operator_id, $country_id, $provider_id)
  {
    return AjaxResponse::set($this->findModel($user_id, $landing_id, $operator_id, $country_id, $provider_id)->delete());
  }

  /**
   * Актуализировать суммы по курсам валют
   * @param int $programId
   * @return array
   */
  public function actionActualizeCourses($programId = null)
  {
    $handler = new PersonalProfitsActualizeCourses();
    if ($programId) {
      $handler->partnerProgramId = $programId;
    }
    $handler->run();
    return AjaxResponse::success();
  }

  /**
   * @param $model
   * @return array
   */
  protected function getSelect2InitValues($model)
  {
    $select2InitValues = [];
    if ($model->landing_id) {
      $select2InitValues['landing_id'] = Landing::findOne($model->landing_id)->getStringInfo();
    }
    if ($model->operator_id) {
      $select2InitValues['operator_id'] = Operator::findOne($model->operator_id)->getStringInfo();
    }
    return $select2InitValues;
  }

  /**
   * Finds the PersonalProfit model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $user_id
   * @param integer $landing_id
   * @param integer $operator_id
   * @param $country_id
   * @return PersonalProfit the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($user_id, $landing_id, $operator_id, $country_id, $provider_id)
  {
    $model = PersonalProfit::findOne([
      'user_id' => $user_id,
      'landing_id' => $landing_id,
      'operator_id' => $operator_id,
      'country_id' => $country_id,
      'provider_id' => $provider_id,
    ]);
    if ($model !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @return array
   */
  private function getUserSelect2IgnoreIds()
  {
    $userRoles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
    if (!(array_key_exists('manager', $userRoles) || array_key_exists('reseller', $userRoles))) {
      return [];
    }

    $roles = ['manager'];

    $ignoreIds = Yii::$app->getModule('users')
      ->api('user')
      ->search([], true, -1, false, $roles);

    return array_map(function ($user) {
      return $user['id'];
    }, $ignoreIds);
  }
}
