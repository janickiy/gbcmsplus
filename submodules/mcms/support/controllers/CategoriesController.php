<?php

namespace mcms\support\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\controller\AdminBaseController;
use mcms\common\behavior\ModelFetcher;
use mcms\common\web\AjaxResponse;
use mcms\support\components\storage\CategoryStorage;
use mcms\support\models\SupportCategory;
use mcms\support\models\SupportCategorySearch;
use yii\base\Model;
use yii\web\Response;
use yii\widgets\ActiveForm;
use Yii;

/**
 * Class CategoriesController
 * @package mcms\support\controllers
 * @method fetch($id)
 *
 */
class CategoriesController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

  private $categoryStorage;

  public function __construct($id, $module, $config = [], CategoryStorage $categoryStorage)
  {
    parent::__construct($id, $module, $config);
    $this->categoryStorage = $categoryStorage;
  }

  public function behaviors()
  {
    return array_merge(parent::behaviors(), [
      [
        'class' => ModelFetcher::class,
        'defaultAction' => $this->defaultAction,
        'storage' => $this->categoryStorage,
        'controller' => $this
      ]
    ]);
  }

  public function actionList()
  {
    $searchModel = new SupportCategorySearch();

    $this->getView()->title = Yii::_t('support.controller.categories_title');

    return $this->render('list', [
      'dataProvider' => $searchModel->search(\Yii::$app->request->queryParams),
    ]);
  }

  /**
   * @param $id
   * @return string
   */
  public function actionUpdate($id)
  {
    $this->getView()->title = Yii::_t('support.controller.categories_editing');

    /** @var SupportCategory $model */
    $model = $this->fetch($id);
    $model->scenario = $model::SCENARIO_EDIT;

    return $this->formHandle($model);
  }

  private function formHandle(SupportCategory $model)
  {
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($model);
      } else {
        // Сохранение
        return AjaxResponse::set($model->save());
      }
    }

    if (!$model->isNewRecord) {
      $model->roles = ArrayHelper::getColumn($model->getRoles()->each(), 'name');
    }

    $roles = Yii::$app->getModule('users')->api('roles')->getResult();

    return $this->renderAjax('form', [
      'model' => $model,
      'roles' => $roles,
    ]);
  }


  /**
   * Performs ajax validation.
   * @param Model $model
   * @throws \yii\base\ExitException
   */
  protected function performAjaxValidation(Model $model)
  {
    if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      echo ActiveForm::validate($model);
      Yii::$app->end();
    }
  }

  /**
   * @return string
   */
  public function actionCreate()
  {
    $this->getView()->title = Yii::_t('support.controller.categories_creating');

    $model = new SupportCategory();
    $model->scenario = $model::SCENARIO_CREATE;

    return $this->formHandle($model);
  }

  /**
   * @param $id
   * @return \yii\web\Response
   */
  public function actionEnable($id)
  {
    /** @var SupportCategory $model */
    $model = $this->fetch($id);
    $model->scenario = SupportCategory::SCENARIO_ENABLE;

    return AjaxResponse::set($model->setEnabled()->save());
  }

  /**
   * @param $id
   * @return \yii\web\Response
   */
  public function actionDisable($id)
  {
    /** @var SupportCategory $model */
    $model = $this->fetch($id);
    $model->scenario = SupportCategory::SCENARIO_DISABLE;

    return AjaxResponse::set($model->setDisabled()->save());
  }
}