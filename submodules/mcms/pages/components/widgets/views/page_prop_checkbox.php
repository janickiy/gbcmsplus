<?php
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\pages\components\widgets\PagePropsWidget;
/** @var $form \kartik\form\ActiveForm */
/** @var $pageProps \mcms\pages\models\PageProp[] */
/** @var $categoryProp \mcms\pages\models\CategoryProp */


?>


<?php foreach($pageProps as $pageProp): ?>

  <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']value')->checkbox([
    'label' => $categoryProp->name
  ])->label(false) ?>

  <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']page_category_prop_id', [
    'template' => '{input}',
    'options' => ['class' => '']
  ])->hiddenInput() ?>

  <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']id', [
    'template' => '{input}',
    'options' => ['class' => '']
  ])->hiddenInput() ?>

<?php PagePropsWidget::$counter++; ?>

<?php endforeach ?>







