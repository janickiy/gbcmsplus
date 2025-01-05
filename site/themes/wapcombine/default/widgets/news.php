<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

if($data) {
  $data = array_reverse($data);
}

?>
<ul>
  <?php foreach ($data as $page): ?>
    <li>
      <i><?= Yii::$app->formatter->asDatetime($page->created_at,'short') ?></i>
      <span><?= $page->name ?></span>
    </li>
  <?php endforeach ?>
</ul>
