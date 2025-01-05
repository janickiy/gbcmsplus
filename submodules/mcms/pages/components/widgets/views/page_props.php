<?php
use mcms\pages\models\Page;
use mcms\pages\components\widgets\PagePropInputWidget;
use mcms\pages\components\widgets\PagePropCheckboxWidget;
use mcms\pages\components\widgets\PagePropReferenceWidget;
use mcms\pages\components\widgets\PagePropFileWidget;
use mcms\pages\components\widgets\PagePropTextareaWidget;
use yii\widgets\Pjax;
use mcms\pages\components\widgets\PagePropsWidget;

/** @var $page Page */
/** @var $categoryProps \mcms\pages\models\CategoryProp[] */
/** @var $form \kartik\form\ActiveForm */

?>

<?php if($categoryProps && count($categoryProps) > 0): ?>
  <?php foreach($categoryProps as $categoryProp):
    /** @var \mcms\pages\models\CategoryProp $categoryProp */
    ?>
    <?php switch ($categoryProp->type) {
    case $categoryProp::TYPE_INPUT:
      echo PagePropInputWidget::widget([
        'form' => $form,
        'categoryProp' => $categoryProp,
        'page' => $page
      ]);
      break;
    case $categoryProp::TYPE_CHECKBOX:
      echo PagePropCheckboxWidget::widget([
        'form' => $form,
        'categoryProp' => $categoryProp,
        'page' => $page
      ]);
      break;
    case $categoryProp::TYPE_SELECT:
      echo PagePropReferenceWidget::widget([
        'form' => $form,
        'categoryProp' => $categoryProp,
        'page' => $page
      ]);
      break;
    case $categoryProp::TYPE_FILE:
      echo PagePropFileWidget::widget([
        'form' => $form,
        'categoryProp' => $categoryProp,
        'page' => $page
      ]);
      break;
    case $categoryProp::TYPE_TEXTAREA:
      echo PagePropTextareaWidget::widget([
        'form' => $form,
        'categoryProp' => $categoryProp,
        'page' => $page
      ]);
      break;
  }?>
    <hr>
  <?php endforeach ?>
<?php elseif ($categoryProps === false): ?>
  <code><?= Yii::_t('main.page-props-available_after_save') ?></code>
<?php else: ?>
  <code><?= Yii::_t('main.page-props-empty') ?></code>
<?php endif; ?>

