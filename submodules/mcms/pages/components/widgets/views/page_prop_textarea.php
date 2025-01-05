<?php
use mcms\common\multilang\widgets\input\TextareaWidget;
use mcms\pages\components\widgets\PropDynamicFormWidget;
use mcms\pages\components\widgets\PagePropsWidget;

/** @var $form \kartik\form\ActiveForm */
/** @var $pageProps \mcms\pages\models\PageProp[] */
/** @var $categoryProp \mcms\pages\models\CategoryProp */

$widgetContainer = 'dynamicform_wrapper_' . $categoryProp->id;
$widgetBody = 'container-items_' . $categoryProp->id;
$widgetItem = 'item_' . $categoryProp->id;
$insertButton = 'add-item_' . $categoryProp->id;
$deleteButton = 'remove-item_' . $categoryProp->id;
$categoryPropField = 'page-category-prop-id';
?>

<?php PropDynamicFormWidget::begin([
  'widgetContainer' => $widgetContainer,
  'widgetBody' => '.' . $widgetBody,
  'widgetItem' => '.' . $widgetItem,
  'insertButton' => '.' . $insertButton,
  'deleteButton' => '.' . $deleteButton,
  'min' => $categoryProp->is_multivalue ? 0 : 1 ,
  'model' => $pageProps[0],
  'formId' => $form->getId(),
  'formFields' => ['multilang_value', 'id', 'page_category_prop_id'],
  'categoryPropId' => $categoryProp->id,
  'categoryPropField' => '.' . $categoryPropField
]); ?>

<div class="<?= $widgetBody ?>">
  <p class="row">
    <?= $categoryProp->name?>:
    <?php if($categoryProp->is_multivalue): ?>
      <button type="button" class="<?= $insertButton ?> btn btn-success btn-xs pull-right">
        <i class="glyphicon glyphicon-plus"></i> <?= Yii::_t('app.common.Add')?>
      </button>
    <?php endif; ?>

  </p>
  <?php foreach($pageProps as $pageProp): ?>
    <div class="<?= $widgetItem ?>">
      <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']multilang_value')
        ->widget(TextareaWidget::class, [
          'class' => 'form-control',
          'form' => $form,
          'prepend' =>
            $categoryProp->is_multivalue
              ? '<div class="input-group-btn btn-group"><button type="button" class="' . $deleteButton . ' btn btn-default pull-left">
                  <i class="glyphicon glyphicon-trash"></i>
                </button></div>'
              : ''
        ])->label(false) ?>

      <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']page_category_prop_id', [
        'template' => '{input}',
        'options' => ['class' => '']
      ])->hiddenInput(['class' => $categoryPropField]) ?>

      <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']id', [
        'template' => '{input}',
        'options' => ['class' => '']
      ])->hiddenInput() ?>
    </div>
    <?php PagePropsWidget::$counter++; ?>
  <?php endforeach ?>




</div>

<?php PropDynamicFormWidget::end(); ?>






