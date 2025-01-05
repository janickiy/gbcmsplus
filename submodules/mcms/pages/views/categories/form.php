<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\form\AjaxActiveKartikForm;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model mcms\pages\models\Category */
/* @var $form yii\widgets\ActiveForm */
/* @var $propsDataProvider \yii\data\ActiveDataProvider */
?>

<div class="category-form">

  <?php $form = AjaxActiveKartikForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'ajaxSuccess' => new JsExpression('function(response){
      $.pjax.reload({container: "#categoryPropsContainer"});
    }'),
  ]); ?>

  <div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title pull-left"><?=$this->title?></h3>
    <div class="clearfix"></div>
  </div>
  <div class="panel-body">
    <div class="col-lg-6">
      <?= $form->field($model, 'name')->widget(InputWidget::class, [
        'class' => 'form-control',
        'form' => $form
      ]) ?>

      <?= $form->field($model, 'code') ?>

      <?= $form->field($model, 'is_seo_visible')->checkbox(); ?>
      <?= $form->field($model, 'is_url_visible')->checkbox(); ?>
      <?= $form->field($model, 'is_index_visible')->checkbox(); ?>

      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <?= Html::submitButton(
            '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
          ) ?>
        </div>
      </div>
    </div>
  </div>
    </div>

  <?php AjaxActiveKartikForm::end(); ?>

</div>

<div class="clearfix"></div>

<?php if (!$model->isNewRecord): ?>
  <?= $this->render('_props', ['model' => $model, 'form' => $form, 'propsDataProvider' => $propsDataProvider]) ?>
<?php endif; ?>
