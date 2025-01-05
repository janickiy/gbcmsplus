<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

?>
<?php foreach (array_chunk($data, 3) as $pages): ?>
    <div class="row">
        <?php foreach ($pages as $page): ?>
            <div class="col-sm-12 col-md-4 manager_block">
                <p class="manager-functions"><?= $page->name ?></p>
            </div>
        <?php endforeach ?>
    </div>
<?php endforeach ?>
