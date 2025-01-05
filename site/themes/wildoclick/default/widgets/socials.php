<?php

use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>
<div class="socials">
    <?php foreach($data as $page): ?>
        <?php if($page->getPropByCode('link')):?>
          <a rel="nofollow noopener" target="_blank" href="<?=$page->getPropByCode('link')->multilang_value?>" class="<?=$page->code?>"></a>
        <?php endif;?>
    <?php endforeach ?>
</div>