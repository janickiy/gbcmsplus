<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('background');

$image = $images->getImageUrl();
?>

<style type="text/css" id="custom-background-css">
    body.custom-background {
        background-image: url(<?= $image ?>);
        background-repeat: repeat;
        background-position: top left;
        background-attachment: scroll;
    }
</style>

