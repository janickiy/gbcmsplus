<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>
<?php foreach($data as $page): ?>
<li class="anim anim-benefit">
  <div class="benefits-icon <?=ArrayHelper::getValue($page->getPropByCode('color'), 'multilang_value')?>"><img src="<?=$page->getPropByCode('image')->getImageUrl()?>"></div>
  <span class="text"><?=$page->name?></span>
</li>
<?php endforeach ?>