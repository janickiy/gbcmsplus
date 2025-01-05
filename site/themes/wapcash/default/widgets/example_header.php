<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$title = $data[0]->getPropByCode('work_example_title');

$title = str_replace("\r\n", "<br>", $title->multilang_value);

?>

<h2 class="example__title uk-text-center title"><?= $title ?></h2>

