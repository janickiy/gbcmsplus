<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php foreach ($data as $page): ?>
  <div class="col-sm-4 what-work">
    <div class="box-style-1 white-bg object-non-visible animated object-visible fadeInUpSmall"
         data-animation-effect="fadeInUpSmall"
         data-effect-delay="<?= ArrayHelper::getValue($page->getPropByCode('delay'), 'multilang_value', 0) ?>">
      <i class="fa fa-<?= $page->code ?>"></i>
      <h3> <?= $page->name ?></h3>
      <p><?= $page->text ?></p>
      <a class="btn-default btn" href="#" data-toggle="modal" data-target="#addBlock"
         data-scroll="regDrop"><?= $module->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'sign_up_title',
          'viewBasePath' => $this->context->viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></a>
    </div>
  </div>
<?php endforeach ?>
