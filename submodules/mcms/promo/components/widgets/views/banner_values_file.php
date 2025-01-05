<?php
use mcms\common\multilang\widgets\input\FileInputWidget;
use mcms\promo\components\widgets\BannerValuesWidget;

/** @var $form \kartik\form\ActiveForm */
/** @var $bannerValues \mcms\promo\models\BannerAttributeValue */
/** @var $templateAttribute \mcms\promo\models\BannerTemplateAttribute */
/** @var $previews array */
/** @var $imagesDelete array */

?>


<p class="row">
  <?= $templateAttribute->name?>:
</p>
<div class="row" style="margin-bottom: 25px;">

  <?= FileInputWidget::widget([
    'model' => $bannerValues,
    'previews' => $previews,
    'imagesDelete' => $imagesDelete,
    'attribute' => '[' . BannerValuesWidget::$counter . ']file[{lang}]',
    'options' => ['multiple' => false],
    'pluginOptions' => [
      'showUpload' => false,
      'overwriteInitial' => true,
      'showRemove' => true,
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


  <?= $form->field($bannerValues, '[' . BannerValuesWidget::$counter . ']id', [
    'template' => '{input}',
    'options' => ['class' => '']
  ])->hiddenInput() ?>

  <?= $form->field($bannerValues, '[' . BannerValuesWidget::$counter . ']attribute_id', [
    'template' => '{input}',
    'options' => ['class' => '']
  ])->hiddenInput() ?>

  <?php BannerValuesWidget::$counter++; ?>
</div>







