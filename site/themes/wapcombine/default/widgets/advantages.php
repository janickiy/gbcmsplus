<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

?>
<ul class="advantages">
  <?php foreach ($data as $page): ?>
    <li>
      <b><?= $page->name ?></b>
      <p><?= $page->text ?></p>
    </li>
  <?php endforeach ?>
</ul>
