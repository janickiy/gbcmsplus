<?php
/**
 * @var \yii\web\View $this
 */

/** @var \mcms\pages\Module $pagesModule */
$pagesModule = Yii::$app->getModule('pages');
/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

$this->title = $pagesModule->api('pagesWidget', [
    'categoryCode' => 'terms_and_conditions',
    'pageCode' => 'terms_and_conditions',
    'fieldCode' => 'name',
    'viewBasePath' => $viewBasePath,
    'view' => 'widgets/field_value'
])->getResult();

if ($favicon = $modulePartners->api('getFavicon')->getResult()) {
    $this->registerLinkTag(['rel' => 'icon', 'type' => $modulePartners->api('getFavicon')->getIconMimeType(), 'href' => $favicon]);
    $this->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => $favicon]);
}
?>

<body style="padding: 10px">
<?= $pagesModule->api('pagesWidget', [
    'categoryCode' => 'terms_and_conditions',
    'pageCode' => 'terms_and_conditions',
    'fieldCode' => 'text',
    'viewBasePath' => $viewBasePath,
    'view' => 'widgets/field_value'
])->getResult(); ?>
</body>