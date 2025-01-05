<?php
namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\promo\models\BuyoutCondition;
use mcms\promo\models\search\BuyoutConditionSearch;
use rgk\utils\components\response\AjaxResponse;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use mcms\statistic\Module as StatisticModule;

/**
 * Условия выкупа
 */
class BuyoutConditionsController extends AdminBaseController
{
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
          'delete' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * Список
   */
  public function actionIndex()
  {
    $settingBuyoutMinutes = (int)Yii::$app->settingsManager->getValueByKey(StatisticModule::SETTINGS_BUYOUT_MINUTES);
    $settingBuyoutAfterFirstRebillOnly = (int)Yii::$app->settingsManager->getValueByKey(StatisticModule::SETTINGS_BUYOUT_AFTER_1ST_REBILL_ONLY);

    $this->getView()->title = Yii::_t('buyout_conditions.title');
    $searchModel = new BuyoutConditionSearch;
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'settingBuyoutMinutes' => $settingBuyoutMinutes,
      'settingBuyoutAfterFirstRebillOnly' => $settingBuyoutAfterFirstRebillOnly,
    ]);
  }

  /**
   * Создание условия
   * @return string
   */
  public function actionCreate()
  {
    $model = new BuyoutCondition();
    return $this->handleSaveForm($model);
  }

  /**
   * Редактирование условия
   * @param $id
   * @return array|string
   * @throws NotFoundHttpException
   */
  public function actionUpdateModal($id)
  {
    $model = $this->findModel($id);
    return $this->handleSaveForm($model);
  }

  /**
   * @param BuyoutCondition $model
   * @return array|string
   */
  protected function handleSaveForm(BuyoutCondition $model)
  {
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($model);
      }
      // Сохранение
      return AjaxResponse::set($model->save());
    }

    // надо чтоб на вьюхе сразу был выбран пункт ДА для радио. Это можно сделать только присвоив значение модели
    if (!$model->is_buyout_only_after_1st_rebill) {
      $model->is_buyout_only_after_1st_rebill = 1;
    }
    if (!$model->is_buyout_only_unique_phone) {
      $model->is_buyout_only_unique_phone = 1;
    }

    return $this->renderAjax('form', [
      'model' => $model,
    ]);
  }

  /**
   * @param $id
   * @return array
   * @throws NotFoundHttpException
   * @throws \yii\db\StaleObjectException
   */
  public function actionDelete($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete() !== false);
  }

  /**
   * Finds the PartnerProgram model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return BuyoutCondition the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    $model = BuyoutCondition::findOne((int)$id);
    if ($model !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
