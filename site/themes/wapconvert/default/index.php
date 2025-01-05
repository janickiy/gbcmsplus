<?php
/**
 * @var \mcms\common\module\Module $pagesModule
 */

use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapconvert\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use yii\helpers\ArrayHelper;

LandingAsset::register($this);
FormAsset::register($this);

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
  $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);
}

/** @var \mcms\partners\components\api\Publication */
$modulePartners->api('publication', ['view' => $this])->registerImage();

?>
<body>
<div class="header-height"></div>
<header class="header">
  <div class="container">
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/logo_img'
    ])->getResult(); ?>
    <ul class="contacts">
      <li>
        <a href="tel:<?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'main_phone',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?>">
          <i class="fa fa-paper-plane" aria-hidden="true"></i>
          <span class="text"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'main_phone',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></span>
        </a>
      </li>
      <li>
        <a href="skype:<?= $mainQuestionSkype ?>">
          <i class="fa fa-skype" aria-hidden="true"></i>
          <span class="text"><?= $mainQuestionSkype ?></span>
        </a>
      </li>
      <li>
        <a href="mailto:<?= $mainQuestionEmail ?>">
          <i class="fa fa-envelope" aria-hidden="true"></i>
          <span class="text"><?= $mainQuestionEmail ?></span>
        </a>
      </li>
    </ul>
    <ul class="header-controls">
      <li>
        <a href="<?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'faq_url',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?>" class="btn btn-border js-open-faq"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'faq_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></a>
      </li>
      <li>
        <a href="#" class="btn btn-border js-open-login"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'login_button',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></a>
      </li>
      <li>
        <a href="#" class="btn btn-border js-open-reg">
          <i class="fa fa-check-circle green header-controls-check" aria-hidden="true"></i>
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'register_button',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?>
        </a>
      </li>
      <li>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'lang_select',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/lang_select'
        ])->getResult(); ?>
      </li>
    </ul>
  </div>
</header>
<section class="conversion js-conversion">
  <div class="container">
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'socials',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/socials'
    ])->getResult(); ?>
    <div class="conversion-control">
      <div class="conversion-control-text anim anim-fade-down">
        <span class="size1"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'header1',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span><br>
        <span class="size2"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'header2',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span>
        <span class="size3"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'header3',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span>
      </div>
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'conversion_countries',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/conversion_countries'
      ])->getResult(); ?>
    </div>
    <div class="conversion-info js-conversion-info anim anim-zoom-in">
      <div class="conversion-info-pc"><i class="fa fa-television" aria-hidden="true"></i></div>
      <div class="conversion-info-user"><i class="fa fa-child" aria-hidden="true"></i></div>
      <div class="conversion-info-lines"></div>
      <div class="conversion-info-day">
        <span class="conversion-info-time"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'per_day',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span>
        <div class="conversion-info-day-content">
          <div class="conversion-info-day-icon"><i class="fa fa-credit-card" aria-hidden="true"></i></div>
          <div class="conversion-info-day-text">
            <span class="conversion-info-sum js-conversion-sum1">5500</span>
            <span class="conversion-info-currency"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'conversion',
                'pageCode' => 'landing',
                'propCode' => 'currency',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult() ?></span>
          </div>
        </div>
      </div>
      <div class="conversion-info-month">
        <span class="conversion-info-time"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'per_month',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span>
        <span class="conversion-info-sum js-conversion-sum2">85 500</span>
        <span class="conversion-info-currency"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'currency',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span>
      </div>
      <div class="conversion-info-year">
        <span class="conversion-info-time"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'per_year',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span>
        <span class="conversion-info-sum js-conversion-sum3">785 500</span>
        <span class="conversion-info-currency"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'conversion',
            'pageCode' => 'landing',
            'propCode' => 'currency',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?></span>
      </div>
    </div>
  </div>
</section>
<section class="traffic">
  <div class="container">
    <div class="traffic-text anim anim-fade-down">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'traffic_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?>
    </div>
    <div class="traffic-content">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'traffic_countries',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/traffic_countries'
      ])->getResult(); ?>
      <p class="traffic-write-off anim anim-fade-up"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'traffic_write_off',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'traffic_write_off',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/traffic_write_off'
        ])->getResult(); ?>
      </p>
    </div>
  </div>
</section>
<section class="getcontent">
  <div class="container">
    <div class="getcontent-phone anim anim-getcontent">
      <div class="getcontent-content">
        <form class="getcontent-form">
          <div class="getcontent-form-control">
            <input type="text" class="getcontent-form-input js-getcontent-form-input"
                   placeholder="<?= $pagesModule->api('pagesWidget', [
                     'categoryCode' => 'get_content',
                     'pageCode' => 'landing',
                     'propCode' => 'placeholder',
                     'viewBasePath' => $viewBasePath,
                     'view' => 'widgets/prop_multivalue'
                   ])->getResult() ?>" value="Wapconvert.com">
            <a href="#" class="getcontent-form-clear js-getcontent-form-clear"></a>
          </div>
          <h2><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'get_content',
              'pageCode' => 'landing',
              'propCode' => 'title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></h2>
          <button class="btn btn-blue"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'get_content',
              'pageCode' => 'landing',
              'propCode' => 'button_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></button>
        </form>
        <div class="getcontent-message">
          <h4><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'get_content',
              'pageCode' => 'landing',
              'propCode' => 'after_press_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></h4>
          <p><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'get_content',
              'pageCode' => 'landing',
              'propCode' => 'after_press_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></p>
          <p class="privacy"><i class="fa fa-check-circle green"
                                aria-hidden="true"></i><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'get_content',
              'pageCode' => 'landing',
              'propCode' => 'legally',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></p>
        </div>
      </div>
    </div>
  </div>
</section>
<section class="benefits">
  <div class="container">
    <ul class="benefits-list">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'benefits',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/benefits'
      ])->getResult(); ?>
    </ul>
  </div>
</section>
<section class="maps">
  <h2 class="maps-header anim anim-fade-down"><?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'technology_title',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult() ?></h2>
  <div class="maps-container anim anim-zoom-in">
    <div class="maps-bg"></div>
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'technology_countries',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/technology_countries'
    ])->getResult(); ?>
  </div>
</section>
<section class="aswork">
  <div class="container">
    <h2 class="aswork-header anim anim-fade-down"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'how_it_works_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?></h2>
    <p class="aswork-slogan anim anim-fade-up"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'how_it_works_subtitle',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?></p>
    <ul class="aswork-list">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'how_it_works',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/how_it_works'
      ])->getResult(); ?>
    </ul>
    <a href="#" class="btn btn-green aswork-btn js-open-reg anim anim-fade-up"><i class="fa fa-check-circle"
                                                                                  aria-hidden="true"></i>
      Регистрация</a>
    <p class="aswork-privacy anim anim-fade-up"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'how_it_works_privacy',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?></p>
  </div>
</section>
<section class="news">
  <div class="container">
    <h2 class="news-header anim anim-fade-down"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'news_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?></h2>
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'news',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/news'
    ])->getResult(); ?>
  </div>
</section>
<footer class="footer">
  <div class="container">
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/logo_img'
    ])->getResult(); ?>
    <ul class="contacts">
      <li>
        <a href="tel:<?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'main_phone',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?>">
          <i class="fa fa-paper-plane" aria-hidden="true"></i>
          <span class="text"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'main_phone',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></span>
        </a>
      </li>
      <li>
        <a href="skype:<?= $mainQuestionSkype ?>">
          <i class="fa fa-skype" aria-hidden="true"></i>
          <span class="text"><?= $mainQuestionSkype ?></span>
        </a>
      </li>
      <li>
        <a href="mailto:<?= $mainQuestionEmail ?>">
          <i class="fa fa-envelope" aria-hidden="true"></i>
          <span class="text"><?= $mainQuestionEmail ?></span>
        </a>
      </li>
    </ul>
    <ul class="header-controls footer-controls">
      <li>
        <a href="#" class="btn btn-border js-scroll-up">
          <i class="fa fa-arrow-up" aria-hidden="true"></i>
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'button_up_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult() ?>
        </a>
      </li>
    </ul>
  </div>
</footer>

<div style="display: none;">
  <a id="register-modal-button" class="register-modal-button js-open-reg"></a>
  <div class="box-modal js-modal-reg">
    <div class="box-modal-header">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'register_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?>
      <div class="box-modal-close arcticmodal-close"><span class="icon icon-close"></span></div>
    </div>
    <div class="box-modal-content">
      <?= $moduleUser->api('signupForm')->getResult(); ?>
    </div>
  </div>
  <a id="login-modal-button" class="login-modal-button js-open-login"></a>
  <div class="box-modal js-modal-login">
    <div class="box-modal-header">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'login_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?>
      <div class="box-modal-close arcticmodal-close"><span class="icon icon-close"></span></div>
    </div>
    <div class="box-modal-content">
      <?= $moduleUser->api('loginForm')->getResult(); ?>
    </div>
  </div>
  <a id="reset-modal-button" class="request-password-modal-button js-open-passwordresetrequest"></a>
  <div class="box-modal js-modal-passwordresetrequest">
    <div class="box-modal-header">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'password_reset_request_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?>
      <div class="box-modal-close arcticmodal-close"><span class="icon icon-close"></span></div>
    </div>
    <div class="box-modal-content">
      <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
    </div>
  </div>
  <a id="reset-modal-button" class="js-open-passwordreset"></a>
  <div class="box-modal js-modal-passwordreset">
    <div class="box-modal-header">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'password_reset_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?>
      <div class="box-modal-close arcticmodal-close"><span class="icon icon-close"></span></div>
    </div>
    <div class="box-modal-content">
      <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
    </div>
  </div>
  <div class="box-modal js-modal-faq">
    <div class="box-modal-header">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'faq_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult() ?>
      <div class="box-modal-close arcticmodal-close"><span class="icon icon-close"></span></div>
    </div>
    <div class="box-modal-content box-modal-content--faq">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'faq',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/faq'
      ])->getResult(); ?>
    </div>
  </div>

  <a id="fail-modal-button" class="js-open-fail"></a>
  <div class="box-modal js-modal-fail">
    <div class="box-modal-header">
      <h4 class="modal-title fail-title">Error</h4>
      <div class="box-modal-close arcticmodal-close"><span class="icon icon-close"></span></div>
    </div>
    <div class="box-modal-content box-modal-content--faq">
      <div class="modal-body">
        <div class="success_message">
          <div class="mess_title"><span class="fail-subtitle"></span></div>
          <span class="fail-message"></span>
          <span><b></b></span>
        </div>
      </div>
    </div>
  </div>
  <a id="success-modal-button" class="js-open-success"></a>
  <div class="box-modal js-modal-success">
    <div class="box-modal-header">
      <span class="modal-title success-title"></span>
      <div class="box-modal-close arcticmodal-close"><span class="icon icon-close"></span></div>
    </div>
    <div class="box-modal-content modal-content">
      <div class="modal-body">
        <div class="success_message">
          <div class="mess_title"><span class="success-action"></span></div>
          <span class="success-message"></span>
        </div>
      </div>
    </div>
  </div>
</div>
</body>