<?php
use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php $delay = 1; ?>
<?php foreach ($data as $page): ?>
  <?php $images = unserialize($page->images); ?>
  <div class="col-xs-12 col-sm-6 col-md-3">
    <div class="item-wrap_work an2-it-<?= $delay ?>">
      <div class="work-icon">
        <img src="<?= $images[0] ?>" height="60" alt="" class="normal">
        <img src="<?= $images[1] ?>" alt="" class="hover">
        <span class="number"><?= $delay ?></span>
      </div>
      <h4><?= $page->name ?></h4>
      <p><?= $page->text ?></p>
      <?php if ($delay != count($data)): ?><div class="arrow"></div><?php endif; ?>
    </div>
  </div>
  <?php $delay++; ?>
<?php endforeach ?>

