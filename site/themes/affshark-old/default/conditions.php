<?php
/**
 * @var \yii\web\View $this
 * @var \mcms\pages\models\Page $page
 * @var \yii\web\AssetBundle $asset
 */

use mcms\common\multilang\LangAttribute;


/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

/** @var \mcms\pages\Module $pagesModule */
$pagesModule = Yii::$app->getModule('pages');

/** @var \mcms\user\Module $moduleUser */
$moduleUser = Yii::$app->getModule('users');

if ($favicon = $modulePartners->api('getFavicon')->getResult()) {
    $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);
}

$this->registerCssFile($asset->baseUrl . '/css/onepage2.css', [
    'depends' => 'mcms\partners\assets\landings\affshark\LandingAsset',
    'type' => 'text/css',
]);

$this->title = $this->title instanceof LangAttribute && $this->title->getCurrentLangValue()
    ? $this->title
    : $page->getPropByCode('page_title')->multilang_value;

?>
<body id="page-top" data-spy="scroll" data-target="#fixed-collapse-navbar" data-offset="120" class="push-body"
      style="    background-image: url(<?= $asset->baseUrl ?>/bg_signup.jpg);
              background-size: cover;">

<!-- Main-Navigation -->
<header id="main-navigation">
    <div id="navigation" data-spy="affix" data-offset-top="20" class="affix">
        <div class="container">
            <div class="row">
                <div class="col-md-12">

                    <nav class="navbar navbar-default">
                        <div class="navbar-header page-scroll">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                                    data-target="#fixed-collapse-navbar" aria-expanded="true">
                                <span class="icon-bar top-bar"></span> <span class="icon-bar middle-bar"></span> <span
                                        class="icon-bar bottom-bar"></span>
                            </button>
                            <a class="navbar-brand logo" href="#"><img src="<?= $pagesModule->api('pagesWidget', [
                                    'categoryCode' => 'common',
                                    'pageCode' => 'landing',
                                    'propCode' => 'logo',
                                    'viewBasePath' => $viewBasePath,
                                    'view' => 'widgets/prop_img'
                                ])->getResult() ?>" class="img-responsive" style="display:none;"></a>
                        </div>
                        <div id="fixed-collapse-navbar" class="navbar-collapse collapse navbar-right">
                            <ul class="nav navbar-nav">
                                <li class="hidden">
                                    <a class="page-scroll" href="#page-top"></a>
                                </li>
                                <li class="active">
                                    <a href="/" class="page-scroll"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_home',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a class="page-scroll" href="/#responsive"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_about',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a href="/#we-do" class="page-scroll"
                                       id="we_do_ch"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_whatwedo',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a href="/#contact" class="page-scroll"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_contactus',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a style="cursor: pointer;" href="/signup/"
                                       id="Publisher"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_signup',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a class="page-scroll open_modal" data-toggle="modal" data-target="#modal2"
                                       style="cursor: pointer;"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_login',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                            </ul>
                        </div>
                    </nav>

                </div>
            </div>
        </div>
    </div>
</header>
<div class="container" style="margin-top:100px;">
    <?= $page->getPropByCode('page_body')->multilang_value ?>
</div>
<!-- Footer-->
<footer class=" wow fadeInUp animated" data-wow-duration="500ms" data-wow-delay="300ms"
        style="visibility: visible; animation-duration: 500ms; animation-delay: 300ms; animation-name: fadeInUp;">

    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <ul class="breadcrumb">
                    <li><a href="/"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_home',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                    <li><a href="/#responsive"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_about',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                    <li><a href="/#we-do"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_work',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                    <li><a href="/#contact"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_contactus',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                    <li><a href="/conditions/"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_conditions',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                </ul>
                <p>&copy; <?= date("Y") ?> <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'address',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></p>
            </div>
        </div>
    </div>
</footer>

<div class="modal fade" id="modal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= $moduleUser->api('loginForm')->getResult(); ?>
        </div>
    </div>
</div>

</body>