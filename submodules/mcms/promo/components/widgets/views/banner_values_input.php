<?php
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\promo\components\widgets\BannerValuesDynamicFormWidget;
use mcms\promo\components\widgets\BannerValuesWidget;

/** @var $form \kartik\form\ActiveForm */
/** @var $bannerValues \mcms\promo\models\BannerAttributeValue[] */
/** @var $templateAttribute \mcms\promo\models\BannerTemplateAttribute */

$widgetContainer = 'dynamicform_wrapper_' . $templateAttribute->id;
$widgetBody = 'container-items_' . $templateAttribute->id;
$widgetItem = 'item_' . $templateAttribute->id;
$insertButton = 'add-item_' . $templateAttribute->id;
$deleteButton = 'remove-item_' . $templateAttribute->id;
$categoryPropField = 'banner-attribute-id';
?>

<?php BannerValuesDynamicFormWidget::begin([
  'widgetContainer' => $widgetContainer,
  'widgetBody' => '.' . $widgetBody,
  'widgetItem' => '.' . $widgetItem,
  'insertButton' => '.' . $insertButton,
  'deleteButton' => '.' . $deleteButton,
  'model' => $bannerValues[0],
  'formId' => $form->getId(),
  'formFields' => ['multilang_value', 'id', 'banner_id'],
  'categoryPropId' => $templateAttribute->id,
  'categoryPropField' => '.' . $categoryPropField
]); ?>

<div class="<?= $widgetBody ?>">
  <p class="row">
    <?= $templateAttribute->name ?>:

  </p>
  <?php foreach($bannerValues as $bannerValue): ?>
    <div class="<?= $widgetItem ?>">


      <?= $form->field($bannerValue, '[' . BannerValuesWidget::$counter . ']multilang_value')
        ->widget(InputWidget::class, [
          'class' => 'form-control',
          'form' => $form,
        ])->label(false) ?>

      <?= $form->field($bannerValue, '[' . BannerValuesWidget::$counter . ']attribute_id', [
        'template' => '{input}',
        'options' => ['class' => '']
      ])->hiddenInput(['class' => $categoryPropField]) ?>

      <?= $form->field($bannerValue, '[' . BannerValuesWidget::$counter . ']id', [
        'template' => '{input}',
        'options' => ['class' => '']
      ])->hiddenInput() ?>
    </div>
    <?php BannerValuesWidget::$counter++; ?>
  <?php endforeach ?>




</div>

<?php BannerValuesDynamicFormWidget::end(); ?>






