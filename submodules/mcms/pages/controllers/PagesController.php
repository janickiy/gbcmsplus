<?php

namespace mcms\pages\controllers;

use mcms\common\web\AjaxResponse;
use mcms\common\helpers\ArrayHelper;
use mcms\pages\models\Category;
use mcms\pages\models\CategoryProp;
use mcms\pages\models\PageProp;
use mcms\pages\models\search\CategorySearch;
use Yii;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\widgets\ActiveForm;
use mcms\pages\models\Page;
use mcms\pages\models\PageSearch;
use yii\web\NotFoundHttpException;
use yii\base\Model;
use vova07\imperavi\actions\UploadFileAction;
use vova07\imperavi\actions\GetImagesAction;
use mcms\common\helpers\Html;
use yii\helpers\Url;
use yii\web\UploadedFile;
use mcms\common\controller\AdminBaseController;


/**
 * PagesController implements the CRUD actions for Page model.
 */
class PagesController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  public function actions()
  {
    return [
      'image-upload' => [
        'class' => UploadFileAction::class,
        'url' => '/uploads/' . $this->module->id . '/' . $this->id . '/',
        'path' => '@uploadPath/' . $this->module->id . '/' . $this->id . '/'
      ],
      'images-get' => [
        'class' => GetImagesAction::class,
        'url' => '/uploads/' . $this->module->id . '/' . $this->id . '/',
        'path' => '@uploadPath/' . $this->module->id . '/' . $this->id . '/',
      ]
    ];
  }

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    $this->controllerTitle = Yii::_t('main.list_of_pages');

    return parent::beforeAction($action);
  }

  public function actionIndex()
  {
    $this->controllerTitle = Yii::_t('pages.menu.mainpage');
    $categoriesDataProvider = (new CategorySearch())->search([]);
    $categoriesDataProvider->query->with(['activePages']);
    $categoriesDataProvider->pagination = false;
    $categoriesDataProvider->sort = ['sortParam' => 'catsort', 'defaultOrder' => ['id' => SORT_ASC]];

    $searchModel = new PageSearch(['scenario' => PageSearch::SCENARIO_SEARCH]);

    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    if (!$searchModel->page_category_id && !empty($categoriesModels = $categoriesDataProvider->getModels())) {
      $firstCategory = current($categoriesDataProvider->getModels());
      return $this->redirect(['index', $searchModel->formName() => ['page_category_id' => $firstCategory->id]]);
    }

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'categoriesDataProvider' => $categoriesDataProvider
    ]);
  }

  public function actionView($id)
  {
    $model = $this->findModel($id);

    $this->view->title = $model->name;
    $this->controllerTitle = $this->view->title;

    return $this->renderAjax('view', [
      'model' => $model,
    ]);
  }

  public function actionCreate($categoryId)
  {
    $this->controllerTitle = Yii::_t('main.create');

    $model = new Page([
      'scenario' => Page::SCENARIO_CREATE,
      'page_category_id' => $categoryId
    ]);

    $model->loadDefaultValues();



    if (
      $model->load(Yii::$app->request->post()) &&
      $model->loadProps(Yii::$app->request->post()) &&
      Model::validateMultiple($model->propModels, (new PageProp())->getAttributes(null, ['page_category_prop_id']))
    ) {

      $this->performAjaxValidation($model);

      $transaction = Yii::$app->db->beginTransaction();
      try {

        $this->uploadFiles($model, 'images');

        if ($model->save()) {
          $transaction->commit();

          $this->flashSuccess('app.common.Saved successfully');
          return $this->redirect(['update', 'id' => $model->id]);
        }
        $transaction->rollBack();
      } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
      }
    }

    return $this->render('form', [
      'model' => $model,
      'initialPreview' => false,
      'initialPreviewConfig' => false,
      'canUploadImage' => Html::hasUrlAccess(['pages/image-upload/']),
      'canGetImage' => Html::hasUrlAccess(['pages/images-get/']),
    ]);

  }

  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    $model->setScenario($model::SCENARIO_UPDATE);

    $this->controllerTitle = Yii::_t('main.update') . ' | ' . $model->name;

    $previews = [];
    $imagesDelete = [];
    $canDelete = Html::hasUrlAccess(['pages/file-delete/']);
    if (!empty($model->images)) {
      $model->images = unserialize($model->images);
      foreach ($model->images as $key => $img) {
        $previews[] = Html::img($img, ['class' => 'file-preview-image']);
        $deleteData = ['width' => "50px", 'key' => $key];
        $canDelete && $deleteData['url'] = Url::toRoute(['file-delete', 'id' => $model->id]);
        $imagesDelete[] = $deleteData;
      }
    }


    if (
      $model->load(Yii::$app->request->post()) &&
      $model->loadProps(Yii::$app->request->post()) &&
      Model::validateMultiple($model->propModels, (new PageProp())->getAttributes(null, ['page_category_prop_id']))
    ) {

      $this->performAjaxValidation($model);

      $transaction = Yii::$app->db->beginTransaction();
      try {

        $this->uploadFiles($model, 'images');

        if ($model->save()) {
          $this->flashSuccess('app.common.Saved successfully');
          $transaction->commit();
        }
        $transaction->rollBack();
      } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
      }
    }

    return $this->render('form', [
      'model' => $model,
      'initialPreview' => $previews,
      'initialPreviewConfig' => $imagesDelete,
      'canUploadImage' => Html::hasUrlAccess(['pages/image-upload/']),
      'canGetImage' => Html::hasUrlAccess(['pages/images-get/']),
    ]);

  }

  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    if ($model->delete()) {
      return AjaxResponse::success();
    }

    return AjaxResponse::error();
  }

  /**
   * Finds the Page model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Page the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Page::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
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
      echo json_encode(ActiveForm::validate($model));
      Yii::$app->response->content = "";
      Yii::$app->end();
    }
  }

  /**
   * @param $id
   * @return Response
   * @throws NotFoundHttpException
   */
  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_ENABLE);
    if($model->save()){
      return AjaxResponse::success();
    }
    return AjaxResponse::error();
  }

  /**
   * @param $id
   * @return Response
   * @throws NotFoundHttpException
   */
  public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_DISABLE);
    if($model->save()){
      return AjaxResponse::success();
    }
    return AjaxResponse::error();
  }

  /**
   * Загрузка картинок
   * @param $model
   * @param $attribute
   */
  protected function uploadFiles($model, $attribute)
  {
    $storedImages = [];
    $newFiles = [];

    if (!$model->isNewRecord) {
      $storedImages = unserialize($model->getOldAttribute($attribute));
    }

    if ($model->{$attribute} = UploadedFile::getInstances($model, $attribute)) {
      $url = '/uploads/' . $this->module->id . '/' . $this->id . '/gallery/';
      $path = '@uploadPath/' . $this->module->id . '/' . $this->id . '/gallery/';
      FileHelper::createDirectory(Yii::getAlias($path));
      foreach ($model->{$attribute} as $file) {
        // store the source file name
        $ext = pathinfo($file->name)['extension'];
        // generate a unique file name
        $newFilename = Yii::$app->security->generateRandomString() . ".{$ext}";
        $newFiles[] = $url . $newFilename;
        $file->saveAs(Yii::getAlias($path) . $newFilename);
      }
    }
    $model->{$attribute} = serialize(array_unique(ArrayHelper::merge($storedImages, $newFiles)));
  }

  /**
   * Удаление картинок по одной
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionFileDelete($id, $isProp = 0)
  {
    if (!Yii::$app->request->isAjax) return true;
    Yii::$app->response->format = Response::FORMAT_JSON;

    if ($isProp && $pageProp = PageProp::findOne($id)) {
      /** @var  $pageProp PageProp */
      $pageProp->deleteFile(Yii::$app->request->post('key'), Yii::$app->request->post('lang'));
      return json_encode([]);
    }

    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_FILE_DELETE);

    $key = \Yii::$app->request->post('key');
    $images = unserialize($model->images);
    $file = ArrayHelper::remove($images, $key);

    $pathToFile = '..' . $file;
    if (is_file($pathToFile)) {
      unlink($pathToFile);
    }
    $model->images = serialize($images);
    $model->save();

    return json_encode([]);

  }
}
