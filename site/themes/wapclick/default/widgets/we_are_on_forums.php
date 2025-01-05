<?php
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');

?>

<?php foreach($data as $page): ?>
  <?php
  $langDisplay = ArrayHelper::getValue($page->getPropByCode('lang_display'), 'entity');
  if ($langDisplay && $langDisplay->code != Yii::$app->language) {
    continue;
  }
  ?>
<?php
  $images = $page->getPropByCode('banner_img');
  if (empty($images)) continue;
  $image = $images->getImageUrl();
  ?>
  <div class="col-sm-3 col-xs-6"><a target="_blank" href="<?= ArrayHelper::getValue($page->getPropByCode('forum_url'), 'multilang_value', '#') ?>"><img class="lazy" data-src="<?= $image ?>" alt=""></a></div>
<?php endforeach ?>
