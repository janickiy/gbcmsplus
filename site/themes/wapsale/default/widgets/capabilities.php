<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');
?>

<div class="panel-group panel-dark" id="accordion">
  <?php foreach($data as $page): ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $page->id ?>">
          <i class="glyphicon <?= $page->code ?>"></i><?= $page->name ?>
        </a>
      </h4>
    </div>
    <div id="collapse<?= $page->id ?>" class="panel-collapse collapse <?= ArrayHelper::getValue($page->getPropByCode('open'), 'value') == 1 ? 'in' : '' ?>">
      <div class="panel-body">
       <?= $page->text ?>
      </div>
    </div>
  </div>
  <?php endforeach ?>
</div>