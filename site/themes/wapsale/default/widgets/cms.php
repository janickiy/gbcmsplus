<?php
use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
$prop = $data[0]->getPropByCode('cms_images');

?>

<div class="owl-carousel clients">
  <?php foreach ($prop->getImageUrl() as $imageSrc): ?>
    <div class="client">
      <?= Html::img($imageSrc) ?>
    </div>
  <?php endforeach ?>
</div>