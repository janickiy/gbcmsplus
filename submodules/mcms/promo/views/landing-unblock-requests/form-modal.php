<?php

use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\ProvidersDropdown;
use mcms\promo\models\PersonalProfit;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use yii\web\JsExpression;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\assets\LandingUnblockRequestFormAsset;
use mcms\promo\models\UnblockRequest;

LandingUnblockRequestFormAsset::register($this);

$id = 'landing-unblock-requests';
/* @var LandingUnblockRequest|UnblockRequest $model */
if ($model instanceof UnblockRequest) {
  $this->registerJs(<<<JS
    var landing = $('#unblockrequest-landing_id');
    var operator = $('#unblockrequest-operatorid');
    var provider = $('#unblockrequest-providerid');

    function refreshLanding() {
      if (landing.val() !== '') {
        operator.attr('disabled', 'disabled').trigger("change");
        provider.attr('disabled', 'disabled').trigger("change");
      } else {
        operator.removeAttr('disabled');
        provider.removeAttr('disabled');
      }
    }
    
    function refreshOperator() {
      if (operator.val() !== '') {
        landing.attr('disabled', 'disabled').trigger("change");
      } else {
        landing.removeAttr('disabled');
      }
    }
    
    function refreshProvider() {
      if (provider.val() !== '') {
        landing.attr('disabled', 'disabled').trigger("change");
      } else {
        landing.removeAttr('disabled');
      }
    }
    
    landing.on('change', refreshLanding);
    operator.on('change', refreshOperator);
    provider.on('change', refreshProvider);
JS
  );
}
?>


<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord ? ['/promo/' . $id . '/create-modal'] : ['/promo/' . $id . '/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#' . $id . 'PjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->isNewRecord
      ? Yii::_t('promo.landing_unblock_requests.create')
      : Yii::_t('promo.landing_unblock_requests.update'); ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
    'options' => [
      'placeholder' => PersonalProfit::translate('attribute-user_id')
    ],
    'initValueUserId' => $model->user_id,
  ]); ?>

  <?= $form->field($model, 'landing_id')->widget('mcms\common\widget\Select2', [
    'initValueText' => ArrayHelper::getValue($select2InitValues, 'landing_id'),
    'options' => ['placeholder' => Yii::_t('landings.enter_landing_name') . ':'],
    'pluginOptions' => [
      'allowClear' => true,
      'ajax' => [
        'url' => Url::to(['landings/select2']),
        'dataType' => 'json',
        'data' => new JsExpression('function(params) { return {q:params.term}; }')
      ]
    ]
  ]) ?>

  <?= $model instanceof UnblockRequest ? $form->field($model, 'operatorId')->widget(
    OperatorsDropdown::class,
    [
      'options' => [
        'prompt' => Yii::_t('app.common.not_selected'),
      ],
      'useSelect2' => true,
    ]
  ) : '' ?>

  <?= $model instanceof UnblockRequest ? $form->field($model, 'providerId')->widget(
    ProvidersDropdown::class,
    [
      'options' => [
        'prompt' => Yii::_t('app.common.not_selected'),
      ],
      'useSelect2' => true,
    ]
  ) : '' ?>

  <?= $form->field($model, 'description')->textarea(); ?>

  <?= $form->field($model, 'traffic_type')->widget('mcms\common\widget\Select2', [
    'data' => $model->trafficTypes,
    'options' => ['placeholder' => Yii::_t('app.common.choose')],
    'pluginOptions' => [
      'multiple' => true,
      'tags' => true,
    ]
  ]) ?>

  <?= $form->field($model, 'status')->dropDownList($model->statuses, ['prompt' => Yii::_t('app.common.not_selected')]); ?>

  <div id="landing-unblock-request-reject-reason" class="<?= $model->isDisabled() ? '' : 'hide'?>"
       data-status-disabled="<?= LandingUnblockRequest::STATUS_DISABLED; ?>">
    <?= $form->field($model, 'reject_reason')->textarea(); ?>
  </div>
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
<?php AjaxActiveForm::end(); ?>


