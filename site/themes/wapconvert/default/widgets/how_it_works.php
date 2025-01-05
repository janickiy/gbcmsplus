<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

?>
<?php foreach ($data as $page): ?>
  <li class="anim anim-aswork">
    <img class="icon" src="<?= $page->getPropByCode('image')->getImageUrl() ?>">
    <p><?= $page->name ?></p>
  </li>
<?php endforeach ?>

