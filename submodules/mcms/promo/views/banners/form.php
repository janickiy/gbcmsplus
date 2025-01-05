<?php
use kartik\form\ActiveForm;
use kartik\builder\Form;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\promo\components\widgets\BannerValuesWidget;
use mcms\promo\models\Banner;
use yii\helpers\Html;
use mcms\common\helpers\Html as OurHtml;

\mcms\promo\assets\BannerPreviewAssets::register($this);

/** @var \mcms\promo\models\Banner $model */

$this->beginBlock('actions');
if (isset($this->blocks['list_button'])) {
  echo $this->blocks['list_button'];
}
$this->endBlock();
?>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title pull-left"><?=$this->title?></h3>
    <div class="clearfix"></div>
  </div>
  <div class="panel-body">
    <?php
    $form = ActiveForm::begin([
      'options' => [
        'class' => 'form-horizontal',
        'enctype' => 'multipart/form-data',
      ],
      'enableAjaxValidation' => true,
    ]);

    $attributes = [
      'name' => ['type' => Form::INPUT_TEXT],
    ];

    $template = $model->getTemplate()->one();

    ?>

    <div class="row">
      <div class="col-md-7">
        <div class="col-md-12">
          <?= $form->field($model, 'is_default')->checkbox(); ?>
        </div>
        <?= MultiLangForm::widget([
          'model' => $model,
          'form' => $form,
          'attributes' => $attributes,
        ]); ?>

        <div class="col-md-12">
          <?= $form->field($model, 'is_disabled')->checkbox(); ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'opacity'); ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'cross_position')->DropDownList([
            Banner::CROSS_LEFT_TOP => Yii::_t('banners.left-top'),
            Banner::CROSS_RIGHT_TOP => Yii::_t('banners.right-top'),
            Banner::CROSS_LEFT_BOTTOM => Yii::_t('banners.left-bottom'),
            Banner::CROSS_RIGHT_BOTTOM => Yii::_t('banners.right-bottom'),
          ]) ?>
        </div>
        <div class="col-md-12">
          <?= $form->field($model, 'timeout'); ?>
        </div>

        <h4 style="margin-top: 30px"><?= Yii::_t('banners.banner-props') ?>:</h4>
        <hr>
        <div class="col-md-12">
          <?= BannerValuesWidget::widget([
            'form' => $form,
            'banner' => $model,
          ]) ?>
        </div>
      </div>
    </div>
    <hr/>
    <div class="row">
      <div class="col-md-12">
        <div class="btn-group pull-right">

          <?php if(OurHtml::hasUrlAccess(['/promo/banners/form-preview/'])): ?>
          <?= Html::submitButton(
            Yii::_t('app.common.View') . ' [ru]',
            ['class' => 'btn btn-default preview', 'formaction' => $previewUrlRu]
          ); ?>

          <?= Html::submitButton(
            Yii::_t('app.common.View') . ' [en]',
            ['class' => 'btn btn-default preview', 'formaction' => $previewUrlEn]
          ); ?>
          <?php endif; ?>

          <?= Html::submitButton(
            ($model->isNewRecord) ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save'),
            ['class' => 'btn btn-success pull-right', 'id' => 'save']
          ) ?>

        </div>
        <?= Html::resetButton(Yii::_t('app.common.Reset'), ['class' => 'btn btn-danger']) ?>
      </div>
    </div>

    <?php ActiveForm::end(); ?>
  </div>
</div>
