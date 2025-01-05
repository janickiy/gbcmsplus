<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\Html;

?>
<?php foreach ($data as $page): ?>
  <span><strong><?= $page->name ?></strong> <?= $page->text ?></span>
<?php endforeach ?>