<?php
use mcms\common\helpers\ArrayHelper;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$delay = 100;
?>

<div class="example-list">
  <div class="example-list__row example-list__row_first uk-flex uk-flex-wrap uk-flex-center">

    <?php foreach($data as $page): ?>
      <?php if (!ArrayHelper::getValue($page->getPropByCode('is_upper'), 'value')) continue;?>
      <div class="example-list__item <?= ArrayHelper::getValue($page->getPropByCode('additional_css'), 'multilang_value') ?>" data-uk-scrollspy="{cls:'uk-animation-scale-up', repeat: false, delay: <?= $delay ?>}">

        <?php if ($img = empty($page->getPropByCode('image')) ? null : $page->getPropByCode('image')->getImageUrl()): ?>
          <div><i class="pr-icons uk-align-center <?= ArrayHelper::getValue($page->getPropByCode('additional_image_css'), 'multilang_value') ?>" style="background-image: url(<?= $img ?>)"></i></div>
        <?php endif ?>


        <?php if ($number = ArrayHelper::getValue($page->getPropByCode('number'), 'multilang_value')): ?>
          <div class="example-list__number"><?= $number?></div>
        <?php endif ?>

        <div class="example-list__text <?= ArrayHelper::getValue($page->getPropByCode('additional_text_css'), 'multilang_value') ?>"><?= $page->text ?></div>
      </div>

      <?php $delay += 500; ?>
    <?php endforeach ?>

  </div>
  <div class="example-list__row example-list__row_second uk-flex uk-flex-wrap uk-flex-row-reverse uk-flex-center">
    <?php foreach($data as $page): ?>
      <?php if (ArrayHelper::getValue($page->getPropByCode('is_upper'), 'value')) continue;?>
      <div class="example-list__item <?= ArrayHelper::getValue($page->getPropByCode('additional_css'), 'multilang_value') ?>" data-uk-scrollspy="{cls:'uk-animation-scale-up', repeat: false, delay: <?= $delay ?>}">
        <?php if ($img = empty($page->getPropByCode('image')) ? null : $page->getPropByCode('image')->getImageUrl()): ?>
          <div><i class="pr-icons uk-align-center <?= ArrayHelper::getValue($page->getPropByCode('additional_image_css'), 'multilang_value') ?>" style="background-image: url(<?= $img ?>)"></i></div>
        <?php endif ?>

        <?php if ($number = ArrayHelper::getValue($page->getPropByCode('number'), 'multilang_value')): ?>
          <div class="example-list__number"><?= $number?></div>
        <?php endif ?>

        <div class="example-list__text <?= ArrayHelper::getValue($page->getPropByCode('additional_text_css'), 'multilang_value') ?>"><?= $page->text ?></div>
      </div>

      <?php $delay += 500; ?>
    <?php endforeach ?>
  </div>
</div>