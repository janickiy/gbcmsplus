<?php
namespace mcms\pages\controllers;

use mcms\common\web\AjaxResponse;
use mcms\pages\actions\partner_cabinet_styles\category\CreateCategoryModal;
use mcms\pages\actions\partner_cabinet_styles\category\DeleteCategory;
use mcms\pages\actions\partner_cabinet_styles\category\UpdateCategoryModal;
use mcms\pages\actions\partner_cabinet_styles\fields\CreateFieldModal;
use mcms\pages\actions\partner_cabinet_styles\fields\DeleteField;
use mcms\pages\actions\partner_cabinet_styles\fields\UpdateFieldModal;
use mcms\pages\models\PartnerCabinetStyleCategory;
use mcms\pages\models\PartnerCabinetStyleValue;
use Yii;
use mcms\common\controller\AdminBaseController;
use mcms\pages\models\PartnerCabinetStyle;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Оформление партнерского кабинета
 */
class PartnerCabinetStylesController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'create-field-modal' => [
        'class' => CreateFieldModal::class,
      ],
      'update-field-modal' => [
        'class' => UpdateFieldModal::class,
      ],
      'delete-field' => [
        'class' => DeleteField::class,
      ],

      'update-category-modal' => [
        'class' => UpdateCategoryModal::class,
      ],
      'create-category-modal' => [
        'class' => CreateCategoryModal::class,
      ],
      'delete-category' => [
        'class' => DeleteCategory::class,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    // Защита от CSRF. Экшены для изменения используя GET-параметры должны принимать только POST запросы
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'delete-style' => ['POST'],
          'delete-category' => ['POST'],
          'update-category-modal' => ['POST'],
          'create-category-modal' => ['POST'],
          'delete-field' => ['POST'],
          'update-field-modal' => ['POST'],
          'create-field-modal' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws \yii\web\ForbiddenHttpException
   */
  public function beforeAction($action)
  {
    $this->view->title = Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'styles_menu');

    return parent::beforeAction($action);
  }

  /**
   * Управление оформлениями, полями оформлений и категориями полей
   * @param int|null $styleId ID оформления
   * @return string
   */
  public function actionIndex($styleId = null)
  {
    $styleModel = $styleId ? $this->findModelStyle($styleId) : null;

    if ($styleModel) {
      $this->view->title = Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'style_title', ['name' => $styleModel->name]);
    }

    // Форма ввода
    if (!$styleModel || !$styleModel->load(Yii::$app->request->post())) {
      $styles = PartnerCabinetStyle::find()->orderBy('id')->all();
      // Если стиль не выбран, автоматически выбирается первый найденный
      if (!$styleModel && isset($styles[0])) {
        return $this->redirect(['index', 'styleId' => $styles[0]->id]);
      }

      return $this->render('index', [
        'styleModel' => $styleModel,
        'styles' => $styles,
        'categories' => PartnerCabinetStyleCategory::getFieldsWithValues($styleModel ? $styleModel->id : null),
      ]);
    }

    if (!Yii::$app->user->can('PagesCanUpdatePartnerCabinetStyle')) return '';
    // Валидация
    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      return ActiveForm::validate($styleModel);
    }

    if (!$saveStatus = $styleModel->save()) {
      return AjaxResponse::set($saveStatus);
    }

    $catsValues = Yii::$app->request->post((new PartnerCabinetStyleValue)->formName(), []);

    foreach ($catsValues as $catValues) {
      foreach ($catValues as $value) {
        $valueModel = new PartnerCabinetStyleValue;
        if ($value['id']) {
          $valueModel->id = $value['id'];
          $valueModel->setIsNewRecord(false);
        }
        $valueModel->load($value, '');

        $saveStatus = $saveStatus && $valueModel->save();
      }
    }

    return AjaxResponse::set($saveStatus);
  }

  /**
   * Создать оформление
   */
  public function actionCreateStyle()
  {
    $style = new PartnerCabinetStyle;
    $style->load(Yii::$app->request->post());
    // Валидация
    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      return ActiveForm::validate($style);
    }

    // Сохранение
    return AjaxResponse::set($style->insert(), ['styleUrl' => Url::to(['index', 'styleId' => $style->id])]);
  }

  /**
   * Удалить оформление
   * @param int $id
   * @return array
   */
  public function actionDeleteStyle($id)
  {
    $firstStyleModel = PartnerCabinetStyle::find()->where(['<>', 'id', $id])->orderBy('id')->one();
    return AjaxResponse::set(
      PartnerCabinetStyle::findOne($id)->delete() !== false,
      ['url' => Url::to(['index', 'styleId' => $firstStyleModel ? $firstStyleModel->id : null])]
    );
  }

  /**
   * Включить / выключить предпросмотр
   * @param int $id
   * @return array
   */
  public function actionToggleStylePreview($id)
  {
    PartnerCabinetStyle::togglePreview($this->findModelStyle($id)->id);

    return AjaxResponse::success();
  }

  /**
   * Активировать / деактивировать оформление
   * @param int $id
   * @return array
   */
  public function actionToggleStyleActivity($id)
  {
    return AjaxResponse::set($this->findModelStyle($id)->toggleActivity());
  }

  /**
   * Finds the PartnerCabinetStyle model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return PartnerCabinetStyle the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModelStyle($id)
  {
    if (($model = PartnerCabinetStyle::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}