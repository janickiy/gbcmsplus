<?php
use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php $delay = 1; ?>
<?php foreach ($data as $page): ?>
  <?php $images = unserialize($page->images); ?>
  <div class="col-sm-6 col-md-6 col-lg-4">
    <div class="item-wrap_about item-wrap_about an-it-<?= $delay ?>">
      <div class="about-wrap">
      <div class="about-icon">
        <img src="<?= $images[0] ?>" height="60" alt="">
      </div>
      <div class="about-text">
        <h4><?= $page->name ?></h4>
        <p><?= $page->text ?></p>
      </div>
      </div>
    </div>
  </div>
  <?php $delay++; ?>
<?php endforeach ?>

