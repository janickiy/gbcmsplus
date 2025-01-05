<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\ArrayHelper;

$groups = array_chunk($data, 3);
?>
<?php foreach ($groups as $group): ?>
  <ul class="news-list anim anim-zoom-in">
    <?php /** @var \mcms\pages\models\Page $page */
    foreach($group as $page): ?>
    <li>
      <span class="news-date"><?= ArrayHelper::getValue($page->getPropByCode('date'), 'multilang_value') ?></span>
      <a href="#" class="news-title"><?=$page->name?></a>
    </li>
    <?php endforeach ?>
  </ul>
<?php endforeach ?>

