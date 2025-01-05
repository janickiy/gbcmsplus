<?php
use mcms\common\helpers\Html as McmsHtml;
use mcms\common\widget\modal\Modal;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\Module;
use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use yii\web\JsExpression;
use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\UserSelect2;

/** @var bool $isPersonal */
/** @var \mcms\promo\models\RebillCorrectConditions $model */
/** @var \mcms\user\Module $userModule */

?>

<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord
    ? ['/' . Module::getInstance()->id . '/rebill-conditions/create-modal', 'userId' => $model->partner_id]
    : ['/' . Module::getInstance()->id . '/rebill-conditions/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#rebill-conditions-pjax-block'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= Yii::_t('promo.personal-profits.modal-head') ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->errorSummary($model, ['class' => 'alert alert-danger']); ?>

    <?= $form->field($model, 'percent'); ?>

    <div class="well">
      <i><?= Yii::_t('promo.rebill-conditions.conditions-hint') ?>:</i>
      <hr>

      <?php if($isPersonal): ?>
        <?= $form->field($model, 'partner_id', ['options' => ['class' => '']])->hiddenInput()->label(false)->error(false); ?>
      <?php else: ?>
        <?= $form->field($model, 'partner_id')->widget(UserSelect2::class, [
          'initValueUserId' => $model->partner_id,
          'roles' => [$userModule::PARTNER_ROLE],
          'options' => [
            'placeholder' => Yii::_t('promo.rebill-conditions.set-partner-name')
          ],
        ])->error(false); ?>
      <?php endif; ?>

      <?= $form->field($model, 'operator_id')->widget(
        OperatorsDropdown::class,
        [
          'options' => [
            'prompt' => Yii::_t('app.common.not_selected'),
          ],
          'pluginOptions' => [
            'allowClear' => true,
          ],
          'pluginEvents' => [
            'change' => 'function() { $("#' . Html::getInputId($model, 'landing_id') . '").val("").change(); }'
          ],
          'useSelect2' => true,
        ]
      ) ?>

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
              return {
                operatorRequired: 0,
                q: params.term ? params.term : "",
                operators: operatorId ? [operatorId] : [],
              };
          }')
          ]
        ]
      ])->error(false); ?>
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