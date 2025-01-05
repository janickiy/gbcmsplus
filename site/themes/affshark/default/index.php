<?php
/**
 * @var \yii\web\View $this
 * @var \mcms\common\module\Module $pagesModule
 */

use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\affshark\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use mcms\user\models\SignupForm;
use yii\helpers\ArrayHelper;
use mcms\common\SystemLanguage;

//TRICKY Хардкод англ. языка для ленда аффшарка, чтобы модалки были только на англ.
(new SystemLanguage())->setLang('en');

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
  $this->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => $asset->baseUrl . '/img/favicon/apple-touch-icon.png']);
  $this->registerLinkTag(['rel' => 'apple-touch-icon', 'size' => '72x72', 'href' => $asset->baseUrl . '/img/favicon/apple-touch-icon-72x72.png']);
  $this->registerLinkTag(['rel' => 'apple-touch-icon', 'size' => '114x114', 'href' => $asset->baseUrl . '/img/favicon/apple-touch-icon-114x114.png']);
}

$this->registerMetaTag(['name' => 'theme-color', 'content' => '#1a1a1a']);
$this->registerMetaTag(['name' => 'msapplication-navbutton-color', 'content' => '#1a1a1a']);
$this->registerMetaTag(['name' => 'apple-mobile-web-app-status-bar-style', 'content' => '#1a1a1a']);

$this->registerMetaTag(['property' => 'og:type', 'content' => 'website']);
$this->registerMetaTag(['property' => 'og:url', 'content' => 'https://affshark.com']);
$this->registerMetaTag(['property' => 'og:description', 'content' => 'Affshark is an advertiser of new generation, running in-house built mobile subscription offers on CPA and provides premium service for every single partner']);
$this->registerMetaTag(['property' => 'og:title', 'content' => 'AffShark | Direct Advetiser']);

$this->registerMetaTag(['http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge']);
$this->registerMetaTag(['name' => 'title', 'content' => 'AffShark | Direct Advetiser']);
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, maximum-scale=1']);
$this->registerMetaTag(['name' => 'description', 'content' => 'Affshark is an advertiser of new generation, running in-house built mobile subscription offers on CPA and provides premium service for every single partner']);
$this->registerMetaTag(['name' => 'keywords', 'content' => 'AffShark, Campaign, Partner, dedicated manager, 1click flow, direct offers, account manager, Performance, Monetized, ROI']);


$pages = Yii::$app->getModule('pages')->api('pages', ['conditions' => [
  'code' => 'landing',
  'is_disabled' => false
]])->setResultTypeDataProvider()->getResult()->getModels();
$mainPage = ArrayHelper::getValue($pages, 0);

/** @var \mcms\partners\components\api\Publication */
$modulePartners->api('publication', ['view' => $this])->registerImage();
$isTeamDisplay = !!$pagesModule->api('pagesWidget', [
  'categoryCode' => 'common',
  'pageCode' => 'landing',
  'propCode' => 'is_team_display',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/prop_value'
])->getResult();

$this->registerJs(<<<JS
$(".show-hide-password-wrapper").click(function(e){
  e.preventDefault();
  $('.active').removeClass('active');
  var target = $(this).attr('data-target');
  console.log(target);
  $("." + target).addClass("active");
});
$(".modal_close").click(function(e){
  e.preventDefault();
  $(".password-wrapper").removeClass("active");
});
$('.login-modal-button').click(function(e) {
  
});

JS
);

$this->registerCss(<<<CSS
.password-wrapper {
	width: 100%
}

.password-wrapper form {
	width: 500px;
	margin: 0 auto;
	background-color: #fff;
	padding: 20px;
	margin-top: 30px;
	-webkit-border-radius: 1rem;
	border-radius: 1rem;
	-webkit-box-shadow: 0 5px 15px rgba(255, 255, 255, .5);
	box-shadow: 0 5px 15px rgba(255, 255, 255, .5);
	position: relative
}

.password-wrapper form .modal-header {
	padding: 15px;
	border-bottom: 1px solid #e5e5e5;
	font-weight: 400;
	font-size: 30px
}

.password-wrapper form .modal-header .modal_close {
	position: absolute;
	top: 0;
	right: 10px
}

.password-wrapper form .modal-body {
	padding: 15px;
	border-bottom: 1px solid #e5e5e5
}

.password-wrapper form .modal-body .form-group {
	margin-bottom: 15px
}

.password-wrapper form .modal-body .form-group .form-control {
	display: block;
	width: 100%;
	height: 43%;
	padding: 6px 12px;
	font-size: 14px;
	outline: 1px solid #f1f1f1
}

.password-wrapper form .modal-body .form-group .form-control:focus {
	outline: .8px solid #ffa22b;
	border-color: transparent
}

.password-wrapper form .modal-footer {
	padding: 15px;
	text-align: right
}

.password-wrapper form .modal-footer .form-group {
	margin-bottom: 15px
}

.password-wrapper form .modal-footer .form-group .btn {
	background-color: #ff993a;
	color: #fff;
	display: inline-block;
	padding: 6px 12px;
	margin-bottom: 0;
	font-size: 18px;
	font-weight: 400;
	text-align: center;
	vertical-align: middle;
	border: 1px solid transparent
}

.password-wrapper {
	position: fixed;
	background-color: rgba(0, 0, 0, .3);
	display: none;
	z-index: 10;
	top: 0;
	bottom: 0;
	left: 0;
	right: 0;
	color: #000
}

.password-wrapper.active {
	display: block
}


.success-wrapper {
  width: 100%; }
  .success-wrapper .modal-content {
    width: 500px;
    margin: 0 auto;
    background-color: #fff;
    padding: 20px;
    margin-top: 30px;
    -webkit-border-radius: 1rem;
            border-radius: 1rem;
    -webkit-box-shadow: 0 5px 15px rgba(255, 255, 255, 0.5);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.5);
    position: relative; }
    .success-wrapper .modal-header {
      padding: 15px;
      border-bottom: 1px solid #e5e5e5;
      font-weight: normal;
      font-size: 30px; }
      .success-wrapper .modal-header .modal_close {
        position: absolute;
        top: 0;
        right: 10px; }
    .success-wrapper .modal-body {
      padding: 15px;}
      .success-wrapper .modal-body .form-group {
        margin-bottom: 15px; }
        .success-wrapper .modal-body .form-group .form-control {
          display: block;
          width: 100%;
          height: 43%;
          padding: 6px 12px;
          font-size: 14px;
          outline: 1px solid #f1f1f1; }
          .success-wrapper .modal-body .form-group .form-control:focus {
            outline: 0.8px solid #FFA22B;
            border-color: transparent; }
    .success-wrapper .modal-footer {
      padding: 15px;
      text-align: right; }
      .success-wrapper .modal-footer .form-group {
        margin-bottom: 15px; }
        .success-wrapper .modal-footer .form-group .btn {
          background-color: #ff993a;
          color: #fff;
          display: inline-block;
          padding: 6px 12px;
          margin-bottom: 0;
          font-size: 18px;
          font-weight: 400;
          text-align: center;
          vertical-align: middle;
          border: 1px solid transparent; }

.success-wrapper {
  position: fixed;
  background-color: rgba(0, 0, 0, 0.3);
  display: none;
  z-index: 10;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  color: #000; }
  .sing-up-wrapper.active, .success-wrapper.active {
    display: block; }


@media only screen and (max-width:768px) {
  .password-wrapper form{
    width:auto
  }
  
}
CSS
);

?>
<body class="ishome" id="our_body">
<script>
  var TITLE_IMG = '<img src="<?=$asset->baseUrl?>/img/Affshark_logo_sm.png" alt="Affshark company">'
    , LEFT_ARROW = '<img src="<?=$asset->baseUrl?>/img/back.png" alt="left-arow" />'
    , RIGHT_ARROW = '<img src="<?=$asset->baseUrl?>/img/next.png" alt="right-arow" />'
  ;
</script>
<div class="preloader">
  <div class="pulse"></div>
</div>

<div class="log-in-wrapper">
  <?= $moduleUser->api('loginForm')->getResult() ?>
</div>
<div class="password-wrapper password-wrapper-request">
  <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
</div>
<a href="#" data-target="password-wrapper-reset" data-dismiss="modal" data-toggle="modal"
   class="show-hide-password-wrapper request-password-modal-button" id="reset-modal-button"></a>

<div class="password-wrapper password-wrapper-reset">
  <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
</div>
<div class="sing-up-wrapper">
  <?php Yii::$container->set(SignupForm::class, [
    'class' => SignupForm::class,
    'isRecaptchaValidator' => true
  ]);?>
  <?= $moduleUser->api('signupForm')->getResult() ?>
</div>
<div class="success-wrapper">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="modal_close close" data-dismiss="modal" id="success-close" aria-label="Close"><span
          aria-hidden="true">&times;</span>
      </button>
      <h5 class="modal-title text-center success-title" id="myModalLabel">The form has been sent successfully.</h5>
    </div>
    <div class="modal-body success-message">
      <p>Thank you for reaching us! We will get in touch as soon as possible.</p>
      <p style="text-align:center">(Mon-Fri, 10am - 7pm GMT+3)</p>
    </div>
  </div>
</div>

<div id="my-page">
  <div id="my-header">

    <header class="site-header" id="home" style="background-image: url('<?= $asset->baseUrl ?>/img/header_bg.png');">
      <div class="top-line waypoint-container">
        <a href="#" class="logo">
          <img src="<?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'logo',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_img'
          ])->getResult() ?>" alt="<?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'logo_alt',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?>">
        </a>
        <div class="navigation">
          <nav id="my-menu-fs" class="navigation-container">
            <ul class="nav_list" id="nav_list">
              <li class="list_item"><a href="#home"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_home',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></a></li>
              <!-- <li class="list_item"><a href="#products">Our products</a></li> -->
              <li class="list_item"><a href="#products"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_offers',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></a></li>
              <li class="list_item"><a href="#advantages"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_advantages',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></a></li>
              <li class="list_item"><a href="#for-private" class="logo-scroller"
                                       id="scroll_to_private"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_for_private',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></a></li>
              <li class="list_item"><a href="#for-company" class="logo-scroller"
                                       id="scroll_to_company"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_for_company',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></a></li>
              <li class="list_item"><a href="#partners"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_partners',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></a></li>
              <?php if ($isTeamDisplay): ?>
                <li class="list_item"><a href="#team"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_our_team',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
              <?php endif ?>
              <li class="list_item"><a href="#contacts"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_contacts',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></a></li>
              <li class="list_item log-button"><button
                  class="show-hide-login login-modal-button"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_login',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></button ></li>
              <li class="list_item log-button"><button class="show-hide-form register-modal-button"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'nav_link_signup',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></button></li>
            </ul>
          </nav>
          <div class="site-overlay"></div>
          <!-- Your Content -->
          <div id="container">
            <!-- Menu Button -->
            <button class="menu-btn hamburger hamburger--emphatic">
					    	<span class="hamburger-box">
								<span class="hamburger-inner"></span>
							</span>
            </button>
          </div>

          <nav class="pushy pushy-right">
            <div class="pushy-content">
              <img src="<?=$asset->baseUrl?>/img/Affshark_logo_sm.png" alt="Affshark company" class="img-responsive">
              <ul class="nav_list-a" id="nav_list-a">
                <li class="list_item-a pushy-link active"><a href="#home"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_home',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
                <li class="list_item-a pushy-link"><a href="#products"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_offers',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></a></li>
                <li class="list_item-a pushy-link"><a href="#advantages"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_advantages',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
                <li class="list_item-a pushy-link"><a href="#special-offer" class="logo-scroller" id="scroll_to_private"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_for_private',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></a></li>
                <li class="list_item-a pushy-link"><a href="#special-offer" class="logo-scroller" id="scroll_to_company"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_for_company',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></a></li>
                <li class="list_item-a pushy-link"><a href="#partners"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_partners',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
                <li class="list_item-a pushy-link"><a href="#team"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_our_team',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
                <li class="list_item-a pushy-link"><a href="#contacts"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_contacts',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
                <li class="list_item-a pushy-link"><a href="#" class="show-hide-login"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_login',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
                <li class="list_item-a pushy-link"><a href="#" class="show-hide-form"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'nav_link_signup',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></li>
              </ul>
            </div>
          </nav>
        </div>
      </div>

      <div class="flex-center">
        <div>

          <div class="container">
            <div class="row content-flex">
              <div class="col-sm-4 hidden-xs animated" id="left-fade">
                <h2 class="logo-header"><a href="#special-offer" id="scroll_to_private"
                                           class="logo-scroller"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'banner_private',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></h2>
              </div>
              <div class="col-md-3 col-sm-4 col-xs-6 big-logo-container">
                <img src="<?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'logo_big',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_img'
                ])->getResult() ?>" alt="#" class="big-logo img-responsive">
              </div>
              <div class="col-sm-4 hidden-xs animated" id="right-fade">
                <h2 class="logo-header"><a href="#special-offer" id="scroll_to_company"
                                           class="logo-scroller"><?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'banner_company',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a></h2>
              </div>
            </div>
          </div>

          <div class="container">
            <div class="row">
              <div class="col-sm-12" id="fade-up">
                <h1 class="main-page-header"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'banner_main',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult() ?></h1>
              </div>
            </div>
          </div>

        </div>
      </div>
    </header>
  </div>


  <div id="my-content">
    <div class="style-line"></div>
    <section class="examples" id="products"
             style="background-image: url('<?= $asset->baseUrl ?>/img/what-we-have-bg.png'); background-size: 100% 100%;background-repeat: no-repeat;">
      <div>
        <h2 class="examples-section-header center-text"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'what_we_have_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></h2>
        <div class="container">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'landings_slider',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/landings_slider'
          ])->getResult(); ?>
        </div>
      </div>
    </section>
    <section class="advantages" id="advantages"
             style="background-image: url('<?= $asset->baseUrl ?>/img/Adventages-bg.png');">
      <div class="mobile-anchor" id="mobile-advantages"></div>
      <div class="flex-center">
        <h2 class="advantages-section-header left-text"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'advantages_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></h2>
        <div class="container">
          <div class="row">
            <div class="col-xs-12 col-md-10 pull-right big-label">
              <p><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'advantages_offers',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult() ?></p>
            </div>
          </div>
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'advantages',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/advantages'
          ])->getResult(); ?>
        </div>
      </div>
    </section>
    <section class="manager-about" id="offers"
             style="background: url('<?= $asset->baseUrl ?>/img/home-contact-bg-left.svg')  0 100% no-repeat,url('<?= $asset->baseUrl ?>/img/home-contact-bg-right.svg') 100% 100% no-repeat;background-size: 50% 90%;background-color: #fff">
      <div class="flex-center">
        <h2 class="manager-about-section-header center-text"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'manager_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></h2>
        <div class="container">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'manager',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/manager'
          ])->getResult(); ?>

        </div>
      </div>
    </section>

    <section id="special-offer">
      <div class="mobile-anchor" id="mobile-special-offer"></div>
      <section class="for-private" id="for-private"
               style="background-image: url('<?= $asset->baseUrl ?>/img/private-bg.png');">
        <div class="flex-center">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'private_lists',
            'pageCode' => 'private',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/private_list'
          ])->getResult() ?>
        </div>
      </section>
    </section>
    <section class="for-private " id="for-company"
             style="background-image: url('<?= $asset->baseUrl ?>/img/private-bg.png');">
      <div class="flex-center">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'private_lists',
          'pageCode' => 'company',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/private_list'
        ])->getResult() ?>
      </div>
    </section>
    <section class="partners-section section-overlay" id="partners">
      <div class="mobile-anchor" id="mobile-partners"></div>
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            <h2 class="partners-section-header center-text"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'partners_slider_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult() ?></h2>
          </div>
          <div class="col-sm-12">
            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'partners_slider',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/partners_slider'
            ])->getResult(); ?>
          </div>
        </div>
      </div>
    </section>
    <?php if ($isTeamDisplay): ?>
      <section class="team" id="team" style="background-image: url('<?= $asset->baseUrl ?>/img/team-bg.png');">
        <div class="col-sm-12">
          <h2 class="team-section-header left-text"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'team_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></h2>
        </div>
        <div class="container">
          <div class="row">
          </div>
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'team',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/team'
          ])->getResult(); ?>
        </div>
      </section>
    <?php endif ?>

    <section class="callback" id="contacts"
             style="background-image: url('<?= $asset->baseUrl ?>/img/contact-us-bg.png')">
      <div class="mobile-anchor" id="mobile-callback"></div>
      <h2 class="center-text"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'contact_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?></h2>
      <div class="flex-center">
        <div class="container">
          <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6 col-xs-12 center-block">
              <div class="callback-info">
                <p>Skype: <?= $mainQuestionSkype ?></p>
                <p>E-mail: <?= $mainQuestionEmail ?></p>
              </div>
              <?= $moduleUser->api('contactForm')->getResult() ?>
            </div>
            <div class="col-md-3"></div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <div id="my-footer">
    <p class="copy text-center">
      &copy; <?= date("Y") ?> <?= $modulePartners->getFooterCopyright() ?>
    </p>
    <p class="text-center">
      <a href="/terms/" class="terms_link" style="color: #fff;text-decoration: underline;text-align: center;display: block;margin: 0 auto;">
        <?= $mainPage->getPropByCode('terms_and_conditions')->multilang_value->getCurrentLangValue() ?>
      </a>
    </p>
  </div>
</div>

<div class="top" title="To Top">
  <img src="<?= $asset->baseUrl ?>/img/top.png" alt="top icon" class="top-icon">
</div>
</body>