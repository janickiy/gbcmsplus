<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="owl-carousel carousel">
  <?php $n=400; foreach($data as $page): $n -= 100; ?>
  <?php $images = unserialize($page->images); ?>
  <div class="image-box<?php if($n >= 0): ?> object-non-visible<?php endif; ?>"<?php if($n >= 0): ?> data-animation-effect="fadeInLeft" data-effect-delay="<?= $n ?>"<?php endif; ?>>
    <div class="overlay-container">
      <img src="<?= $images[0] ?>" alt="">
      <div class="overlay">
        <div class="overlay-links">
          <a href="<?= ArrayHelper::getValue($page->getPropByCode('review_url'), 'multilang_value', '#') ?>"><i class="fa fa-link"></i></a>
        </div>
      </div>
    </div>
    <div class="image-box-body">
      <h3 class="title"><a href="<?= ArrayHelper::getValue($page->getPropByCode('review_url'), 'multilang_value', '#') ?>"><?= $page->name ?></a></h3>
      <p><?= $page->text ?></p>
      <a href="<?= ArrayHelper::getValue($page->getPropByCode('review_url'), 'multilang_value', '#') ?>" class="link">
        <span><?= $module->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'more_title',
            'viewBasePath' => $this->context->viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></span>
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>