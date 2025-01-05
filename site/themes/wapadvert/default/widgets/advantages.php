<?php
use yii\helpers\Html;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="advantages uk-container uk-container-center">
  <h2 class="title title_big title_center uk-text-center"><?= $module->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'our_advantages_title',
      'viewBasePath' => $this->context->viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></h2>
  <div class="uk-grid uk-grid-width-medium-1-2">
    <?php foreach($data as $page): ?>
    <?php $images = unserialize($page->images); ?>
    <div class="advantages-item">
      <div class="advantages-item__icon">
        <i class="sp-icons sp-icons__<?= $page->code ?>" style="background-image: url(<?= $images[0] ?>); background-position: 0 0;"></i>
      </div>
      <div class="advantages-item__column">
        <h3 class="title title_medium">

          <div class="title__text">

            <?= $page->name ?>

          </div>

        </h3>
        <div class="advantages-item__text">
          <?= $page->text ?>
        </div>
        <?php if($page->code == 'payments'):?>
        <div class="advantages-item__note"><?= $module->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'pay_through_title',
            'viewBasePath' => $this->context->viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>:
          <?= $module->api('pagesWidget', [
            'categoryCode' => 'payments',
            'viewBasePath' => $this->context->viewBasePath,
            'view' => 'widgets/payments'
          ])->getResult(); ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach ?>
  </div>
</div>