<?php

use yii\helpers\Html;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');

$images = $data[0]->getPropByCode('slider_images');

?>

<?php foreach ($images->getImageUrl() as $image): ?>
    <div class="slide">
        <?= Html::img($image, ['width' => 640, 'height' => 360]) ?>
    </div>
<?php endforeach ?>

