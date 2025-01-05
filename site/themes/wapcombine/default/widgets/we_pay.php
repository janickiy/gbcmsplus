<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */
use yii\helpers\Html;

?>

<div class="webmoney">
  <?php foreach ($data as $page): ?>
    <?php
    $href = $page->getPropByCode('href') ? $page->getPropByCode('href')->multilang_value : '';
    $target = ($page->getPropByCode('target') && $page->getPropByCode('target')->multilang_value) ? '_blank' : null;
    $css_class = $page->getPropByCode('css_class') ? $page->getPropByCode('css_class')->multilang_value : '';
    ?>
    <?= Html::a('', $href, [
      'target' => $target,
      'class' => $css_class,
    ]) ?>
  <?php endforeach ?>
</div>