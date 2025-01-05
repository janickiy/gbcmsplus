<?php

use mcms\promo\components\widgets\BannerValuesInputWidget;
use mcms\promo\components\widgets\BannerValuesTextareaWidget;
use mcms\promo\components\widgets\BannerValuesCheckboxWidget;
use mcms\promo\components\widgets\BannerValuesFileWidget;
use mcms\promo\models\Banner;

/** @var $banner Banner */
/** @var $templateAttributes \mcms\promo\models\BannerTemplateAttribute[] */
/** @var $form \kartik\form\ActiveForm */

?>

<?php if($templateAttributes && count($templateAttributes) > 0): ?>
  <?php foreach($templateAttributes as $templateAttribute):
    /** @var \mcms\promo\models\BannerTemplateAttribute $templateAttribute */
    ?>
    <?php switch ($templateAttribute->type) {
    case $templateAttribute::TYPE_INPUT:
      echo BannerValuesInputWidget::widget([
        'form' => $form,
        'templateAttribute' => $templateAttribute,
        'banner' => $banner
      ]);
      break;
    case $templateAttribute::TYPE_IMAGE:
      echo BannerValuesFileWidget::widget([
        'form' => $form,
        'templateAttribute' => $templateAttribute,
        'banner' => $banner
      ]);
      break;
    case $templateAttribute::TYPE_TEXTAREA:
      echo BannerValuesTextareaWidget::widget([
        'form' => $form,
        'templateAttribute' => $templateAttribute,
        'banner' => $banner
      ]);
      break;
  }?>
    <hr>
  <?php endforeach ?>
<?php elseif ($templateAttributes === false): ?>
  <code><?= Yii::_t('promo.banners.banner-values-available_after_save') ?></code>
<?php else: ?>
  <code><?= Yii::_t('promo.banners.banner-values-empty') ?></code>
<?php endif; ?>

