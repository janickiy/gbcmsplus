<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;

?>


<?php $form = AjaxActiveKartikForm::begin([
  'action' => $model->isNewRecord ? ['/promo/ads-networks/create'] : ['/promo/ads-networks/update', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess('#adsNetworksGrid'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= MultiLangForm::widget([
      'model' => $model,
      'form' => $form,
      'attributes' => [
        'description1' => ['type' => \kartik\builder\Form::INPUT_TEXT],
        'description2' => ['type' => \kartik\builder\Form::INPUT_TEXT],
      ]
    ]); ?>
    <div class="well">
      <?= $form->field($model, 'name') ?>
      <?= $form->field($model, 'label1') ?>
      <?= $form->field($model, 'label2') ?>
      <?= $form->field($model, 'status')->dropDownList($model->getStatuses()) ?>
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
<?php AjaxActiveKartikForm::end(); ?>