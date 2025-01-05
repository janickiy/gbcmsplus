<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\pages\models\PartnerCabinetStyleCategory;

/** @var PartnerCabinetStyleCategory $category */
/** @var AjaxActiveKartikForm $form */
/** @var integer $styleId */
?>

<?php foreach ($category->fields as $i => $field): ?>
    <?= $form->field($field->value, "[{$category->id}][$i]id")->hiddenInput()->label(false) ?>
    <?= Html::hiddenInput(Html::getInputName($field->value, "[{$category->id}][$i]field_id"), $field->id) ?>
    <?= Html::hiddenInput(Html::getInputName($field->value, "[{$category->id}][$i]style_id"), $styleId) ?>
  <div class="form-group">
    <label><?= $field->name ?>:</label>
    <?= Html::activeTextInput($field->value, "[{$category->id}][$i]value", ['class'=>'form-control input-sm', 'placeholder' => $field->default_value])?>
    <div class="text-muted">
      <small>
        <?php if (Yii::$app->user->can('PagesPartnerCabinetStylesUpdateFieldModal')): ?>
          <?= $field->getAttributeLabel('sort_css') ?>: <?= $field->sort_css ?> |
          <?= $field->css_selector ?>[<?= $field->css_prop ?>]
          <span class=pull-right>
            <?= Modal::widget([
              'url' => ['update-field-modal', 'id' => $field->id],
              'size' => Modal::SIZE_LG,
              'toggleButtonOptions' => [
                'tag' => 'a',
                'label' => mb_strtolower(Yii::_t('commonMsg.main.update_short')),
                'title' => Yii::t('yii', 'Update'),
                'data-pjax' => 0,
              ],
            ]); ?>&nbsp;&nbsp;
            <?= Html::a(
              mb_strtolower(Yii::_t('commonMsg.main.delete_short')),
              ['delete-field', 'id' => $field->id],
              [
                'class' => 'text-danger',
                'data-pjax' => 0,
                AjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
                AjaxButtons::AJAX_ATTRIBUTE => 1
              ]) ?>
          </span>
        <?php endif; ?>
      </small>

    </div>


  </div>

<?php endforeach; ?>