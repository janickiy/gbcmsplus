<?php
use mcms\common\helpers\ArrayHelper;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$module = Yii::$app->getModule('pages');
?>

<div class="phone-box__main uk-container-center uk-hidden-small">
  <?= $module->api('pagesWidget', [
    'categoryCode' => 'common',
    'pageCode' => 'landing',
    'viewBasePath' => $this->context->viewBasePath,
    'view' => 'widgets/phone_img'
  ])->getResult(); ?>
</div>

<div class="phone-box-side phone-box-side_left phone-box-side_arbitrage">

  <h3 class="for-titles-title"><span class="for-titles-title__text"><?= $module->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'arbitrary_title',
        'viewBasePath' => $this->context->viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></span></h3>

  <?php foreach($data as $page): ?>
    <?php if ($page->getPropByCode('category_possibilities')->entity->code != 'arbitrary') continue;?>
    <div class="phone-box-side__item">
      <div class="phone-box-side__icon">
        <i class="phone-box-side__i sp-icon" style="background-image: url(<?= $page->getPropByCode('image')->getImageUrl(); ?>)"></i>
      </div>
      <div class="phone-box-side__text"><?= $page->name ?></div>
    </div>
  <?php endforeach ?>

</div>

<div class="phone-box-side phone-box-side_right phone-box-side_owners">

  <h3 class="for-titles-title"><span class="for-titles-title__text"><?= $module->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'webmaster_title',
        'viewBasePath' => $this->context->viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></span></h3>

  <?php foreach($data as $page): ?>
    <?php if ($page->getPropByCode('category_possibilities')->entity->code != 'webmaster') continue;?>
    <div class="phone-box-side__item">
      <div class="phone-box-side__icon">
        <i class="phone-box-side__i sp-icon" style="background-image: url(<?= $page->getPropByCode('image')->getImageUrl(); ?>)"></i>
      </div>
      <div class="phone-box-side__text"><?= $page->name ?></div>
    </div>
  <?php endforeach ?>

</div>
