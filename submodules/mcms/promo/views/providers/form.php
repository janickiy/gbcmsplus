<?php
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\AbstractProviderSettings;
use mcms\promo\models\Provider;
use yii\widgets\Pjax;

/** @var Provider $model Провайдер */
/** @var AbstractProviderSettings|null $settings Настройки провайдера */
/** @var string $formUrl Относительный адрес формы */
/** @var \yii\web\View $this */

// Подгрузка набора настроек при изменении класса-хэндлера
$this->registerJs(<<<JS
$('#provider-form-pjax').on('change', '#provider-handler_class_name', function () {
  $.pjax.reload("#provider-form-pjax", {
    type: "post",
    url: "$formUrl",
    data: $("#provider-form").serialize(),
    push: false,
    replace: false
  });
});
JS
);
?>

<?php Pjax::begin([
  'id' => 'provider-form-pjax',
  'enablePushState' => false,
  'enableReplaceState' => false,
]) ?>
<?php $form = AjaxActiveForm::begin([
  'id' => 'provider-form',
  'ajaxSuccess' => Modal::ajaxSuccess('#providers-info'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'handler_class_name')->dropDownList(Provider::getHandlerItems(), ['prompt' => Yii::_t('app.common.choose')]); ?>
    <?php if ($settings) { ?>
      <div class="well">
        <?= $this->render('provider_settings/' . $settings->getViewName(), [
          'model' => $settings,
          'form' => $form,
        ])?>
      </div>
    <?php } ?>
    <?= $form->field($model, 'code'); ?>
    <?= $form->field($model, 'url'); ?>
    <?= $form->field($model, 'status')->dropDownList($model->getStatuses()); ?>
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
<?php Pjax::end() ?>