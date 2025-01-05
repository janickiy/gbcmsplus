<?php
use yii\helpers\Url;
/** @var \mcms\promo\models\Banner $banner */
/** @var \yii\web\View $this */
/** @var string $language */
//$this->title = '[' . $language . '] ';
$this->params['banner'] = $banner;
?>

<?= $compiled ?>