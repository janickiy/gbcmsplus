<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

$i = 0;

foreach ($data as $page): ?>
    <div class="col-xs-3 show__right show__right<?= ++$i ?>">
        <div class="reccomend">
            <div class="reccomend__top">
                <span><?= $page->name ?></span>
            </div>
            <div class="reccomend__body">
                <?= $page->text ?>
            </div>
        </div>
    </div>
<?php endforeach ?>