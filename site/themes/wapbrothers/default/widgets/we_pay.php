<?php
use yii\helpers\Html;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');

$prop = $data[0]->getPropByCode('we_pay_images');

?>

<ul>
  <?php foreach($prop->getImageUrl() as $imageSrc): ?>
  <li><?= Html::img($imageSrc)?></li>
  <?php endforeach ?>
</ul>
