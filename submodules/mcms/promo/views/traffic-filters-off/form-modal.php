<?php
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\UserOperatorTrafficFiltersOff;
use rgk\utils\widgets\form\AjaxActiveForm;

/** @var UserOperatorTrafficFiltersOff $model */
?>


<?php $form = AjaxActiveForm::begin([
  'action' => $model->isNewRecord
    ? ['/promo/traffic-filters-off/create-modal', 'userId' => $model->user_id]
    : ['/promo/traffic-filters-off/update-modal', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#TrafficFiltersOffGrid'),
  'forceResultMessages' => true,
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <div class="row">
      <div class="col-sm-12">
        <?= $form->field($model, 'user_id')->hiddenInput()->label(false);?>
        <div class="well">
          <?= $form->field($model, 'operator_id')->widget(
            OperatorsDropdown::class,
            [
              'options' => [
                'prompt' => Yii::_t('app.common.not_selected'),
              ],
              'useSelect2' => true,
            ]
          ) ?>
        </div>
      </div>
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