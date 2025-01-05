<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="how-accept uk-flex uk-flex-middle uk-flex-space-around">
  <div class="how-accept__row how-accept__row_column">
    <div class="how-accept__title"><?= $module->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'accept_traffic_title',
        'viewBasePath' => $this->context->viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></div>
    <div class="uk-flex uk-flex-inline">
      <?php foreach($data as $page): ?>
        <?php $images = unserialize($page->images); ?>
        <div class="how-accept__country uk-flex uk-flex-middle uk-flex-space-around"><img src="<?= $images[0] ?>" alt=""><?=strtoupper($page->code)?></div>
      <?php endforeach ?>
    </div>
  </div>
</div>