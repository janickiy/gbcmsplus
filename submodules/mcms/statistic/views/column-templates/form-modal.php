<?php
use mcms\common\form\AjaxActiveForm;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\statistic\assets\ColumnsTemplateAsset;
use yii\bootstrap\Html as BHtml;
use yii\helpers\Html;

/** @var \mcms\statistic\models\ColumnsTemplate $model */

ColumnsTemplateAsset::register($this);
$this->registerJs(/** @lang JavaScript */
  'ColumnsTemplateForm.generateColumnsFields();');
?>

<?php $form = AjaxActiveForm::begin([
  'id' => 'columns-template-form',
  'action' => $model->isNewRecord ? ['/statistic/column-templates/create'] : ['/statistic/column-templates/update', 'id' => $model->id],
  'ajaxSuccess' => Modal::ajaxSuccess(null, /** @lang JavaScript */'updateColumnsSelector(' . $model->id . ')'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $model->name ? : Yii::_t('statistic.statistic.columns_templates_create') ?></h4>
  </div>

  <div class="modal-body columns-template-modal">
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'columns')->hiddenInput(['id' => 'template-columns'])->label(false) ?>
    <div class="form-group">
        <p>
        <a href="#" onclick="ColumnsTemplateForm.selectAllColumns(); return false;"><?= Yii::_t('statistic.statistic.select_all_columns') ?></a>
        / <a href="#" onclick="ColumnsTemplateForm.unselectAllColumns(); return false;"><?= Yii::_t('statistic.statistic.unselect_all_columns_low') ?></a>
        </p>
      <div class="columns-template-columns" data-columns="<?= Html::encode($model->columns) ?>"></div>
    </div>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?php if (!$model->isNewRecord) { ?>
          <?= Html::a(
            BHtml::icon('trash') . ' ' . Yii::t('yii', 'Delete'),
            ['/statistic/column-templates/delete', 'id' => $model->id],
            [
              AjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
              AjaxButtons::RELOAD_ATTRIBUTE => 0,
              AjaxButtons::SUCCESS_ATTRIBUTE => Modal::ajaxSuccess(null, /** @lang JavaScript */'updateColumnsSelector()'),
              'data-pjax' => 0,
              'class' => 'btn btn-default'
            ]
          ) ?>
        <?php } ?>
        <?= Html::submitButton(
          '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
          ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
      </div>
    </div>
  </div>
<?php AjaxActiveForm::end(); ?>