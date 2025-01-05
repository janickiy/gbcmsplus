<?php
/**
 * Вывод названия категории.
 */

/** @var $this \mcms\common\web\View */
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

use yii\helpers\ArrayHelper;

echo ArrayHelper::getValue($category,'name');