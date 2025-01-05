<?php

use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\affshark\LandingAsset;
use mcms\partners\assets\landings\affshark\FormAsset;

LandingAsset::register($this);
FormAsset::register($this);
$modulePartners = Yii::$app->getModule('partners');
$moduleUser = Yii::$app->getModule('users');

$this->title = $this->title instanceof LangAttribute && $this->title->getCurrentLangValue()
    ? $this->title
    : 'AffShark';

$mainQuestionSkype = $modulePartners->getFooterMainQuestionSkype();
$mainQuestionEmail = $modulePartners->getFooterMainQuestionEmail();
if ($favicon = $modulePartners->api('getFavicon')->getResult())
    $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);

$modulePartners->api('publication', ['view' => $this])->registerImage();

$viewBasePath = '/' . $this->context->id . '/';

// отключаем из-за конфликта скриптов шаблона с JqueryAsset
Yii::$app->assetManager->bundles['yii\web\JqueryAsset'] = false;
?>

<body class="home blog custom-background">
<style type="text/css">.recentcomments a {
        display: inline !important;
        padding: 0 !important;
        margin: 0 !important;
    }</style>

<?= $pagesModule->api('pagesWidget', [
    'categoryCode' => 'common',
    'pageCode' => 'landing',
    'viewBasePath' => $viewBasePath,
    'view' => 'widgets/background_img'
])->getResult(); ?>


<div style="display: none;" class="preloader">
    <div style="display: none;" class="status">&nbsp;</div>
</div>

<div id="mobilebgfix">
    <div class="mobile-bg-fix-img-wrap">
        <div class="mobile-bg-fix-img"></div>
    </div>
    <div class="mobile-bg-fix-whole-site">


        <header style="min-height: 76px;" id="home" class="header">

            <div style="min-height: 76px;" id="main-nav" class="navbar navbar-inverse bs-docs-nav fixed" role="banner">

                <div class="container">

                    <div class="navbar-header responsive-logo">

                        <button class="navbar-toggle collapsed" type="button" data-toggle="collapse"
                                data-target=".bs-navbar-collapse">

                            <span class="sr-only">Toggle navigation</span>

                            <span class="icon-bar"></span>

                            <span class="icon-bar"></span>

                            <span class="icon-bar"></span>

                        </button>

                        <?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/logo_img'
                        ])->getResult(); ?>

                    </div>
                    <!-- меюнюшка-->
                    <nav class="navbar-collapse bs-navbar-collapse collapse" role="navigation" id="site-navigation">
                        <a class="screen-reader-text skip-link" href="#content">Skip to content</a>
                        <ul id="menu-menu-1" class="nav navbar-nav navbar-right responsive-nav main-nav-list">
                            <li id="menu-item-6"
                                class="menu-item menu-item-type-custom menu-item-object-custom current-menu-item current_page_item menu-item-home menu-item-6">
                                <a href="/">
                                    <?= $pagesModule->api('pagesWidget', [
                                        'categoryCode' => 'common',
                                        'pageCode' => 'landing',
                                        'propCode' => 'home_title',
                                        'viewBasePath' => $viewBasePath,
                                        'view' => 'widgets/prop_multivalue'
                                    ])->getResult(); ?>
                                </a>
                            </li>

                            <li id="menu-item-19"
                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-19">
                                <a href="#" data-toggle="modal" data-target="#publisher_signup"
                                   class="register-modal-button">
                                    <?= $pagesModule->api('pagesWidget', [
                                        'categoryCode' => 'common',
                                        'pageCode' => 'landing',
                                        'propCode' => 'register_button_text',
                                        'viewBasePath' => $viewBasePath,
                                        'view' => 'widgets/prop_multivalue'
                                    ])->getResult(); ?>
                                </a>
                            </li>
                            <li id="menu-item-8"
                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-8">
                                <a href="#contact">
                                    <?= $pagesModule->api('pagesWidget', [
                                        'categoryCode' => 'common',
                                        'pageCode' => 'landing',
                                        'propCode' => 'contacts_title',
                                        'viewBasePath' => $viewBasePath,
                                        'view' => 'widgets/prop_multivalue'
                                    ])->getResult(); ?>
                                </a>
                            </li>
                            <li id="menu-item-21"
                                class="menu-item menu-item-type-custom menu-item-object-custom menu-item-21">
                                <a href="#" data-toggle="modal" data-target="#login" class="login-modal-button">
                                    <?= $pagesModule->api('pagesWidget', [
                                        'categoryCode' => 'common',
                                        'pageCode' => 'landing',
                                        'propCode' => 'login_button_text',
                                        'viewBasePath' => $viewBasePath,
                                        'view' => 'widgets/prop_multivalue'
                                    ])->getResult(); ?>
                                </a>
                            </li>
                        </ul>
                    </nav>

                </div>

            </div>
            <style>
                #slider-wrap { /* Оболочка слайдера и кнопок */
                    margin-top: 115px;
                    margin-bottom: 35px;
                    /*width:660px; */
                }

                #slider { /* Оболочка слайдера */
                    /*width:640px;*/
                    height: 360px;
                    overflow: hidden;
                    /*border:#eee solid 10px;*/
                    position: relative;
                }

                .slide { /* Слайд */
                    width: 100%;
                    height: 100%;
                }

                .sli-links { /* Кнопки смены слайдов */
                    margin-top: 10px;
                    text-align: center;
                }

                .sli-links .control-slide {
                    margin: 2px;
                    display: inline-block;
                    width: 16px;
                    height: 16px;
                    overflow: hidden;
                    text-indent: -9999px;
                }

                .sli-links .control-slide:hover {
                    cursor: pointer;
                    background-position: center center;
                }

                .sli-links .control-slide.active {
                    background-position: center top;
                }

                #prewbutton, #nextbutton { /* Ссылка "Следующий" и "Педыдущий" */
                    display: block;
                    width: 15px;
                    height: 100%;
                    position: absolute;
                    top: 0;
                    overflow: hidden;
                    text-indent: -999px;
                    background: url(<?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/slider_arrows_img'
            ])->getResult(); ?>) left center no-repeat;
                    opacity: 0.8;
                    z-index: 3;
                    outline: none !important;
                }

                #prewbutton {
                    left: 10px;
                }

                #nextbutton {
                    right: 10px;
                    background: url(<?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/slider_arrows_img'
            ])->getResult(); ?>) right center no-repeat;
                }

                #prewbutton:hover, #nextbutton:hover {
                    opacity: 1;
                }
            </style>
            <div id="slider-wrap">
                <div id="slider">
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'slider_images',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/slider'
                    ])->getResult(); ?>
                </div>
            </div>


            <!-- / END TOP BAR -->
        </header>

        <div id="content" class="site-content">

            <section class="about-us" id="aboutus">
                <div class="container">

                    <!-- SECTION HEADER -->

                    <div class="section-header">

                        <!-- SECTION TITLE -->

                        <h2 class="white-text">
                            <?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'about_us_title',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult(); ?>
                        </h2>
                        <!-- SHORT DESCRIPTION ABOUT THE SECTION -->

                    </div><!-- / END SECTION HEADER -->

                    <!-- 3 COLUMNS OF ABOUT US-->

                    <div class="row">

                        <!-- COLUMN 1 - BIG MESSAGE ABOUT THE COMPANY-->

                        <div class="col-lg-4 col-md-4 column zerif-rtl-big-title">
                            <div data-sr-complete="true" data-sr-init="true" class="big-intro"
                                 data-scrollreveal="enter left after 0s over 1s">
                                <?= $pagesModule->api('pagesWidget', [
                                    'categoryCode' => 'common',
                                    'pageCode' => 'landing',
                                    'propCode' => 'always_keep_trend_title',
                                    'viewBasePath' => $viewBasePath,
                                    'view' => 'widgets/prop_multivalue'
                                ])->getResult(); ?>
                            </div>
                        </div>
                        <div data-sr-complete="true" data-sr-init="true"
                             class="col-lg-4 col-md-4 column zerif_about_us_center"
                             data-scrollreveal="enter bottom after 0s over 1s">

                            <?= $pagesModule->api('pages', ['conditions' => ['code' => 'landing']])->setResultTypeDataProvider()->getResult()->getModels()[0]->text; ?>
                        </div>
                        <!-- COLUMN 1 - SKILSS-->

                        <div class="col-lg-4 col-md-4 column zerif-rtl-skills ">

                            <ul data-sr-complete="true" data-sr-init="true" class="skills"
                                data-scrollreveal="enter right after 0s over 1s">

                                <!-- SKILL ONE -->

                                <li class="skill">

                                    <div class="skill-count">
                                        <div class="skill-count-inner">
                                            <canvas class="skill-canvas" height="400" width="128"></canvas>
                                            <input style="width: 36px; height: 21px; position: absolute; vertical-align: middle; margin-top: 21px; margin-left: -50px; border: 0px none; background: transparent none repeat scroll 0% 0%; font: bold 12px Arial; text-align: center; color: rgb(255, 255, 255); padding: 0px;"
                                                   readonly="readonly" value="50" data-thickness=".2" class="skill1"
                                                   tabindex="-1" type="text"></div>
                                    </div>
                                    <div class="section-legend">
                                        <?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => '50_traffic_text',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult(); ?>
                                    </div>
                                </li>

                                <!-- SKILL TWO -->

                                <li class="skill">

                                    <div class="skill-count">
                                        <div class="skill-count-inner">
                                            <canvas class="skill-canvas" height="400" width="128"></canvas>
                                            <input style="width: 36px; height: 21px; position: absolute; vertical-align: middle; margin-top: 21px; margin-left: -50px; border: 0px none; background: transparent none repeat scroll 0% 0%; font: bold 12px Arial; text-align: center; color: rgb(255, 255, 255); padding: 0px;"
                                                   readonly="readonly" value="70" data-thickness=".2" class="skill2"
                                                   tabindex="-1" type="text"></div>
                                    </div>
                                    <div class="section-legend">
                                        <?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => '70_of_custumers',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult(); ?>
                                    </div>
                                </li>

                                <!-- SKILL THREE -->

                                <li class="skill">

                                    <div class="skill-count">
                                        <div class="skill-count-inner">
                                            <canvas class="skill-canvas" height="400" width="128"></canvas>
                                            <input style="width: 36px; height: 21px; position: absolute; vertical-align: middle; margin-top: 21px; margin-left: -50px; border: 0px none; background: transparent none repeat scroll 0% 0%; font: bold 12px Arial; text-align: center; color: rgb(255, 255, 255); padding: 0px;"
                                                   readonly="readonly" value="100" data-thickness=".2" class="skill3"
                                                   tabindex="-1" type="text"></div>
                                    </div>
                                    <div class="section-legend">
                                        <?= $pagesModule->api('pagesWidget', [
                                            'categoryCode' => 'common',
                                            'pageCode' => 'landing',
                                            'propCode' => '100_success_in_result',
                                            'viewBasePath' => $viewBasePath,
                                            'view' => 'widgets/prop_multivalue'
                                        ])->getResult(); ?>
                                    </div>
                                </li>

                                <!-- SKILL FOUR -->

                                <li class="skill">

                                    <div class="section-legend"></div>
                                </li>

                            </ul>

                        </div> <!-- / END SKILLS COLUMN-->

                    </div> <!-- / END 3 COLUMNS OF ABOUT US-->

                    <!-- CLIENTS -->

                </div> <!-- / END CONTAINER -->

            </section> <!-- END ABOUT US SECTION -->
            <section class="contact-us" id="contact">
                <div class="container">
                    <!-- SECTION HEADER -->
                    <div class="section-header">

                        <h2 class="white-text">
                            <?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'get_in_touch_title',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult(); ?>
                        </h2></div>
                    <!-- / END SECTION HEADER -->

                    <!-- CONTACT FORM-->
                    <div class="row">

                        <?= $moduleUser->api('contactForm')->getResult(); ?>

                    </div>

                    <!-- / END CONTACT FORM-->

                </div> <!-- / END CONTAINER -->

            </section> <!-- / END CONTACT US SECTION-->

        </div><!-- .site-content -->

        <footer id="footer" role="contentinfo">

            <div class="container">

                <div class="new_footer">
                    &copy; <?= date("Y") ?> <?= $modulePartners->getFooterCopyright() ?>
                </div>

            </div> <!-- / END CONTAINER -->

        </footer> <!-- / END FOOOTER  -->


    </div><!-- mobile-bg-fix-whole-site -->
</div><!-- .mobile-bg-fix-wrap -->

<!-- Modal -->
<div class="modal fade text-left login-modal" id="publisher_signup" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?= $moduleUser->api('signupForm')->getResult(); ?>
        </div>
    </div>
</div>


<div class="modal fade overlay" id="a_register" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="box a_reg">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"
                                                                                              class="modal-close">&times;</span>
            </button>
            <h1>
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'join_as_advertiser_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
            </h1>
            <p>
                <br>
                <strong>
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'contact_name',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>
                </strong>
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'contact_position',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
                <br>
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'phone_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
                : <a href="tel:<?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'phone',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>">
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'phone',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>
                </a><br>
                <?php if ($mainQuestionSkype): ?>
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'skype_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>: <a href="skype:<?= $mainQuestionSkype; ?>?call"><?= $mainQuestionSkype; ?></a>
                    <br>
                <?php endif; ?>
                <?php if ($mainQuestionEmail): ?>
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'email_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>: <a href="mailto:<?= $mainQuestionEmail; ?>"><?= $mainQuestionEmail; ?></a>
                <?php endif; ?>
            </p>
        </div>
    </div>

</div>

<!-- Modal -->
<div class="modal fade text-left login-modal" id="login" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?= $moduleUser->api('loginForm')->getResult(); ?>
    </div>
</div>


<a id="success-modal-button" data-toggle="modal" data-target="#success-modal"></a>
<div class="modal fade login-modal" id="success-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog modal-center" role="document">
        <div class="box a_reg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"
                                                                                                      class="modal-close">&times;</span>
                    </button>
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
<div class="modal fade text-left login-modal error-msg" id="fail-modal" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                <h1 class="modal-title text-center fail-title" id="myModalLabel"></h1>
            </div>
            <div class="modal-body">
                <center>
                    <i></i>
                    <h3 class="fail-subtitle"></h3>
                </center>
                <p class="text-center fail-message"></p>
            </div>
            <div class="modal-footer text-center">
                <button type="submit" class="btn custom-button custom-red-btn" data-dismiss="modal">Ок</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade login-modal" id="lost-pass" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
    </div>
</div>

<a id="reset-modal-button" data-toggle="modal" data-target="#reset-modal"></a>
<div class="modal fade login-modal" id="reset-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
    </div>
</div>

</body>
