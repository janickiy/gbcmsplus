<?php

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

?>

<h2><?= $category->name ?></h2>
<div class="advantage-area inview-animate">
<?php foreach($data as $page): ?>
  <div class="advantage">
    <p><strong><?= $page->name ?></strong></p>
    <p><em><?= $page->text ?></em></p>
  </div>
<?php endforeach ?>
</div>

