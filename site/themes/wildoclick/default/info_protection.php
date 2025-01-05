<?php
/**
 * @var \yii\web\View $this
 * @var \mcms\pages\models\Page $page
 */

/** @var \mcms\pages\Module $pagesModule */
$pagesModule = Yii::$app->getModule('pages');
/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

if ($favicon = $modulePartners->api('getFavicon')->getResult()) {
  $this->registerLinkTag(['rel' => 'icon', 'type' =>  $modulePartners->api('getFavicon')->getIconMimeType(), 'href' => $favicon]);
  $this->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => $favicon]);
}

$this->title = $page->getPropByCode('page_title')->multilang_value;
?>
<body>
<?= $page->getPropByCode('page_body')->multilang_value ?>
</body>