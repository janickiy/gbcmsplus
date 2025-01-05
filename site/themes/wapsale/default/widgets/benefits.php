<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="vertical hc-tabs">

  <!-- Tabs Top -->
  <div class="hc-tabs-top">
    <?php $i=0; foreach($data as $page): $i++; ?>
      <?php $images = unserialize($page->images); ?>
      <img data-tab="#tab<?= $i ?>" src="<?= $images[0] ?>" alt="iDea" data-tab-animation-effect="fadeInRightSmall">
    <?php endforeach; ?>
    <div class="space"></div>
  </div>

  <!-- Tabs Arrow -->
  <div class="arrow hidden-sm hidden-xs"><i class="fa fa-caret-up"></i></div>

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <?php $i=0; foreach($data as $page): $i++; ?>
    <li<?php if ($i == 1): ?> class="active"<?php endif;?>><a href="#tab<?= $i ?>" role="tab" data-toggle="tab"><i class="fa <?= $page->code ?> pr-10"></i><?= $page->name ?></a></li>
    <?php endforeach; ?>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <?php $i=0; foreach($data as $page): $i++; ?>
    <div class="tab-pane fade in<?php if ($i == 1): ?> active<?php endif; ?>" id="tab<?= $i ?>">
      <h1 class="text-center title"><?= $page->name ?></h1>
      <div class="space"></div>
      <div class="row">
        <div class="col-md-6">
          <ul class="list-icons">
            <?php foreach($page->getPropByCode('benefits_list') as $pageProp): ?>
            <li><i class="icon-check pr-10"></i> <?= ArrayHelper::getValue($pageProp, 'multilang_value') ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="col-md-6">
          <?= $page->text ?>
          <?php if($page->code != 'fa-expand'): ?>
          <a class="btn-default btn" href="#" data-toggle="modal" data-target="#addBlock" data-scroll="regDrop"><?= $module->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'sign_up_title',
              'viewBasePath' => $this->context->viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></a>
          <?php else: ?>
          <a href="#contact" class="btn btn-default"><?= $module->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'contacts_title',
              'viewBasePath' => $this->context->viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>