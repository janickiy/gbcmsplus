<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php foreach ($data as $page): ?>
  <?php $images = unserialize($page->images); ?>
  <h2 class="title"><?= $page->name ?></h2>
  <div class="row">
    <div class="col-md-6">
      <img src="<?= $images[0] ?>" alt="">
    </div>
    <div class="col-md-6">
      <p><?= $page->text ?></p>
    </div>
  </div>
  <p><?= ArrayHelper::getValue($page->getPropByCode('manage_from_device_text'), 'multilang_value') ?></p>
  <a class="btn-default btn" href="#" data-toggle="modal" data-target="#addBlock" data-scroll="regDrop"><?= $module->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'sign_up_title',
      'viewBasePath' => $this->context->viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></a>
  <div class="space hidden-md hidden-lg"></div>

<?php endforeach ?>
