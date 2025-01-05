<?php
use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapbrothers\LandingAsset;
use mcms\partners\assets\landings\wapbrothers\LandingIEAsset;
use mcms\partners\assets\landings\FormAsset;
use yii\helpers\ArrayHelper;

/** @var $pagesModule \mcms\common\module\Module */

LandingAsset::register($this);
LandingIEAsset::register($this);
FormAsset::register($this);
$modulePartners = Yii::$app->getModule('partners');
$moduleUser = Yii::$app->getModule('users');

$contactValues = Yii::$app->getModule('partners')->getFooterContactValues();
$mainQuestionExists = ArrayHelper::keyExists('mainQuestions', $contactValues);
$mainQuestionSkype = ArrayHelper::getValue($contactValues, 'mainQuestions.skype');
$mainQuestionEmail = ArrayHelper::getValue($contactValues, 'mainQuestions.email');

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

if ($favicon = $modulePartners->api('getFavicon')->getResult())
  $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);

$modulePartners->api('publication', ['view' => $this])->registerImage();
?>

  <body>

<!-- Header -->
<header class="header" id="header">
  <div class="container">
    <div class="row">

      <div class="col-md-2 col-sm-3 hidden-xs logo-wrap">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/logo_img'
        ])->getResult(); ?>
      </div>

      <div class="contacts col-md-6">
        <?php if($mainQuestionEmail): ?>
          <a href="mailto:<?= $mainQuestionEmail; ?>" class="mail"><span><?= $mainQuestionEmail; ?></span></a>
        <?php endif; ?>
        <?php if ($mainQuestionSkype): ?>
          <a href="skype:<?= $mainQuestionSkype; ?>?chat" class="skype"><span><?= $mainQuestionSkype; ?></span></a>
        <?php endif; ?>
      </div>

      <div class="col-md-4 col-sm-9 col-xs-12 btn-head-wrap">
        <div class="head-btn">
          <a href="#modal-form_rega" class="rega-btn register-modal-button popup-with-move-anim"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'registration_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></a>
          <a href="#modal-form_login" class="login-btn login-modal-button popup-with-move-anim"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'login_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></a>
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'lang_select',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/lang_select'
          ])->getResult(); ?>
        </div>
      </div>

    </div>

  </div>
</header>

<!-- Шапка -->
<section class="home">
  <div class="home-content">
    <div class="logo-box_home">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/logo_img'
      ])->getResult(); ?><br>
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'millions_in_one_click',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></span>
    </div>

    <!-- Телефон с графиком -->
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/phone_img'
    ])->getResult(); ?>

  </div>
</section>
<nav class="menu clearfix">
  <ul>
    <li><a href="#about"> <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'about_us_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></a></li>
    <li><a href="#kak_rabotaet"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'how_it_works_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></a></li>
    <li><a href="#usloviya"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'terms_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></a></li>
    <li><a href="#reviews"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'reviews_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></a></li>
    <li><a href="#contacts"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'contacts_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></a></li>
  </ul>
  <div class="contacts">
    <?php if($mainQuestionEmail): ?>
      <a href="mailto:<?= $mainQuestionEmail; ?>" class="mail"><span><?= $mainQuestionEmail; ?></span></a>
    <?php endif; ?>
    <?php if ($mainQuestionSkype): ?>
      <a href="skype:<?= $mainQuestionSkype; ?>?chat" class="skype"><span><?= $mainQuestionSkype; ?></span></a>
    <?php endif; ?>
  </div>
</nav>

<!-- Страны -->
<section class="countryes">
  <div class="container">
    <div class="row">

      <div class="col-md-12">
        <div class="country-wrap">

          <h3><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'accept_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>
            <span><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'best_geo',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></span>
          </h3>

          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'accept_traffic',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/accept_traffic'
          ])->getResult(); ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Преимущества -->
<section class="about" id="about">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h2><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'why_us_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?> <span><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'we_know_very_well_job',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>:</span></h2>
      </div>
    </div>
    <div class="row">

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'why_us',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/why_us'
      ])->getResult(); ?>

    </div>
  </div>
</section>

<!-- Как это работает -->
<section class="kak-rabotaet hidden-xs" id="kak_rabotaet">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h2><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'how_it_works_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>?</h2>
      </div>
    </div>

    <div class="row kak-rabotaet-item">

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'how_it_works',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/how_it_works'
      ])->getResult(); ?>

      <div class="col-md-12 col-sm-12 col-xs-12">
        <a href="#modal-form_rega" class="btn-reg popup-with-move-anim"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'sign_up_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></a>
      </div>

    </div>
  </div>
</section>

<!-- Условия -->
<section class="usloviya" id="usloviya">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h2><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'best_conditions_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>!</h2>
      </div>
    </div>

    <div class="row">

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'best_conditions',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/best_conditions'
      ])->getResult(); ?>

    </div>

  </div>
</section>

<!-- Отзывы (чтобы на мобильных скрыть второй ряд с отзывов, добавьте к каждому слайду на 2-й отзыв class="hidden-xs") -->
<section class="reviews" id="reviews">
  <div class="container">
    <div class="col-md-12">
      <div class="row">
        <h2><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'reviews_about_us_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?> <span><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'see_what_write_about_us_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></span></h2>
      </div>
    </div>
  </div>

  <div class="container">

    <div class="row">
      <div class="slider-revievs">

        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'reviews',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/reviews'
        ])->getResult(); ?>

      </div>
    </div>
  </div>

</section>

<section class="payment">
  <div class="container">
    <div class="row">
      <h2><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'we_pay_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>:</h2>

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/we_pay'
      ])->getResult(); ?>


    </div>
  </div>
</section>

<!-- Регистрация -->
<section class="block-registratoin">
  <div class="container">
    <div class="row">
      <div class="col-sm-8 col-sm-offset-2">
        <h2><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'footer_reg_text_1',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?> <span class="green"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'footer_reg_text_2',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></span></h2>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-sm-12">

        <div class="forma-wrap">
          <div class="border-form">
            <?= $moduleUser->api('signupForm', ['hidden-xs' => true, 'secondForm' => true])->getResult(); ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer" id="contacts">
  <div class="container">
    <div class="row">

      <div class="col-xs-12 col-sm-3 col-md-2 hidden-xs row">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/logo_img_footer'
        ])->getResult(); ?>
      </div>

      <div class="col-xs-12 col-sm-6 col-md-8 text-center">
        <span class="copy">&copy; <?= date("Y") ?> <span
            class="hidden-sm hidden-xs"><?= $modulePartners->getFooterCopyright() ?></span></span>

        <div class="contacts">
          <?php if ($mainQuestionEmail): ?>
            <a href="mailto:<?= $mainQuestionEmail; ?>" class="mail"><span><?= $mainQuestionEmail; ?></span></a>
          <?php endif; ?>
          <?php if ($mainQuestionSkype): ?>
            <a href="skype:<?= $mainQuestionSkype; ?>?chat" class="skype"><span><?= $mainQuestionSkype; ?></span></a>
          <?php endif; ?>
        </div>
      </div>


      <div class="col-xs-12 col-sm-3 col-md-2 text-right">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'social_networks',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/social_networks'
        ])->getResult(); ?>
      </div>
    </div>
  </div>
</footer>
<section class="back-top hidden-sm hidden-md hidden-lg">
  <div class="container">
    <a href="#header"></a>
  </div>
</section>


<!--	Формы в модальных окнах-->
<div class="hidden">

  <!-- Форма входа -->
  <div class="forma-wrap login-form popup-form zoom-anim-dialog" id="modal-form_login">
    <div class="border-form">
      <div class="login-form-top">
      <h3><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'login_modal_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></h3>
      <p><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'login_modal_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></p>
      </div>
      <?= $moduleUser->api('loginForm')->getResult(); ?>
    </div>
  </div>

  <!-- Форма восстановления пароля -->
  <div class="forma-wrap login-form popup-form zoom-anim-dialog" id="modal-form_pass">
    <div class="border-form">
      <?= $moduleUser->api('passwordResetRequestForm', [
        'modal_title' => $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'recovery_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(),
        'modal_text' => $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'recovery_modal_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult()
      ])->getResult(); ?>
    </div>
  </div>

  <!-- Форма измененния пароля -->
  <a href="#modal-form_reset" id="reset-modal-button" class="popup-with-move-anim"></a>
  <div class="forma-wrap login-form popup-form zoom-anim-dialog" id="modal-form_reset">
    <div class="border-form">
      <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
    </div>
  </div>

  <!-- Форма регистрации popap -->
  <div class="forma-wrap popup-form zoom-anim-dialog" id="modal-form_rega">
    <div class="border-form">
      <h3><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'registration_modal_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></h3>
      <?= $moduleUser->api('signupForm', ['modal_text' => $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'registration_modal_text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult()])->getResult(); ?>
    </div>
  </div>

  <a href="#modal-form_success" class="popup-with-move-anim" id="success-modal-button"></a>
  <div class="forma-wrap popup-form zoom-anim-dialog" id="modal-form_success">
    <div class="border-form">
      <h3 class="success-title"></h3>
      <p class="success-subtitle"></p>
      <p class="success-message"></p>
    </div>
  </div>

  <a href="#fail-modal" class="popup-with-move-anim" id="fail-modal-button"></a>
  <div class="forma-wrap popup-form zoom-anim-dialog" id="fail-modal">
    <div class="border-form">
      <h3 class="fail-title"></h3>
      <p class="fail-subtitle"></p>
      <p class="fail-message"></p>
    </div>
  </div>

</div>

</body>


