<?php
use mcms\pages\components\widgets\PagePropsWidget;
use mcms\common\helpers\ArrayHelper;
/** @var $form \kartik\form\ActiveForm */
/** @var $pageProp \mcms\pages\models\PageProp */
/** @var $categoryProp \mcms\pages\models\CategoryProp */


?>


<p class="row">
  <?= $categoryProp->name?>:
</p>

<?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']entities')
  ->label(false)->dropDownList(
    ArrayHelper::map($categoryProp->getPropEntities()->all(), 'id', 'label'),
    [
      'prompt' => '',
      'multiple' => !!$categoryProp->is_multivalue
    ]
  ); ?>

<?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']page_category_prop_id', [
  'template' => '{input}',
  'options' => ['class' => '']
])->hiddenInput() ?>

<?php PagePropsWidget::$counter++; ?>







