<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="countries uk-width-large-2-6 uk-visible-large">
  <div class="title title_mini uk-text-center"><?= $module->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'accept_countries_title',
      'viewBasePath' => $this->context->viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?>:</div>

  <ul class="countries-list">
    <?php foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
    <li class="countries-list__item" data-uk-tooltip="{pos:'bottom'}" title="<?= $page->name ?>">
      <i class="sp-icons sp-icons__country" style="background-image: url(<?= $images[0] ?>); background-position: 0 0; background-size: 100% 100%;"></i>
    </li>
    <?php endforeach ?>
  </ul>
</div>