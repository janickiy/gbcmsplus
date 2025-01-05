<?php

namespace mcms\promo\controllers;


use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use mcms\promo\components\BannerCompiler;
use mcms\promo\models\Banner;
use mcms\promo\models\BannerAttributeValue;
use mcms\promo\models\BannerTemplate;
use mcms\promo\models\search\BannerSearch;
use mcms\promo\models\search\BannerTemplateSearch;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

class BannersController extends AdminBaseController
{

  /**
   * @inheritdoc
   */
  public $layout = '@app/views/layouts/main';

  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('promo.banners.banner_list');
    return parent::beforeAction($action);
  }

  public function actionIndex()
  {
    $templatesDataProvider = (new BannerTemplateSearch())->search([]);
    $templatesDataProvider->pagination = false;
    $templatesDataProvider->sort = ['defaultOrder' => ['id' => SORT_ASC], 'sortParam' => 'templateSort'];

    $searchModel = new BannerSearch();

    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    if (!$searchModel->template_id && !empty($templateModels = $templatesDataProvider->getModels())) {
      $firstTemplate = current($templatesDataProvider->getModels());
      return $this->redirect(['index', $searchModel->formName() => ['template_id' => $firstTemplate->id]]);
    }

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
      'templatesDataProvider' => $templatesDataProvider
    ]);
  }

  public function actionCreate($templateId)
  {
    $this->getView()->title = Yii::_t('banners.create_banner');

    /** @var Banner $model */
    $model = new Banner([
      'scenario' => Banner::SCENARIO_CREATE,
      'template_id' => $templateId
    ]);

    $model->loadDefaultValues();

    if (
      $model->load(Yii::$app->request->post()) &&
      $model->loadValues(Yii::$app->request->post()) &&
      Model::validateMultiple(
        $model->valuesModels,
        (new BannerAttributeValue())->getAttributes(null, ['attribute_id'])
      )
    ) {

      $this->performAjaxValidation($model);

      $transaction = Yii::$app->db->beginTransaction();
      try {

        if ($model->save()) {
          $transaction->commit();
          return $this->redirect(['index', (new BannerSearch())->formName() => ['template_id' => $templateId]]);
        }
        $transaction->rollBack();
      } catch (\Exception $e) {
        $transaction->rollBack();
        throw $e;
      }
    }

    // подставляю значение затемнения по умолчанию
    $model->opacity = Banner::DEFAULT_OPACITY;
    return $this->render('form', [
      'model' => $model,
      'initialPreview' => false,
      'initialPreviewConfig' => false,
      'previewUrlRu' => Url::toRoute(['banners/form-preview', 'templateId' => $templateId, 'lang' => 'ru']),
      'previewUrlEn' => Url::toRoute(['banners/form-preview', 'templateId' => $templateId, 'lang' => 'en']),
    ]);

  }

  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    $model->setScenario($model::SCENARIO_UPDATE);

    $this->getView()->title = Yii::_t('banners.update_banner') . ' | ' . $model->name;

    $previews = [];
    $imagesDelete = [];

    if (
      $model->load(Yii::$app->request->post()) &&
      $model->loadValues(Yii::$app->request->post()) &&
      Model::validateMultiple(
        $model->valuesModels,
        (new BannerAttributeValue())->getAttributes(null, ['attribute_id'])
      )
    ) {

      $this->performAjaxValidation($model);

      $transaction = Yii::$app->db->beginTransaction();
      try {
        if ($model->save()) {
          $transaction->commit();
          return $this->redirect(['index', (new BannerSearch())->formName() => ['template_id' => $model->template_id]]);
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
      'previewUrlRu' => Url::toRoute(['banners/form-preview', 'bannerId' => $id, 'lang' => 'ru']),
      'previewUrlEn' => Url::toRoute(['banners/form-preview', 'bannerId' => $id, 'lang' => 'en']),
    ]);

  }

  public function actionFormPreview()
  {
    $banner = new Banner();
    $banner->loadValues(Yii::$app->request->post());

    $bannerValues = $banner->valuesModels;
    $compiler = null;
    $lang = Yii::$app->request->get('lang', 'ru');
    if (isset($_REQUEST['templateId'])) {
      /** @var BannerTemplate $template */
      $template = BannerTemplate::findOne((int)$_REQUEST['templateId']);
      $compiler = BannerCompiler::createFromTemplateAttributeValues($template, $bannerValues, $lang);
    } else if (isset($_REQUEST['bannerId'])) {
      /** @var Banner $banner */
      $banner = Banner::findOne((int)$_REQUEST['bannerId']);
      $banner->load(Yii::$app->request->post());

      $compiler = BannerCompiler::createFromTemplateAttributeValues(
        $banner->getTemplate()->one(),
        $bannerValues,
        $lang
      );
    }

    if (!$compiler) {
      return $this->redirect(['index']);
    }

    $this->layout = '@mcms/promo/views/layouts/banner_preview';
    return $this->render('form-preview', [
      'banner' => $banner,
      'compiled' => $compiler->compile()
    ]);
  }

  public function actionView($id, $language, $isIframe = false)
  {
    $banner = $this->findModel($id);

    if (!$isIframe) {
      $this->layout = '@mcms/promo/views/layouts/banner_preview';
      return $this->render('preview', [
        'banner' => $banner,
        'language' => $language
      ]);
    }

    return $this->renderPartial('iframe', [
      'compiled' => BannerCompiler::createFromBannerLanguage($banner, $language)->compile()
    ]);
  }

  /**
   * @param $id
   * @return Banner
   * @throws NotFoundHttpException
   */
  protected function findModel($id)
  {
    if (($model = Banner::findOne($id)) !== null) return $model;

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  protected function performAjaxValidation(Model $model)
  {
    if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      echo json_encode(ActiveForm::validate($model));
      Yii::$app->end();
    }
  }

  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_ENABLE);
    if($model->save()){
      return AjaxResponse::success();
    }
    return AjaxResponse::error();
  }

  public function actionDisable($id)
  {
    $model = $this->findModel($id);
    if ($model->is_default) {
      return AjaxResponse::error(Yii::_t('promo.banners.banner-cannot-be-disabled'));
    }
    $model->setScenario($model::SCENARIO_DISABLE);
    if($model->save()){
      return AjaxResponse::success();
    }
    return AjaxResponse::error();
  }

  protected function uploadFiles(Banner $model, $attribute)
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
}