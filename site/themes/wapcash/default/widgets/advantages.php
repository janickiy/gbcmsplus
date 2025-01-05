<?php
use yii\helpers\Html;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="advant uk-container uk-container-center uk-hidden-small">
  <h2 class="advant__title uk-text-center title"><?= $module->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'our_advantages_title',
      'viewBasePath' => $this->context->viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></h2>
  <div class="devider"><i class="sp-icons sp-icons__devider_red uk-align-center"></i>
  </div>

  <ul class="advant-list uk-flex uk-flex-top uk-align-center uk-flex-center uk-flex-wrap uk-flex-space-around">
    <?php $delay = 1; ?>
    <?php foreach($data as $page): ?>
      <li class="advant-list__item" data-uk-scrollspy="{cls:'uk-animation-slide-bottom', repeat: false, delay: <?=$delay?>00}">
        <div class="advant-list__img"><?= Html::img($page->getPropByCode('image')->getImageUrl())?></div>
        <div class="advant-list__text"><?= $page->name ?></div>
      </li>
      <?php $delay++; ?>
    <?php endforeach ?>
  </ul>
</div>