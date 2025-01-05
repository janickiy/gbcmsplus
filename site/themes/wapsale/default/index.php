<?php
use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapsale\LandingAsset;
use mcms\partners\assets\landings\FormAsset;

LandingAsset::register($this);
FormAsset::register($this);
$modulePartners = Yii::$app->getModule('partners');
$moduleUser = Yii::$app->getModule('users');

$viewBasePath = '/' . $this->context->id . '/';

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
  $this->registerLinkTag(['rel' => 'icon', 'type' =>  $modulePartners->api('getFavicon')->getIconMimeType(), 'href' => $favicon]);
  $this->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => $favicon]);
}

$modulePartners->api('publication', ['view' => $this])->registerImage();
?>
<body>

<!-- scrollToTop -->
<!-- ================ -->
<div class="scrollToTop"><i class="icon-up-open-big"></i></div>

<!-- page wrapper start -->
<!-- ================ -->
<div class="page-wrapper">

  <!-- header-top start (Add "dark" class to .header-top in order to enable dark header-top e.g <div class="header-top dark">) -->
  <!-- ================ -->
  <div class="header-top">
    <div class="container">
      <div class="row">
        <div class="col-xs-2 col-sm-6">

          <!-- header-top-first start -->
          <!-- ================ -->
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/header_social'
          ])->getResult(); ?>
          <!-- header-top-first end -->

        </div>
        <div class="col-xs-10 col-sm-6">

          <!-- header-top-second start -->
          <!-- ================ -->
          <div id="header-top-second"  class="clearfix">

            <!-- header top dropdowns start -->
            <!-- ================ -->
            <div class="header-top-dropdown">
              <div class="btn-group dropdown"><button id="loginDrop" class="btn dropdown-toggle login-modal-button" type="button" data-toggle="dropdown">
                  <i class="fa fa-user"></i> <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'login_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?></button>
                <ul class="dropdown-menu dropdown-menu-right dropdown-animation">
                  <li>
                    <?= $moduleUser->api('loginForm')->getResult(); ?>
                  </li>
                </ul>
              </div>
              <div class="btn-group dropdown"><button id="rememberDrop" class="btn dropdown-toggle dropdown-remember" type="button" data-toggle="dropdown"></button>
                <ul class="dropdown-menu dropdown-menu-right dropdown-animation">
                  <li>
                    <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
                  </li>
                </ul>
              </div>

              <div class="reset-password btn-group dropdown"><button class="btn dropdown-toggle dropdown-remember" type="button" data-toggle="dropdown"></button>
                <ul class="dropdown-menu dropdown-menu-right dropdown-animation">
                  <li>
                    <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
                  </li>
                </ul>
              </div>

              <div class="btn-group dropdown"><button id="regDrop" class="btn dropdown-toggle register-modal-button" type="button" data-toggle="dropdown">
                  <i class="fa fa-shopping-cart"></i> <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'sign_up_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?></button>
                <ul class="dropdown-menu dropdown-menu-right dropdown-animation">
                  <li>
                    <?= $moduleUser->api('signupForm')->getResult(); ?>
                  </li>
                </ul>
              </div>

              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'lang_select',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/lang_select'
              ])->getResult(); ?>

            </div>
            <!--  header top dropdowns end -->

          </div>
          <!-- header-top-second end -->

        </div>
      </div>
    </div>
  </div>
  <!-- header-top end -->

  <!-- header start classes:
    fixed: fixed navigation mode (sticky menu) e.g. <header class="header fixed clearfix">
     dark: dark header version e.g. <header class="header dark clearfix">
     ================ -->
  <header class="header fixed clearfix">
    <div class="container">
      <div class="row">
        <div class="col-md-2">

          <!-- header-left start -->
          <!-- ================ -->
          <div class="header-left clearfix">

            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/logo_img'
            ])->getResult(); ?>

            <!-- name-and-slogan -->
            <div class="site-slogan">
            </div>

          </div>
          <!-- header-left end -->

        </div>
        <div class="col-md-10">

          <!-- header-right start -->
          <!-- ================ -->
          <div class="header-right clearfix">

            <!-- main-navigation start -->
            <!-- ================ -->
            <div class="main-navigation animated">

              <!-- navbar start -->
              <!-- ================ -->
              <nav class="navbar navbar-default" role="navigation">
                <div class="container-fluid">

                  <!-- Toggle get grouped for better mobile display -->
                  <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
                      <span class="sr-only">Toggle navigation</span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                    </button>
                  </div>

                  <!-- Collect the nav links, forms, and other content for toggling -->
                  <div class="collapse navbar-collapse" id="navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                      <li class="dropdown">
                        <a href="#ability" class="dropdown-toggle" data-toggle="dropdown"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'opportunities_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                          ])->getResult(); ?></a>
                      </li>
                      <li class="dropdown">
                        <a href="#work" class="dropdown-toggle" data-toggle="dropdown"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'how_it_works_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                          ])->getResult(); ?></a>
                      </li>
                      <!-- mega-menu start -->
                      <li class="dropdown mega-menu">
                        <a href="#preference" class="dropdown-toggle" data-toggle="dropdown"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'advantage_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                          ])->getResult(); ?></a>
                      </li>
                      <!-- mega-menu end -->
                      <!-- mega-menu start -->
                      <li class="dropdown mega-menu">
                        <a href="#comment" class="dropdown-toggle" data-toggle="dropdown"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'reviews_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                          ])->getResult(); ?></a>
                      </li>
                      <!-- mega-menu end -->
                      <li class="dropdown">
                        <a href="#about" class="dropdown-toggle" data-toggle="dropdown"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'about_us_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                          ])->getResult(); ?></a>
                      </li>
                      <li class="dropdown">
                        <a href="#contact" class="dropdown-toggle" data-toggle="dropdown"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'contacts_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                          ])->getResult(); ?></a>
                      </li>
                    </ul>
                  </div>

                </div>
              </nav>
              <!-- navbar end -->

            </div>
            <!-- main-navigation end -->

          </div>
          <!-- header-right end -->

        </div>
      </div>
    </div>
  </header>
  <!-- header end -->

  <!-- banner start -->
  <!-- ================ -->
  <div class="banner">

    <!-- slideshow start -->
    <!-- ================ -->
    <div class="slideshow">

      <!-- slider revolution start -->
      <!-- ================ -->
      <div class="slider-banner-container">
        <div class="slider-banner">
          <ul>
            <!-- slide 2 start -->
            <li data-transition="random" data-slotamount="7" data-masterspeed="500" data-saveperformance="on" data-title="Powerful Bootstrap Theme">

              <!-- main image -->
              <img src="<?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'slide_1',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/slide_img'
              ])->getResult(); ?>"  alt="slidebg1" data-bgposition="center top" data-bgfit="cover" data-bgrepeat="no-repeat">

              <!-- LAYER NR. 1 -->
              <div class="tp-caption white_bg large sfr tp-resizeme"
                   data-x="0"
                   data-y="70"
                   data-speed="600"
                   data-start="1200"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_opportunities_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 2 -->
              <div class="tp-caption default_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="170"
                   data-speed="600"
                   data-start="1600"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 3 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="170"
                   data-speed="600"
                   data-start="1600"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_favorable_conditions_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 4 -->
              <div class="tp-caption default_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="220"
                   data-speed="600"
                   data-start="1800"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 5 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="220"
                   data-speed="600"
                   data-start="1800"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_high_contributions_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 6 -->
              <div class="tp-caption default_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="270"
                   data-speed="600"
                   data-start="2000"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 7 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="270"
                   data-speed="600"
                   data-start="2000"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_full_analytics_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 8 -->
              <div class="tp-caption default_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="320"
                   data-speed="600"
                   data-start="2200"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 9 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="320"
                   data-speed="600"
                   data-start="2200"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_stable_payments_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>
              <!-- LAYER NR. 8 -->
              <div class="tp-caption default_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="370"
                   data-speed="600"
                   data-start="2400"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 9 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="370"
                   data-speed="600"
                   data-start="2400"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_referral_program_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 11 -->
              <div class="tp-caption sfr tp-resizeme"
                   data-x="right"
                   data-y="center"
                   data-speed="600"
                   data-start="2700"
                   data-end="9400"
                   data-endspeed="600"><img src="<?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_1_image',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/slide_img'
                ])->getResult(); ?>" alt="">
              </div>

            </li>
            <!-- slide 2 end -->

            <!-- slide 3 start -->
            <li data-transition="random" data-slotamount="7" data-masterspeed="500" data-saveperformance="on" data-title="Powerful Bootstrap Theme">

              <!-- main image -->
              <img src="<?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'slide_2',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/slide_img'
              ])->getResult(); ?>"  alt="kenburns"  data-bgposition="left center" data-kenburns="on" data-duration="10000" data-ease="Linear.easeNone" data-bgfit="100" data-bgfitend="115" data-bgpositionend="right center">

              <!-- LAYER NR. 1 -->
              <div class="tp-caption white_bg large sfr tp-resizeme"
                   data-x="0"
                   data-y="70"
                   data-speed="600"
                   data-start="1200"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_simple_and_profitable_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 2 -->
              <div class="tp-caption dark_gray_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="170"
                   data-speed="600"
                   data-start="1600"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 3 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="170"
                   data-speed="600"
                   data-start="1600"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_add_site_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 4 -->
              <div class="tp-caption dark_gray_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="220"
                   data-speed="600"
                   data-start="1800"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 5 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="220"
                   data-speed="600"
                   data-start="1800"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_set_code_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 6 -->
              <div class="tp-caption dark_gray_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="270"
                   data-speed="600"
                   data-start="2000"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 7 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="270"
                   data-speed="600"
                   data-start="2000"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_you_get_income_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 8 -->
              <div class="tp-caption dark_gray_bg sfl medium tp-resizeme"
                   data-x="0"
                   data-y="320"
                   data-speed="600"
                   data-start="2200"
                   data-end="9400"
                   data-endspeed="600"><i class="icon-check"></i>
              </div>

              <!-- LAYER NR. 9 -->
              <div class="tp-caption white_bg sfb medium tp-resizeme"
                   data-x="50"
                   data-y="320"
                   data-speed="600"
                   data-start="2200"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_everything_is_automated_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 10 -->
              <div class="tp-caption dark_gray_bg sfb medium tp-resizeme"
                   data-x="0"
                   data-y="370"
                   data-speed="600"
                   data-start="2400"
                   data-end="9400"
                   data-endspeed="600"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'slide_do_not_miss_the_chance_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>
              </div>

              <!-- LAYER NR. 11 -->
              <div class="tp-caption sfr"
                   data-x="right" data-hoffset="-660"
                   data-y="center"
                   data-speed="600"
                   data-start="2700"
                   data-endspeed="600"
                   data-autoplay="false"
                   data-autoplayonlyfirsttime="false"
                   data-nextslideatend="true">

              </div>

            </li>
            <!-- slide 3 end -->

          </ul>
          <div class="tp-bannertimer tp-bottom"></div>
        </div>
      </div>
      <!-- slider revolution end -->

    </div>
    <!-- slideshow end -->

  </div>
  <!-- banner end -->

  <!-- page-top start-->
  <!-- ================ -->
  <div class="page-top">
    <div class="container">
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div class="call-to-action">
            <h1 class="title"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'quick_easy_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></h1>
            <p><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'special_monetization_code_text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></p>
            <a class="btn btn-white more" data-toggle="modal" data-target="#myModal">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'more_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?> <i class="pl-10 fa fa-info"></i>
            </a>

            <!-- Modal -->
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'quick_easy_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></h4>
                  </div>
                  <div class="modal-body">
                    <?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/page_text'
                    ])->getResult(); ?>
                    </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" data-scroll="regDrop"><i class="icon-check"></i> <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'sign_up_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></button>
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><i class="icon-check"></i> <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'contacts_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></button>
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal"><i class="icon-check"></i> <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'close_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></button>
                  </div>
                </div>
              </div>
            </div>

            <a href="#contact" class="btn btn-default contact"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'contacts_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?> <i class="pl-10 fa fa-phone"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- page-top end -->

  <!-- main-container start -->
  <!-- ================ -->
  <section id="ability" name="ability" class="main-container gray-bg">

    <!-- main start -->
    <!-- ================ -->
    <div class="main">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <h1 class="text-center title"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'how_it_works_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></h1>
            <div class="separator"></div>
            <!--p class="text-center">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p-->
            <div class="row">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'how_it_works',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/how_it_works'
              ])->getResult(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- main end -->

  </section>
  <!-- main-container end -->

  <!-- section start -->
  <!-- ================ -->
  <div id="about" name="about" class="section clearfix">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'opportunities_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></h1>
          <div class="separator"></div>
          <p class="lead text-center"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'guarantee_high_payout_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></p>
          <br>
          <div class="row">
            <div class="col-md-6">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'manage_from_any_device',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/manage_from_device'
              ])->getResult(); ?>
            </div>
            <div class="col-md-6">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'capabilities',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/capabilities'
              ])->getResult(); ?>
            </div>
          </div>
          <br>
        </div>
      </div>
    </div>
  </div>
  <!-- section end -->

  <!-- section start -->
  <!-- ================ -->
  <div id="preference" name="preference" class="section parallax light-translucent-bg parallax-bg-3">
    <div class="container">
      <div class="call-to-action">
        <div class="row">
          <div class="col-md-8">
            <h1 class="title text-center"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'earn_more_with_friends_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></h1>
            <p class="text-center"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'have_passive_profit_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></p>
          </div>
          <div class="col-md-4">
            <div class="text-center">
              <a href="#" class="btn btn-default btn-lg" data-scroll="regDrop"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'start_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- section end -->

  <!-- section start -->
  <!-- ================ -->
  <div  class="section clearfix">
    <div class="container">
      <div class="row">
        <div class="col-md-12">

          <h1 class="text-center"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'advantage_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></h1>
          <div class="separator"></div>
          <p class="lead text-center"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'years_of_experience_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></p>

          <!-- Tabs start -->
          <!-- ================ -->
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'benefits',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/benefits'
          ])->getResult(); ?>

          <!-- tabs end -->

        </div>
      </div>
    </div>
  </div>
  <!-- section end -->

  <!-- section start -->
  <!-- ================ -->
  <div id="comment" name="comment" class="section gray-bg clearfix">
    <h1 class="text-center title"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'reviews_about_us_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></h1>

    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'reviews',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/user_reviews'
    ])->getResult(); ?>

  </div>
  <!-- section end -->

  <!-- section start -->
  <!-- ================ -->
  <div id="work" name="work" class="section clearfix">

    <div class="container">
      <div class="row">
        <div class="col-md-12">

          <h2><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'wite_about_us_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></h2>
          <div class="separator-2"></div>
          <p><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'about_us_many_bloggers_write_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></p>
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'write_about_us',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/site_reviews'
          ])->getResult(); ?>
        </div>
      </div>
    </div>

  </div>
  <!-- section end -->

  <!-- section start -->
  <!-- ================ -->
  <div class="section gray-bg text-muted footer-top clearfix">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/cms'
          ])->getResult(); ?>

        </div>
        <div class="col-md-6">
          <blockquote class="inline">
            <p class="margin-clear"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'script_easy_to_install_text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></p>
            <footer><cite title="Source Title">Wap.Sale</cite></footer>
          </blockquote>
        </div>
      </div>
    </div>
  </div>
  <!-- section end -->

  <!-- footer start (Add "light" class to #footer in order to enable light footer) -->
  <!-- ================ -->
  <footer id="footer" >

    <!-- .footer start -->
    <!-- ================ -->
    <div id="contact" name="contact" class="footer">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <div class="footer-content">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/logo_footer'
              ])->getResult(); ?>
              <div class="row">
                <div class="col-sm-6">
                  <p><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'get_information_text',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?></p>
                  <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/footer_social'
                  ])->getResult(); ?>
                </div>
                <div class="col-sm-6">
                  <ul class="list-icons">
                    <?php if($footerMainQuestionSkype = $modulePartners->getFooterMainQuestionSkype()): ?>
                      <li><i class="fa fa-skype pr-10"></i> <?= $footerMainQuestionSkype; ?></li>
                    <?php endif; ?>
                    <?php if ($footerMainQuestionEmail = $modulePartners->getFooterMainQuestionEmail()): ?>
                      <li><i class="fa fa-envelope-o pr-10"></i> <?= $footerMainQuestionEmail; ?></li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
              <a href="#" data-toggle="modal" data-target="#addBlock" data-scroll="regDrop"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'sign_up_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?></a>

              <a href="#" class="link" data-toggle="modal" data-target="#enterSite" data-scroll="loginDrop"><span> <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'authorization_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?></span></a>
            </div>
          </div>
          <div class="space-bottom hidden-lg hidden-xs"></div>
          <div class="col-sm-6 col-md-2">
            <div class="footer-content">
              <h2><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'menu_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?></h2>
              <nav>
                <ul class="nav nav-pills nav-stacked">
                  <li><a href="#about"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'opportunities_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></a></li>
                  <li><a href="#ability"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'how_it_works_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></a></li>
                  <li><a href="#preference"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'advantage_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></a></li>
                  <li><a href="#comment"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'reviews_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></a></li>
                  <li><a href="#work"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'about_us_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                      ])->getResult(); ?></a></li>
                </  ul>
              </nav>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 col-md-offset-1">
            <div class="footer-content">
              <h2><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'blogger_reviews_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?></h2>

              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'blogger_reviews',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/blogger_reviews'
              ])->getResult(); ?>

            </div>
          </div>
        </div>
        <div class="space-bottom hidden-lg hidden-xs"></div>
      </div>
    </div>
    <!-- .footer end -->

    <!-- .subfooter start -->
    <!-- ================ -->
    <div class="subfooter">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <p class="text-center">&copy;  <?= date("Y") ?> <?= $modulePartners->getFooterCopyright() ?></p>
          </div>
        </div>
      </div>
    </div>
    <!-- .subfooter end -->

  </footer>
  <!-- footer end -->

</div>
<!-- page-wrapper end -->

<a id="success-modal-button" data-toggle="modal" data-target="#success-modal"></a>
<div class="modal" id="success-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-center" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title success-title"></h2>
      </div>
      <div class="modal-body">
        <h3 class="success-subtitle"></h3>
        <div class="success-action"></div>
        <i class="modal-ok glyphicon glyphicon-ok"></i>
        <div class="modal-note success-message"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn" data-dismiss="modal">Ок</button>
      </div>
    </div>
  </div>
</div>

<a id="fail-modal-button" data-toggle="modal" data-target="#fail-modal"></a>
<div class="modal" id="fail-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-center" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fail-title"></h2>
      </div>
      <div class="modal-body">
        <h3 class="fail-subtitle"></h3>
        <div class="fail-action"></div>
        <i class="modal-ok glyphicon glyphicon-ok modal-error fa fa-close"></i>
        <div class="modal-note fail-message"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn" data-dismiss="modal">Ок</button>
      </div>
    </div>
  </div>
</div>
</body>