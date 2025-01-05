<?php
/**
 * @var \mcms\common\module\Module $pagesModule
 */

use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\affshark\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use yii\helpers\ArrayHelper;

$asset = LandingAsset::register($this);
FormAsset::register($this);

$viewBasePath = '/' . $this->context->id . '/';

if (isset($page) && $page->url) {
    echo $this->render($page->url, [
        'page' => $page,
        'asset' => $asset,
        'viewBasePath' => $viewBasePath,
    ]);
    return;
}

/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

/** @var \mcms\user\Module $moduleUser */
$moduleUser = Yii::$app->getModule('users');

$contactValues = $modulePartners->getFooterContactValues();
$mainQuestionExists = ArrayHelper::keyExists('mainQuestions', $contactValues);
$mainQuestionSkype = ArrayHelper::getValue($contactValues, 'mainQuestions.skype');
$mainQuestionEmail = ArrayHelper::getValue($contactValues, 'mainQuestions.email');
$mainQuestionIcq = ArrayHelper::getValue($contactValues, 'mainQuestions.icq');
$techSupportEmail = ArrayHelper::getValue($contactValues, 'techSupport.email');
$techSupportIcq = ArrayHelper::getValue($contactValues, 'techSupport.icq');

$this->title = $this->title instanceof LangAttribute && $this->title->getCurrentLangValue()
    ? $this->title
    : $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'page_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
    ])->getResult();

if ($favicon = $modulePartners->api('getFavicon')->getResult()) {
    $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);
}

/** @var \mcms\partners\components\api\Publication */
$modulePartners->api('publication', ['view' => $this])->registerImage();

?>
<body id="page-top" data-spy="scroll" data-target="#fixed-collapse-navbar" data-offset="120" class="push-body">

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
                                    <a href="#" class="page-scroll"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_home',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a class="page-scroll" href="#responsive"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_about',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a href="#we-do" class="page-scroll"
                                       id="we_do_ch"><?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => 'nav_link_whatwedo',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult() ?></a>
                                </li>
                                <li class="">
                                    <a href="#contact" class="page-scroll"><?= $pagesModule->api('pagesWidget', [
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

<img src="<?= $asset->baseUrl ?>/slide-1.jpg" style="display:none;">

<input type="hidden" id="baseUrl" value="<?= $asset->baseUrl ?>">

<section id="main-slider_1" style="overflow: hidden; height: 700px;">
    <div class="container zi2">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-6" style="    margin-top: 114px;">
                    <img src="<?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'logo2',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_img'
                    ])->getResult() ?>" style="width: 100%; max-width: 830px;">
                    <div id="icon" style="transition: 0.9s;">

                        <a href="#responsive"><img src="<?= $asset->baseUrl ?>/images/54414-200.png" alt=""
                                                   style="margin-top: 20px;width: 20%;"></a>
                    </div>
                </div>
                <div class="col-md-6" id="main-slider">
                    <img src='<?= $asset->baseUrl ?>/slider-mobile-img.png' style='margin-top: 700px; width:100%;'
                         class='slow'>
                    <img src='<?= $asset->baseUrl ?>/slider-mobile-img2.png' style='margin-top: 700px; width:100%;'
                         class='slow'>
                </div>
            </div>
        </div>
    </div>
    <div class="slide_bg">
        <div class="slide_bg-1 visible"></div>
        <div class="slide_bg-2 "></div>
    </div>
</section>

<!-- Responsive image with left -->
<section id="responsive" class="top-padding">
    <div id="h3_my" style="display:none;text-align:center;"><h3 class="magin30"
                                                                style="font-family: sans-serif;"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'features_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></h3></div>
    <div class="container-fluid">
        <div class="row responsive-pic">

            <div class="container wow fadeInRight animated" data-wow-duration="500ms" data-wow-delay="900ms"
                 style="visibility: visible; animation-duration: 500ms; animation-delay: 900ms; animation-name: fadeInRight;">
                <div class="row">
                    <div class="col-md-6 col-sm-6 wow fadeInLeft animated" data-wow-duration="500ms"
                         data-wow-delay="600ms"
                         style="visibility: visible; animation-duration: 500ms; animation-delay: 600ms; animation-name: fadeInLeft;">
                        <img src="<?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'full_responsive_img',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_img'
                        ])->getResult() ?>" alt="fully responsive" class="img-responsive"
                             style="position:absolute;left:-110px;    max-width: 119%;" id="full-responsive-img">
                    </div>
                    <div class="col-md-6 col-sm-6 r-test" style="padding-left: 50px;">
                        <h3 id="h3_my_show" class="magin30"
                            style="font-family: sans-serif;"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'features_title',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></h3>

                        <?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'features',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/features'
                        ])->getResult(); ?>

                        <div style="text-align: center;">
                            <div class="ugol bounce-green"><a href="/signup/"
                                                              style="cursor: pointer;"><?= $pagesModule->api('pagesWidget', [
                                        'categoryCode' => 'common',
                                        'pageCode' => 'landing',
                                        'propCode' => 'joinus_button',
                                        'viewBasePath' => $viewBasePath,
                                        'view' => 'widgets/prop_multivalue'
                                    ])->getResult() ?></a></div>
                        </div>
                        <div class="screens"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What We Do Section -->
<section class="we-do bg-grey padding" id="we-do">
</section>

<div id="we-do2" style="display:none;" class="Wallop wow fadeInLeftBig animated" data-wow-duration="500ms"
     data-wow-delay="300ms"
     style="visibility: visible; animation-duration: 500ms; animation-delay: 300ms; animation-name: fadeInLeftBig;">
</div>

<!-- Contact Us -->
<section class="info-section" id="contact">
    <div class="row" style="background-image: url('<?= $asset->baseUrl ?>/images/bg.svg');    background-size: cover;">
        <div class="col-md-6 block text-center wow fadeInLeftBig animated" data-wow-duration="500ms"
             data-wow-delay="300ms"
             style="visibility: visible; animation-duration: 500ms; animation-delay: 300ms; animation-name: fadeInLeftBig;height: 510px;">
            <div class="center">
                <!--  <p class="title">Open for you</p> -->

                <div style="text-align: left;padding-left: 20%;" id="countries">
                    <h2 style="font-family: sans-serif;"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'top_countries_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                        ])->getResult() ?></h2>
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'top_countries',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/top_countries'
                    ])->getResult(); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6 block light text-center wow fadeInRightBig animated" data-wow-duration="500ms"
             data-wow-delay="300ms"
             style="visibility: visible; animation-duration: 500ms; animation-delay: 300ms; animation-name: fadeInRightBig;">
            <div class="center">
                <p class="title"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'with_proposiotions',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></p>
                <h2 style="font-family: sans-serif;"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'with_proposiotions',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></h2>
                <?= $moduleUser->api('contactForm')->getResult(); ?>
            </div>
        </div>
    </div>
</section>

<!-- Footer-->
<footer class=" wow fadeInUp animated" data-wow-duration="500ms" data-wow-delay="300ms"
        style="visibility: visible; animation-duration: 500ms; animation-delay: 300ms; animation-name: fadeInUp;">

    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <ul class="breadcrumb">
                    <li><a href="#"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_home',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                    <li><a href="#responsive"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_about',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                    <li><a href="#we-do"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'nav_link_work',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult() ?></a></li>
                    <li><a href="#contact"><?= $pagesModule->api('pagesWidget', [
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

<a class="register-modal-button" href="/signup/"></a>
<a id="login-modal-button" class="login-modal-button" data-toggle="modal" data-target="#modal2"></a>
<div class="modal fade" id="modal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= $moduleUser->api('loginForm')->getResult(); ?>
        </div>
    </div>
</div>

<a class="request-password-modal-button" data-toggle="modal" data-target="#modal3"></a>
<div class="modal fade" id="modal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
        </div>
    </div>
</div>

<a id="reset-modal-button" data-toggle="modal" data-target="#modal4"></a>
<div class="modal fade" id="modal4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
        </div>
    </div>
</div>

<a id="success-modal-button" data-toggle="modal" data-target="#success-modal"></a>
<div class="modal fade" id="success-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="false">
    <div class="modal-dialog modal-center" role="document">
        <div class="box a_reg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close_modal close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true" class="modal-close">&times;</span></button>
                    <h1 class="modal-title success-title"></h1>
                </div>
                <div class="modal-body">
                    <h3 class="success-subtitle"></h3>
                    <div class="success-action"></div>
                    <i class="modal-ok fa fa-check"></i>
                    <div class="modal-note success-message"></div>
                </div>
                <div class="modal-footer text-center">
                    <button type="submit" class="btn custom-button custom-red-btn" data-dismiss="modal">Ок</button>
                </div>
            </div>
        </div>
    </div>
</div>

<a id="fail-modal-button" data-toggle="modal" data-target="#fail-modal"></a>
<div class="modal fade" id="fail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close_modal close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h1 class="modal-title text-center fail-title" id="myModalLabel"></h1>
            </div>
            <div class="modal-body">
                <h3 class="fail-subtitle"></h3>
                <p class="text-center fail-message"></p>
            </div>
            <div class="modal-footer text-center">
                <button type="submit" class="btn custom-button custom-red-btn" data-dismiss="modal">Ок</button>
            </div>
        </div>
    </div>
</div>

<div id="overlay"></div>

</body>