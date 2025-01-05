<?php

use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\promo\components\widgets\AutoConvertWidget;
use mcms\promo\components\widgets\CountriesDropdown;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\components\widgets\ProvidersDropdown;
use mcms\promo\models\Provider;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use yii\web\JsExpression;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\PersonalProfit;
use mcms\promo\assets\PersonalProfitFormAssets;

/** @var bool $isPersonal */
/** @var PersonalProfit $model */
/** @var string $getUserCurrencyLinkParams */
/** @var \mcms\payments\components\exchanger\CurrencyCourses $exchangeCourses */
PersonalProfitFormAssets::register($this);

(new AutoConvertWidget(
  $this,
  $exchangeCourses,
  'personalprofit-cpa_profit_rub',
  'personalprofit-cpa_profit_usd',
  'personalprofit-cpa_profit_eur'))
  ->run();
?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord
    ?
    ['/promo/personal-profits/create-modal', 'userId' => $model->user_id]
    :
    [
      '/promo/personal-profits/update-modal',
      'user_id' => $model->user_id,
      'landing_id' => $model->landing_id,
      'operator_id' => $model->operator_id,
      'country_id' => $model->country_id,
      'provider_id' => $model->provider_id,
    ],
  'ajaxSuccess' => Modal::ajaxSuccess('#personal-profit-pjax-block'),
  'options' => [
    'class' => 'personalProfitForm',
    'data' => [
      'user-currency-link' => Url::to($getUserCurrencyLinkParams)
    ]
  ]

]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= Yii::_t('promo.personal-profits.modal-head') ?></h4>
</div>

<div class="modal-body">
  <?= $form->errorSummary($model, ['class' => 'alert alert-danger']); ?>

  <?php if ($model->user_id != Yii::$app->user->id): ?>
    <?= $form->field($model, 'rebill_percent'); ?>
  <?php endif ?>
  <?= $form->field($model, 'buyout_percent'); ?>

  <?php if (PersonalProfit::canManagePersonalCPAPrice()): ?>
    <?= $form->field($model, 'cpa_profit_rub')->label(sprintf(
      '%s',
      $model->getAttributeLabel('cpa_profit_rub')
    )) ?>
    <?= $form->field($model, 'cpa_profit_usd')->label(sprintf(
      '%s',
      $model->getAttributeLabel('cpa_profit_usd')
    )) ?>
    <?= $form->field($model, 'cpa_profit_eur')->label(sprintf(
      '%s',
      $model->getAttributeLabel('cpa_profit_eur')
    )) ?>
  <?php else: ?>
    <?= $form->field($model, 'cpa_profit_rub')->label(false)->hiddenInput(); ?>
    <?= $form->field($model, 'cpa_profit_usd')->label(false)->hiddenInput(); ?>
    <?= $form->field($model, 'cpa_profit_eur')->label(false)->hiddenInput(); ?>
  <?php endif; ?>

  <div class="well">
    <i><?= Yii::_t('promo.personal-profits.conditions-hint') ?>:</i>
    <hr>

    <?= $form->field($model, 'country_id')->widget(
      CountriesDropdown::class,
      [
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
        ],
        'pluginEvents' => [
          'change' => 'function() { $("#' . Html::getInputId($model, 'operator_id') . '").val("").change(); }'
        ],
        'useSelect2' => true,
      ]
    ) ?>

    <?= $form->field($model, 'operator_id')->widget(
      OperatorsDropdown::class,
      [
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
        ],
        'pluginEvents' => [
          'change' => 'function() { $("#' . Html::getInputId($model, 'landing_id') . '").val("").change(); }'
        ],
        'pluginOptions' => [
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['operators/select2']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) {
            var countryId = $("#' . Html::getInputId($model, 'country_id') . '").val();
            return {
              operatorRequired: 0,
              q: params.term ? params.term : "",
              countriesIds: countryId ? [countryId] : [],
            };
          }')
          ]
        ],
        'useSelect2' => true,
      ]
    ) ?>

    <?= $form->field($model, 'provider_id')->widget(
      ProvidersDropdown::class,
      [
        'options' => [
          'prompt' => Yii::_t('app.common.not_selected'),
        ],
        'pluginEvents' => [
          'change' => 'function() { $("#' . Html::getInputId($model, 'landing_id') . '").val("").change(); }'
        ],
        'useSelect2' => true,
      ]
    ) ?>

    <?php if ($model->isNewRecord): ?>
      <?= $form->field($model, 'landingCategory')->widget('mcms\common\widget\Select2', [
        'options' => ['placeholder' => Yii::_t('app.common.not_selected')],
        'pluginOptions' => [
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['landing-categories/select2']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) {
            var operatorId = $("#' . Html::getInputId($model, 'operator_id') . '").val();
            var providerId = $("#' . Html::getInputId($model, 'provider_id') . '").val();
            var countryId = $("#' . Html::getInputId($model, 'country_id') . '").val();
            return {
              q: params.term ? params.term : "",
              operators: operatorId ? [operatorId] : [],
              countries: countryId ? [countryId] : "",
              provider_id: providerId ? providerId : "",
            };
          }')
          ]
        ]
      ]); ?>
    <?php endif ?>

    <?= $form->field($model, 'landing_id')->widget('mcms\common\widget\Select2', [
      'initValueText' => ArrayHelper::getValue($select2InitValues, 'landing_id'),
      'options' => ['placeholder' => Yii::_t('landings.enter_landing_name') . ':'],
      'pluginOptions' => [
        'allowClear' => true,
        'ajax' => [
          'url' => Url::to(['landings/select2']),
          'dataType' => 'json',
          'data' => new JsExpression('function(params) {
            var operatorId = $("#' . Html::getInputId($model, 'operator_id') . '").val();
            var providerId = $("#' . Html::getInputId($model, 'provider_id') . '").val();
            var countryId = $("#' . Html::getInputId($model, 'country_id') . '").val();
            var landingCategoryId = $("#' . Html::getInputId($model, 'landingCategory') . '").val();
            return {
              operatorRequired: 0,
              q: params.term ? params.term : "",
              operators: operatorId ? [operatorId] : [],
              countries: countryId ? [countryId] : "",
              provider_id: providerId ? providerId : "",
              category_id: landingCategoryId ? landingCategoryId : "",
            };
          }')
        ]
      ]
    ]); ?>

    <?php if ($isPersonal): ?>
      <?= $form->field($model, 'user_id')->hiddenInput(['class' => 'personalprofit-user_id'])->label(false)->error(false); ?>
    <?php else: ?>
      <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
        'options' => [
          'placeholder' => PersonalProfit::translate('attribute-user_id'),
          'class' => 'personalprofit-user_id',
        ],
        'initValueUserId' => $model->user_id,
        'ignoreIds' => $ignoreIds,
      ]); ?>
    <?php endif; ?>
    <?php $this->registerJs('
      $("#'. $form->id .'").personalProfitForm();
    '); ?>
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


