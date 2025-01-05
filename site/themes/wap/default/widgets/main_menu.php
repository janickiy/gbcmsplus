<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>

<ul class="nav__list">
  <?php /** @var \mcms\pages\models\Page $page */
    foreach($data as $page){ ?>
        <li class="nav__item">
            <?=\yii\helpers\Html::a($page->name,$page->url,['class'=>'nav__link'])?>
        </li>
    <?php } ?>
</ul>