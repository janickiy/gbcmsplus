<?php

use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\SubscriptionCorrectCondition;
use rgk\utils\widgets\modal\Modal;

/**
 * @var \yii\web\View $this
 * @var SubscriptionCorrectCondition $model
 */

$this->title = $model->isNewRecord
  ? Yii::_t('promo.correct-conditions.create')
  : Yii::_t('promo.correct-conditions.update')
  ;
?>
<?php $form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#conditionsPjax'),
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->field($model, 'name') ?>
    <?= $form->field($model, 'operator_id')->widget(
      OperatorsDropdown::class,
      [
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ],
        'useSelect2' => true,
      ]
    ) ?>
    <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
      'model' => $model,
      'attribute' => 'user_id',
      'initValueUserId' => $model->user_id,
      'roles' => ['partner'],
      'options' => [
        'placeholder' => '',
      ],
    ]) ?>
    <?= $form->field($model, 'landing_id')->widget(
      LandingsDropdown::class,
      [
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
        ],
        'pluginOptions' => [
          'allowClear' => true,
        ],
        'useSelect2' => true,
      ]
    ) ?>
    <?= $form->field($model, 'percent') ?>
    <?= $form->field($model, 'is_active')->checkbox() ?>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton('<i class="fa fa-save"></i> ' . Yii::_t('app.common.Save'), ['class' => 'pull-right btn btn-success']) ?>
      </div>
    </div>
  </div>
<?php AjaxActiveKartikForm::end() ?>