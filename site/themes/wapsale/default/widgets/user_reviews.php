<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="owl-carousel content-slider">
  <?php foreach($data as $page): ?>
  <?php $images = unserialize($page->images); ?>

  <div class="testimonial">
    <div class="container">
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div class="testimonial-image">
            <img src="<?= $images[0] ?>" alt="<?= $page->name ?>" title="<?= $page->name ?>" class="img-circle">
          </div>
          <div class="testimonial-body">
            <p><?= $page->text ?></p>
            <div class="testimonial-info-1">- <?= $page->name ?> </div>
            <div class="testimonial-info-2"><?= ArrayHelper::getValue($page->getPropByCode('user_position'), 'multilang_value') ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach ?>
</div>