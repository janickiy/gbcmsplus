<?php

use mcms\common\widget\modal\Modal;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\common\form\AjaxActiveKartikForm;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \mcms\promo\models\SourceOperatorLanding $landingModel */
?>

<?php $form = AjaxActiveKartikForm::begin([
  'id' => 'add-landing-form',
  'enableAjaxValidation' => true,
  'action' =>  isset($update)
    ? [
      '/promo/webmaster-sources/update-landing/',
      'sourceId' => $landingModel->source_id,
      'key' => $key,
      'landingId' => $landingModel->landing_id,
      'operatorId' => $landingModel->operator_id,
      'profitType' => $landingModel->profit_type,
    ]
  : [
      '/promo/webmaster-sources/add-landing/',
      'sourceId' => $landingModel->source_id,
    ],
  'options' => [
    'data-key' => $key,
    'data-update' => isset($update) ? 'true' : 'false',
    'data-update-url' =>  Url::to([
      '/promo/webmaster-sources/update-landing/',
      'sourceId' => $landingModel->source_id,
    ])
  ],
  'ajaxSuccess' => Modal::ajaxSuccess('#webmaster-sources-landings-list')
]); ?>
<?= $form->field($landingModel, 'source_id', ['template' => '{input}', 'options' => ['class' => '']])->hiddenInput(); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">

  <?= Html::activeHiddenInput($landingModel, 'id') ?>

  <?= $form->field($landingModel, 'operator_id')->widget(OperatorsDropdown::class,
    [
      'pluginOptions' => [
        'placeholder' => Yii::_t('app.common.not_selected'),
        'allowClear' => true,
        'ajax' => [
          'url' => Url::to(['operators/select2']),
          'dataType' => 'json',
          'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
      ],
      'pluginEvents' => [
        'change' => 'function() { $("#' . Html::getInputId($landingModel, 'landing_id') . '").val("").change(); }'
      ],
      'useSelect2' => true,
    ]); ?>

    <?= $form->field($landingModel, 'landing_id')->widget(LandingsDropdown::class,
    [
      'pluginOptions' => [
        'placeholder' => Yii::_t('app.common.not_selected'),
        'allowClear' => true,
        'ajax' => [
          'url' => Url::to(['landings/select2', 'excludeSourceId' => $landingModel->source_id, 'category_id' => $landingModel->source->category_id]),
          'dataType' => 'json',
          'data' => new JsExpression('function(params) {
            var operatorId = $("#' . Html::getInputId($landingModel, 'operator_id') . '").val();
            return {
              operatorRequired: 0,
              q: params.term ? params.term : "",
              operators: operatorId ? [operatorId] : [],
            };
          }')
        ],
      ],
      'useSelect2' => true,
    ]);
    ?>

  <?= $form
    ->field($landingModel, 'profit_type')
    ->dropDownList(
      $landingModel::getProfitTypes(),
      ['class' => 'form-control input-sm', 'disabled' => !$landingModel->isAttributeSafe('profit_type')]
    ) ?>

  <?= $form->field($landingModel, 'source_id')->hiddenInput()->label(false) ?>
</div>

<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Save'),
        ['class' => 'btn btn-primary add-item', 'id' => 'add-landing-form-save']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveKartikForm::end(); ?>