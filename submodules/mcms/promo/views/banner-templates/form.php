<?php

use mcms\common\grid\ContentViewPanel;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\form\AjaxActiveKartikForm;
use yii\web\JsExpression;
use conquer\codemirror\CodemirrorWidget;
use mcms\promo\models\BannerTemplate;
/* @var $this yii\web\View */
/* @var $model mcms\promo\models\BannerTemplate */
/* @var $form yii\widgets\ActiveForm */
/* @var $attributesDataProvider \yii\data\ActiveDataProvider */
?>

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title pull-left"><?=$this->title?></h3>
    <div class="clearfix"></div>
  </div>
  <div class="panel-body">
    <div class="category-form">

  <?php $form = AjaxActiveKartikForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'options' => ['class' => 'clearfix'],
    'ajaxSuccess' => new JsExpression('function(response){
      $.pjax.reload({container: "#templateAttributesContainer"});
    }'),
  ]); ?>

    <div class="col-lg-12">

      <?= $form->field($model, 'name')->widget(InputWidget::class, [
        'class' => 'form-control',
        'form' => $form
      ]) ?>

      <?= $form->field($model, 'code') ?>

      <?= $form->field($model, 'display_type')->DropDownList(BannerTemplate::getDisplayTypeDropdownItems()) ?>

      <?= $form->field($model, 'template')
        ->widget(
          CodemirrorWidget::class,
          [
            'preset'=>'php',
            'options'=>['rows' => 20],
          ]
        )
        ->hint('<ul>
            <li>' . Yii::_t('banner-templates.tag_usage', ['code' => '<code>{button_text}</code>']) . '</li>
            <li>' . Yii::_t('banner-templates.tag_return_url', ['code' => '<code>{returnUrl}</code>']) . '</li>
          </ul>');
      ?>

      <hr>
      <div class="form-group clearfix">
        <?= Html::submitButton(
          '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
          ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
      </div>


    </div>

  <?php AjaxActiveKartikForm::end(); ?>

      </div>
    </div>
</div>
<div class="clearfix"></div>

<?= $this->render('_attributes', ['model' => $model, 'form' => $form, 'attributesDataProvider' => $attributesDataProvider])?>
