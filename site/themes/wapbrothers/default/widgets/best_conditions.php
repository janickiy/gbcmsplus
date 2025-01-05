<?php
use mcms\common\helpers\ArrayHelper;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$module = Yii::$app->getModule('pages');
?>

<!-- Для арбитражника -->
<div class="col-sm-6">
  <div class="usloviya-item_wrap arbitragniki">
    <h3><?= $module->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'arbitrary_title',
        'viewBasePath' => $this->context->viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?>
     </h3>
    <ul class="usloviya-list arb-list">
      <?php foreach($data as $page): ?>
        <?php if ($page->getPropByCode('condition')->entity->code != 'arbitrary') continue;?>
        <li><?= $page->name ?></li>
      <?php endforeach ?>
    </ul>
  </div>
</div>

<!-- Для вебмастера -->
<div class="col-sm-6">
  <div class="usloviya-item_wrap webmasters">
    <h3><?= $module->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'webmasters_title',
        'viewBasePath' => $this->context->viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></h3>
    <ul class="usloviya-list web-list">
      <?php foreach($data as $page): ?>
        <?php if ($page->getPropByCode('condition')->entity->code != 'webmaster') continue;?>
        <li><?= $page->name ?></li>
      <?php endforeach ?>
    </ul>
  </div>
</div>