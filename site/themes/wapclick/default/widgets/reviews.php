<?php
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="load_news">

<?php $i = $n = 0;
foreach ($data as $page): ?>
  <?php
  $langDisplay = ArrayHelper::getValue($page->getPropByCode('lang_display'), 'entity');
  if ($langDisplay && $langDisplay->code != Yii::$app->language) {
    continue;
  }
  $i++; $n++;
  ?>
  <?php if ($i == 1): ?><div class="row"><?php endif; ?>
  <div class="col-md-3 col-sm-6">
    <a target="_blank" href="<?= ArrayHelper::getValue($page->getPropByCode('review_url'), 'multilang_value', '#') ?>"
       class="link_title"><?= $page->name ?></a>
    <div><a target="_blank" href="<?= ArrayHelper::getValue($page->getPropByCode('review_url'), 'multilang_value', '#') ?>"
            class="link_source"><?= ArrayHelper::getValue($page->getPropByCode('site'), 'multilang_value') ?></a></div>
  </div>
  <?php if ($i == 4 || $n == count($data)): $i = 0; ?></div><?php endif; ?>
<?php endforeach; ?>
</div>
<?php if (count($data) > 4): ?>
<div class="show_more">
  <a href=""><?= $module->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'show_more_text',
      'viewBasePath' => $this->context->viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></a>
</div>
<?php endif;?>