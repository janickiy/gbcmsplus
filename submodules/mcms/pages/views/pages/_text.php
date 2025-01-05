<?php
use kartik\tabs\TabsX;

\mcms\pages\assets\PagePreviewAsset::register($this);

?>

<?php
$items = [];
foreach ($model->text as $language => $value) {
  $items[] = [
    'label' => strtoupper($language),
    'content' => empty($value) ? '-' : $value,
  ];
}
?>
<?= TabsX::widget([
  'items' => $items,
  'position' => TabsX::POS_ABOVE,
  'encodeLabels' => false
]); ?>
