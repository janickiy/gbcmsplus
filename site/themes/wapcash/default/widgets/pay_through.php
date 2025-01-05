<?php
use yii\helpers\Html;
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');

$prop = $data[0]->getPropByCode('pay_through_images');


?>

<div class="uk-width-medium-1-3 b_paym uk-hidden-small">
  <span><?= $module->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'pay_through_title',
      'viewBasePath' => $this->context->viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?>: </span>

  <?php foreach($prop->getImageUrl() as $imageSrc): ?>
    <?= Html::img($imageSrc)?>
  <?php endforeach ?>

</div>
