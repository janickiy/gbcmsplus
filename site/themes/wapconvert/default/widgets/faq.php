<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

?>
<?php foreach ($data as $page): ?>
  <h3><?=$page->name?></h3>
  <p><?=$page->text?></p>
<?php endforeach ?>

