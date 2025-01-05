<?php
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<?php $n = 0; ?>
<?php foreach ($data as $page): $n++;  ?>
  <?php $images = unserialize($page->images); ?>
  <!-- Слайд 1 -->
  <?php if($n % 2 == 1) :?><div class="slide-rev"><?php endif; ?>

    <div class="col-sm-6<?php if($n % 2 == 0) :?> hidden-xs<?php endif; ?>">
      <div class="reviev-img">
        <img src="<?= $images[0] ?>" width="130" height="130" alt="">
      </div>
      <h4><strong><?= $page->name ?></strong> <span class="<?= ArrayHelper::getValue($page->getPropByCode('color'), 'multilang_value') ?>"><?= ArrayHelper::getValue($page->getPropByCode('description'), 'multilang_value') ?></span></h4>
      <?= $page->text ?>
    </div>
  <?php if($n % 2 == 0 || count($data) == $n) :?></div><?php endif; ?>
<?php endforeach ?>

