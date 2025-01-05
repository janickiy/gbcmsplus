<?php
/** @var \yii\web\View $this */
/** @var \mcms\promo\models\PartnerProgramItem $model */
/** @var \mcms\payments\components\exchanger\CurrencyCourses $exchangeCourses */

use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;
use mcms\promo\components\widgets\AutoConvertWidget;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\promo\components\widgets\MultipleLandingsInsertWidget;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\PersonalProfit;
use yii\helpers\Url;
use yii\web\JsExpression;

(new AutoConvertWidget(
  $this,
  $exchangeCourses,
  'partnerprogramitemform-cpa_profit_rub',
  'partnerprogramitemform-cpa_profit_usd',
  'partnerprogramitemform-cpa_profit_eur'))
  ->run();
?>
<?php $form = AjaxActiveKartikForm::begin([
  'ajaxSuccess' => \mcms\common\widget\modal\Modal::ajaxSuccess('#partner-program-items-list')
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->field($model, 'operator_id')->widget(
      OperatorsDropdown::class,
      [
        'pluginEvents' => [
          'change' => 'function() { $("#' . Html::getInputId($model, 'landing_id') . '").val("").change(); }'
        ],
        'useSelect2' => true,
      ]
    ) ?>
    <?= $form->field($model, 'landing_id')->widget(
      LandingsDropdown::class,
      [
        'pluginOptions' => [
          'placeholder' => Yii::_t('app.common.not_selected'),
          'allowClear' => true,
          'ajax' => [
            'url' => Url::to(['landings/select2']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) {
            var operatorId = $("#' . Html::getInputId($model, 'operator_id') . '").val();
            return {
              operatorRequired: 0,
              q: params.term ? params.term : "",
              operators: operatorId ? [operatorId] : [],
            };
          }')
          ],
        ],
        'useSelect2' => true,
      ]
    ) ?>

    <?= MultipleLandingsInsertWidget::widget([
      'model' => $model,
      'form' => $form,
    ]) ?>

    <hr>

    <?= $form->field($model, 'rebill_percent') ?>
    <?= $form->field($model, 'buyout_percent') ?>

    <?php if (PersonalProfit::canManagePersonalCPAPrice()): ?>
      <div class="row">
        <div class="col-sm-4">
          <?= $form->field($model, 'cpa_profit_rub') ?>
        </div>
        <div class="col-sm-4">
          <?= $form->field($model, 'cpa_profit_usd') ?>
        </div>
        <div class="col-sm-4">
          <?= $form->field($model, 'cpa_profit_eur') ?>
        </div>
      </div>
    <?php endif; ?>
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
<?php AjaxActiveKartikForm::end() ?>