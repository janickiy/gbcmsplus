<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

use yii\helpers\ArrayHelper;

?>
<div class="conversion-socials">
  <ul class="conversion-socials-list">
    <?php foreach ($data as $page): ?>
    <li><a href="<?= ArrayHelper::getValue($page->getPropByCode('url'), 'multilang_value', '#') ?>" class="<?= ArrayHelper::getValue($page->getPropByCode('link_class'), 'multilang_value', '#') ?>">
        <i class="<?= ArrayHelper::getValue($page->getPropByCode('icon_class'), 'multilang_value', '#') ?>" aria-hidden="true"></i></a></li>
    <?php endforeach ?>
  </ul>
</div>