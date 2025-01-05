<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>
<form class="conversion-control-form js-conversion-control-form anim anim-fade-up">
  <input type="text" class="form-control js-conversion-input" placeholder="Сколько моб. посетителей в сутки">
  <div class="form-select js-conversion-select">
    <a href="#" class="form-control">
      <span class="text"><?=$data[0]->name?></span>
      <span class="icon"><i class="fa fa-chevron-down" aria-hidden="true"></i></span>
    </a>
    <select>
      <?php foreach($data as $page): ?>
        <?=Html::tag('option',$page->name, [
          'data-ratio' => ArrayHelper::getValue($page->getPropByCode('ratio'), 'multilang_value')
        ]) ?>
      <?php endforeach ?>
    </select>
  </div>
</form>