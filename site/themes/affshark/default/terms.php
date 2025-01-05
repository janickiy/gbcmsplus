<?php

use mcms\partners\assets\landings\affshark\LandingAsset;

/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

$asset = LandingAsset::register($this);

if ($favicon = $modulePartners->api('getFavicon')->getResult()) {
    $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);
    $this->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => $asset->baseUrl . '/img/favicon/apple-touch-icon.png']);
    $this->registerLinkTag(['rel' => 'apple-touch-icon', 'size' => '72x72', 'href' => $asset->baseUrl . '/img/favicon/apple-touch-icon-72x72.png']);
    $this->registerLinkTag(['rel' => 'apple-touch-icon', 'size' => '114x114', 'href' => $asset->baseUrl . '/img/favicon/apple-touch-icon-114x114.png']);
}

?>
<style>
    #my-page {
        background-color: #3b3b3b
    }

    .terms_header {
        color: #fff;
        text-align: center;
        margin: 0 auto;
    }
</style>
<script>
    var TITLE_IMG = '<img src="<?=$asset->baseUrl?>/img/Affshark_logo_sm.png" alt="Affshark company">'
        , LEFT_ARROW = '<img src="<?=$asset->baseUrl?>/img/back.png" alt="left-arow" />'
        , RIGHT_ARROW = '<img src="<?=$asset->baseUrl?>/img/next.png" alt="right-arow" />'
    ;
</script>
<body class="ishome" id="our_body">
<div class="preloader">
    <div class="pulse"></div>
</div>
<div id="my-page">
    <div class="container">
        <a href="/"
           class="back_link"><?= $page->getPropByCode('back_to_affshark')->multilang_value->getCurrentLangValue() ?></a>
        <h2 class="terms_header"><?= $page->getPropByCode('terms_header')->multilang_value->getCurrentLangValue() ?></h2>
        <?= $page->text ?>
    </div>
</div>

</body>