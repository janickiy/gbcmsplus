<?php

use mcms\promo\models\LandingOperator;
use wbraganca\dynamicform\DynamicFormWidget;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\assets\LandingUpdateAsset;

/** @var bool $showDaysHold */
/** @var int $onetimeId */
LandingUpdateAsset::register($this);
$formatter = Yii::$app->formatter;

$js = <<<JS
function checkSubscriptionType(\$subscriptionTypeSelect) {
  var isOnetime = false;
  if (\$subscriptionTypeSelect.val() === '$onetimeId') {
    isOnetime = true;
  }
  \$buyoutPriceBlocks = \$subscriptionTypeSelect.closest('.item').find('.buyout-prices');
  isOnetime ? \$buyoutPriceBlocks.hide() : \$buyoutPriceBlocks.show();
}

$('.subscription_type_id').each(function(){
  checkSubscriptionType($(this));
});

$(document).on('change', '.subscription_type_id', function(e){
  checkSubscriptionType($(e.target));
});
JS;

$this->registerJs($js);

?>

<?php DynamicFormWidget::begin([
  'widgetContainer' => 'dynamicform_wrapper',
  'widgetBody' => '.container-items',
  'widgetItem' => '.item',
  'insertButton' => '.add-item',
  'deleteButton' => '.remove-item',
  'min' => 1,
  'model' => $model->operatorModels[0],
  'formId' => 'landing-form',
  'formFields' => ['default_currency_id'],
]); ?>

  <div class="panel panel-info">
    <div class="panel-heading">
      <h3 class="panel-title pull-left"><?= Yii::_t('promo.landings.operator-list') ?>:</h3>

      <div class="pull-right">
        <button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="container-items panel-body">
      <?php foreach ($model->operatorModels as $key => $operatorModel): ?>
        <?php /** @var LandingOperator $operatorModel */
          $prices = $operatorModel->getCompletePrices();
        ?>
        <div class="item well">

          <?php if (!$operatorModel->isNewRecord): ?>
            <?= $form->field($operatorModel, '[' . $key . ']' . 'landing_id', ['enableClientValidation' => false])->hiddenInput()->label(false) ?>
          <?php endif ?>
          <div class="row">
            <div class="col-sm-3">
              <?= $form->field($operatorModel, '[' . $key . ']' . 'operator_id')->widget(OperatorsDropdown::class, [
                'options' => [
                  'prompt' => Yii::_t('app.common.not_selected'),
                ]
              ]); ?>
            </div>
            <div class="col-sm-3">
              <?= $form->field($operatorModel, '[' . $key . ']' . 'subscription_type_id')->dropDownList($subscriptionTypes, ['prompt' => Yii::_t('app.common.not_selected'), 'class' => 'form-control subscription_type_id']) ?>
            </div>
            <?php if ($showDaysHold): ?>
            <div class="col-sm-2">
              <?= $form->field($operatorModel, '[' . $key . ']' . 'days_hold') ?>
            </div>
            <?php endif;?>
            <div class="col-sm-4">
              <?= $form->field($operatorModel, '[' . $key . ']' . 'cost_price') ?>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-3">
              <?= $form->field($operatorModel, '[' . $key . ']' . 'local_currency_id')->dropDownList($operatorModel->currencies, ['prompt' => Yii::_t('app.common.not_selected')]) ?>
            </div>
            <div class="col-sm-3 buyout-prices">
              <?= !$operatorModel->isNewRecord && $operatorModel->buyout_price_rub == 0
                ? $form->field($operatorModel, '[' . $key . ']' . 'buyout_price_rub')
                  ->hint($formatter->asDecimal($prices->getBuyoutPrice('rub')))
                : $form->field($operatorModel, '[' . $key . ']' . 'buyout_price_rub'); ?>
            </div>
            <div class="col-sm-3 buyout-prices">
              <?= !$operatorModel->isNewRecord && $operatorModel->buyout_price_usd == 0
                ? $form->field($operatorModel, '[' . $key . ']' . 'buyout_price_usd')->hint($formatter->asDecimal($prices->getBuyoutPrice('usd')))
                : $form->field($operatorModel, '[' . $key . ']' . 'buyout_price_usd'); ?>
            </div>
            <div class="col-sm-3 buyout-prices">
              <?= !$operatorModel->isNewRecord && $operatorModel->buyout_price_eur == 0
                ? $form->field($operatorModel, '[' . $key . ']' . 'buyout_price_eur')->hint($formatter->asDecimal($prices->getBuyoutPrice('eur')))
                : $form->field($operatorModel, '[' . $key . ']' . 'buyout_price_eur') ?>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-3">
              <?= $form->field($operatorModel, '[' . $key . ']' . 'local_currency_rebill_price') ?>
            </div>
            <div class="col-sm-3">
              <?= !$operatorModel->isNewRecord && $operatorModel->rebill_price_rub == 0
                ? $form->field($operatorModel, '[' . $key . ']' . 'rebill_price_rub')->hint($formatter->asDecimal($prices->getRebillPrice('rub')))
                : $form->field($operatorModel, '[' . $key . ']' . 'rebill_price_rub'); ?>
            </div>
            <div class="col-sm-3">
              <?= !$operatorModel->isNewRecord && $operatorModel->rebill_price_usd == 0
                ? $form->field($operatorModel, '[' . $key . ']' . 'rebill_price_usd')->hint($formatter->asDecimal($prices->getRebillPrice('usd')))
                : $form->field($operatorModel, '[' . $key . ']' . 'rebill_price_usd'); ?>
            </div>
            <div class="col-sm-3">
              <?= !$operatorModel->isNewRecord && $operatorModel->rebill_price_eur == 0
                ? $form->field($operatorModel, '[' . $key . ']' . 'rebill_price_eur')->hint($formatter->asDecimal($prices->getRebillPrice('eur')))
                : $form->field($operatorModel, '[' . $key . ']' . 'rebill_price_eur'); ?>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-3">
            <?php echo $form->field($operatorModel, '[' . $key . ']' . 'payTypeIds')->dropDownList($payTypes, [
              'multiple' => true,
              'class' => 'form-control selectpicker operators-selectpicker',
              'data-width' => '100%',
              'data-live-search' => 'true',
              'data-none-selected-text' => Yii::_t('app.common.not_selected'),
              'style' => 'width:100%',
            ]);?>
            </div>
            <div class="col-sm-3">
              <label></label>
              <?= $form->field($operatorModel, '[' . $key . ']' . 'use_landing_operator_rebill_price')->checkbox() ?>
            </div>
            <div class="col-sm-3">
              <label></label>
              <?= $form->field($operatorModel, '[' . $key . ']' . 'is_deleted')->checkbox() ?>
            </div>
            <div class="col-sm-3">
              <label>&nbsp;</label>
              <div class="clearfix">
              <?= \rgk\utils\widgets\AjaxRequest::widget([
                'title' => 'Delete',
                'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'url' => [
                  'landing-operator/delete',
                  'landingId' => $operatorModel->landing_id,
                  'operatorId' => $operatorModel->operator_id
                ],
                'options' => [
                  'class' => 'btn btn-danger pull-right delete-button',
                  'data-id' => $operatorModel->operator_id,
                ],
                'successCallback' => 'function () {
                  $(this).closest(".item").remove();
                }',
              ]) ?>
            </div>
            </div>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>

<?php DynamicFormWidget::end(); ?>
