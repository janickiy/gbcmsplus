<?php
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');

?>

<?php foreach($data as $page){
  
  $langDisplay = ArrayHelper::getValue($page->getPropByCode('lang_display'), 'entity');
  if ($langDisplay && $langDisplay->code != Yii::$app->language) {
    continue;
  }

  $images = $page->getPropByCode('banner_img');
  if (empty($images)) continue;
  $image = $images->getImageUrl();
  ?>
    <li class="forums__item">
        <a class="forums__item-link" href="<?= ArrayHelper::getValue($page->getPropByCode('forum_url'), 'multilang_value', '#') ?>">
            <?=\yii\helpers\Html::encode($page->name) ?>
            <img class="forums__item-image" src="<?= $image ?>" alt="<?=\yii\helpers\Html::encode($page->name) ?>">
        </a>
    </li>
<?php } ?>
