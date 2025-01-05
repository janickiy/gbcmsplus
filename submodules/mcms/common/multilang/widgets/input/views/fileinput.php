<?php
use kartik\file\FileInput;
use kartik\tabs\TabsX;
use mcms\common\helpers\ArrayHelper;

/** @var \yii\base\Model $model */
/** @var string $attribute */
/** @var array $options */
/** @var array $pluginOptions */
/** @var array $languages */
/** @var array $previews */
/** @var array $imagesDelete */
/** @var array $pluginEvents */


$items = [];

foreach ($languages as $lang) {

  $langPluginOptions = $pluginOptions;
  $langPluginOptions['initialPreview'] = ArrayHelper::getValue($previews, $lang, []);
  $langPluginOptions['initialPreviewConfig'] = ArrayHelper::getValue($imagesDelete, $lang, []);

  $items[] = [
    'label' => strtoupper($lang),
    'content' => FileInput::widget([
      'model' => $model,
      'attribute' => str_replace('{lang}', $lang, $attribute),
      'options' => $options,
      'pluginOptions' => $langPluginOptions,
      'pluginEvents' => $pluginEvents
    ]),
  ];
}

?>

<?= TabsX::widget([
  'items' => $items,
  'position' => TabsX::POS_ABOVE,
  'encodeLabels' => false
]);?>
