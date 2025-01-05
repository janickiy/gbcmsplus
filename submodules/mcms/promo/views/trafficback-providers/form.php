<?php
use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\common\widget\modal\Modal;

/**
 * @var \mcms\promo\models\TrafficbackProvider $model
 * @var string $postbackLink
 */
?>

<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#providers-trafficback-grid'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->errorSummary($model); ?>
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'url')->hint(Yii::_t('promo.trafficback_providers.url_hint')); ?>
    <?= $form->field($model, 'category_id')->dropDownList($model->getCategoriesMap(), ['prompt' => '']); ?>
    <?= $form->field($model, 'status')->dropDownList($model->getStatuses()); ?>

    <?php if ($postbackLink): ?>
      <div class="form-group">
        <label class="control-label"><?= Yii::_t('promo.trafficback_providers.link') ?></label>
        <input class="form-control" type="text" disabled value="<?= $postbackLink ?>"/>
      </div>
      <a data-toggle="collapse" href="#table"><?= Yii::_t('promo.trafficback_providers.possible_parameters') ?></a>

      <div id="table" class="collapse">
        <table class="table table-striped table-small table-bordered">
          <tr>
            <td>currency</td>
            <td><?= Yii::_t('promo.trafficback_providers.currency') ?> (rub, usd, eur)</td>
          </tr>
          <tr>
            <td>hit_id</td>
            <td><?= Yii::_t('promo.trafficback_providers.hit_id') ?></td>
          </tr>
          <tr>
            <td>sum</td>
            <td><?= Yii::_t('promo.trafficback_providers.sum') ?></td>
          </tr>
          <tr>
            <td>trans_id</td>
            <td><?= Yii::_t('promo.trafficback_providers.trans_id') ?></td>
          </tr>
        </table>
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
<?php AjaxActiveForm::end(); ?>