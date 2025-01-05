<?php
use yii\helpers\Url;
/** @var \mcms\promo\models\Banner $banner */
/** @var \yii\web\View $this */
/** @var string $language */
$this->title = '[' . $language . '] ' . $banner->name->getLangValue($language);
$this->params['banner'] = $banner;
?>

<iframe src="<?= Url::to(['banners/view', 'id' => $banner->id, 'isIframe' => 1, 'language' => $language], true)?>" width="100%" height="100%" frameborder="0"></iframe>