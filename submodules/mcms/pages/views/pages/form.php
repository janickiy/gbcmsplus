<?php
use kartik\form\ActiveForm;
use kartik\builder\Form;
use kartik\file\FileInput;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use yii\helpers\Url;
use yii\helpers\Html;
use mcms\pages\components\widgets\PagePropsWidget;
use mcms\pages\models\Category;

/** @var $canUploadImage bool может ли позьзователь загружать изображения */
/** @var $canGetImage bool может ли позьзователь просматривать изображения на сервере */

$this->render('actions', ['id' => $model->id]);
$this->beginBlock('actions');
//if (isset($this->blocks['list_button'])) {
//  echo $this->blocks['list_button'];
//}
echo Html::a(
  '<i class="glyphicon glyphicon-list"></i> ' . Yii::_t("main.list_of_pages"),
  ['index', 'PageSearch'=>['page_category_id' =>$model->page_category_id]],
  ['class' => 'btn btn-primary btn-labeled', 'data-pjax' => 0]
);
echo Html::a(
  '<i class="fa fa-plus"></i> ' .
  Yii::_t('main.create_page'),
  ['create', 'categoryId' => $model->page_category_id],
  ['class' => 'btn btn-success btn-labeled', 'data-pjax' => 0]
);
$this->endBlock();
?>

<?php
$form = ActiveForm::begin([
  'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data'],
  'enableAjaxValidation' => true
]);

$attributes = [
  'name' => ['type' => Form::INPUT_TEXT],
  'text' => ['type' => Form::INPUT_WIDGET,
    'widgetClass' => 'vova07\imperavi\Widget', 'options' => [
      'settings' => [
        'minHeight' => '300px',
        'imageUpload' => $canUploadImage ? Url::toRoute(['pages/image-upload/']) : null,
        'imageManagerJson' => $canGetImage ? Url::toRoute(['pages/images-get/']) : null,
        'plugins' => ['imagemanager', 'fullscreen'],
        'paragraphize' => false
      ]
    ]
  ]
];

$pageCategory = $model->getCategory()->one();

if ($pageCategory && $pageCategory->is_seo_visible) {
  $attributes['seo_title'] = ['type' => Form::INPUT_TEXT];
  $attributes['seo_keywords'] = ['type' => Form::INPUT_TEXT];
  $attributes['seo_description'] = ['type' => Form::INPUT_TEXTAREA, 'options' => ['rows' => 6]];
}
?>

<div class="row">
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title pull-left"><?=$this->title?></h3>
        <div class="clearfix"></div>
      </div>
      <div class="panel-body">

        <?= MultiLangForm::widget([
          'model' => $model,
          'form' => $form,
          'attributes' => $attributes
        ]); ?>

        <label class="control-label"><?php echo $model->getAttributeLabel('images') ?></label>

        <?= FileInput::widget([
          'model' => $model,
          'attribute' => 'images[]',
          'options' => ['multiple' => true],
          'pluginOptions' => [
            'showUpload' => false,
            'overwriteInitial' => false,
            'initialPreview' => $initialPreview,
            'initialPreviewConfig' => $initialPreviewConfig
          ]
        ]); ?>

        <div class="clearfix"></div>

        <h4 style="margin-top: 30px"><?= Yii::_t('main.page-props') ?>:</h4>
        <hr>

        <div class="col-md-12">
          <?= PagePropsWidget::widget([
            'form' => $form,
            'page' => $model
          ])?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title pull-left"><?=Yii::_t('main.settings')?></h3>
        <div class="clearfix"></div>
      </div>
      <div class="panel-body">

        <div class="col-md-12">
          <?= $form->field($model, 'code'); ?>

          <?php if($pageCategory && $pageCategory->is_url_visible): ?>
            <?= $form->field($model, 'url'); ?>
          <?php endif; ?>

          <?= $form->field($model, 'sort'); ?>

          <?php if($pageCategory && $pageCategory->is_index_visible): ?>
            <?= $form->field($model, 'noindex')->checkbox(['value' => 0, 'uncheck' => 1]); ?>
          <?php endif; ?>

          <?= $form->field($model, 'is_disabled')->checkbox(['value' => 0, 'uncheck' => 1]); ?>
        </div>

      </div>
    </div>
  </div>
</div>
<hr/>
<div class="row">
  <div class="col-md-12">
    <?= Html::submitButton(($model->isNewRecord) ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save'), ['class' => 'btn btn-success pull-right']) ?>
    <?= Html::resetButton(Yii::_t('app.common.Reset'), ['class' => 'btn btn-danger']) ?>
  </div>
</div>

<?php ActiveForm::end(); ?>



