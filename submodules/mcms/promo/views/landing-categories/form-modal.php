<?php

use kartik\sortinput\SortableInput;
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\ArrayHelper;
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\promo\models\AdsType;
use mcms\common\widget\modal\Modal;
use yii\helpers\Html;
use yii\web\JsExpression;
use mcms\promo\components\widgets\BannerPicker;

$id = 'landing-categories';

/** @var \mcms\promo\models\LandingCategory $model */

?>

<?php $form = AjaxActiveKartikForm::begin([
  'action' => $model->isNewRecord ? ['/promo/' . $id . '/create-modal'] : ['/promo/' . $id . '/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#' . $id . 'PjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ?: Yii::_t('promo.landing_categories.create') ?></h4>
</div>

<div class="modal-body">
  <?= $model->canEditAttribute('name') ? MultiLangForm::widget([
    'model' => $model,
    'form' => $form,
    'attributes' => [
      'name' => [
        'type' => \kartik\builder\Form::INPUT_TEXT
      ],
    ]
  ]) : '' ?>
  <?= $form->field($model, 'code', ['inputOptions' => ['disabled' => !$model->isNewRecord]]) ?>

  <?= $form->field($model, 'status', ['inputOptions' => ['disabled' => !$model->canEditAttribute('status')]])->dropDownList($model->statuses); ?>

  <?= $form->field($model, 'is_not_mainstream')->checkbox(['disabled' => !$model->canEditAttribute('is_not_mainstream')]); ?>

  <?= $form->field($model, 'alter_categories')->widget(SortableInput::class, [
    'items' => ArrayHelper::map($model->getAlterCategories(), 'code', function ($category) use ($model) {
      return [
        'content' => $category->name . '<button type="button" class="close remove-category"><span>×</span></button>',
        'disabled' => !$model->canEditAttribute('alter_categories')
      ];
    }),
  ]) ?>

  <div class="form-group">
    <div class="input-group">
      <?= Html::dropDownList('alterCategorySelect', null, $model::getDropdownItems($model->code), ['disabled' => !$model->canEditAttribute('alter_categories'), 'class' => 'form-control', 'id' => 'alterCategorySelect']) ?>
      <div class="input-group-btn">
        <?= Html::button(Yii::_t('landing_categories.add-category'), ['disabled' => !$model->canEditAttribute('alter_categories'), 'class' => 'btn btn-default', 'id' => 'alterCategoryAdd']) ?>
      </div>
    </div>
  </div>

  <?= $form->field($model, 'tb_url', ['inputOptions' => ['disabled' => !$model::canEditAttribute('tb_url')]]); ?>

  <?php if (Yii::$app->user->can('CanEditLandingCategoryBannersIds')): ?>
  <?= $form->field($model, 'bannersIds')
    ->widget(BannerPicker::class)
    ->label(false);
  ?>
  <?php endif ?>

  <?php if (AdsType::isClickNConfirmAvailable() && $model->canEditAttribute('click_n_confirm_text')): ?>
    <?= Html::activeLabel($model, 'click_n_confirm_text')?>
    <?= InputWidget::widget([
      'model' => $model,
      'form' => $form,
      'attribute' => 'click_n_confirm_text'
    ]) ?>
  <?php endif ?>
</div>

<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
        ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
      ) ?>
    </div>
  </div>
</div>

<?php $this->registerJs('$(document).trigger("modal.landing_category.show")'); ?>

<?php AjaxActiveKartikForm::end(); ?>


