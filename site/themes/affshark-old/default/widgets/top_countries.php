<?php
/**
 * @var $data \mcms\pages\models\Page[]
 * @var $category \mcms\pages\models\Category
 */

use yii\helpers\ArrayHelper;

?>
<?php foreach ($data as $page): ?>
    <p class="margen" style="font-size: 17px;margin-bottom: 19px;width:160px;float: left;"> - <?= $page->name ?></p>
<?php endforeach ?>