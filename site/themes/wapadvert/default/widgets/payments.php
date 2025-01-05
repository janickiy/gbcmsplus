<?php
use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php foreach ($data as $page): ?>
  <?php $images = unserialize($page->images); ?>
  <i class="sp-icons sp-icons__<?= $page->code ?>"
     style="background-image: url(<?= $images[0] ?>); background-position: 0 0;"></i>
<?php endforeach ?>
