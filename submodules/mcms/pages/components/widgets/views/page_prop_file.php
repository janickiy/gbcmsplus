<?php
use mcms\pages\components\widgets\PagePropsWidget;
use mcms\common\multilang\widgets\input\FileInputWidget;

/** @var $form \kartik\form\ActiveForm */
/** @var $pageProp \mcms\pages\models\PageProp */
/** @var $categoryProp \mcms\pages\models\CategoryProp */
/** @var $previews array */
/** @var $imagesDelete array */


?>


<p class="row">
  <?= $categoryProp->name?>:
</p>
<div class="row" style="margin-bottom: 25px;">

  <?= FileInputWidget::widget([
    'model' => $pageProp,
    'previews' => $previews,
    'imagesDelete' => $imagesDelete,
    'attribute' => '[' . PagePropsWidget::$counter . ']file[{lang}][]',
    'options' => ['multiple' => !!$categoryProp->is_multivalue],
    'pluginOptions' => [
      'showUpload' => false,
      'overwriteInitial' => false,
    ],
    'pluginEvents' => [
      'filepredelete' => 'function(event, key, xhr) {
        if (confirm("' . Yii::t('yii', 'Are you sure you want to delete this item?') . '")) return;
        xhr.abort();
        setTimeout(function(){
            $(".kv-file-remove[data-key=\"" + key + "\"]")
              .removeClass("disabled")
              .closest(".file-preview-frame").removeClass("file-uploading");
        }, 100);
      }' // Мега костыльный вариант реализации confirm-а для удаления. Иначе никак для этого плагина :(
    ]
  ])?>


  <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']id', [
    'template' => '{input}',
    'options' => ['class' => '']
  ])->hiddenInput() ?>

  <?= $form->field($pageProp, '[' . PagePropsWidget::$counter . ']page_category_prop_id', [
    'template' => '{input}',
    'options' => ['class' => '']
  ])->hiddenInput() ?>

  <?php PagePropsWidget::$counter++; ?>
</div>







