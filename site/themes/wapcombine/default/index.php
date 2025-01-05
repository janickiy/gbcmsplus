<?php
/**
 * @var \mcms\common\module\Module $pagesModule
 */

use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapcombine\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

LandingAsset::register($this);
FormAsset::register($this);

$this->registerJs("$('select').styler()");
$this->registerJs("$(document).tooltip();");
$this->registerCss('div[role="log"]{display: none;}');

/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

/** @var \mcms\user\Module $moduleUser */
$moduleUser = Yii::$app->getModule('users');

$viewBasePath = '/' . $this->context->id . '/';

$contactValues = $modulePartners->getFooterContactValues();
$mainQuestionExists = ArrayHelper::keyExists('mainQuestions', $contactValues);
$mainQuestionSkype = ArrayHelper::getValue($contactValues, 'mainQuestions.skype');
$mainQuestionEmail = ArrayHelper::getValue($contactValues, 'mainQuestions.email');
$mainQuestionIcq = ArrayHelper::getValue($contactValues, 'mainQuestions.icq');
$mainQuestionTelegram = ArrayHelper::getValue($contactValues, 'mainQuestions.telegram');
$mainQuestionPhone = $pagesModule->api('pagesWidget', [
  'categoryCode' => 'common',
  'pageCode' => 'landing',
  'propCode' => 'contacts_phone',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/prop_multivalue'
])->getResult();
$mainQuestionJabber = $pagesModule->api('pagesWidget', [
  'categoryCode' => 'common',
  'pageCode' => 'landing',
  'propCode' => 'contacts_jabber',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/prop_multivalue'
])->getResult();
$techSupportSkype = ArrayHelper::getValue($contactValues, 'techSupport.skype');
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

$headerImage = $pagesModule->api('pagesWidget', [
  'categoryCode' => 'common',
  'pageCode' => 'landing',
  'propCode' => 'header_image',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/prop_img'
])->getResult();
$footerImage = $pagesModule->api('pagesWidget', [
  'categoryCode' => 'common',
  'pageCode' => 'landing',
  'propCode' => 'footer_image',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/prop_img'
])->getResult();


?>
<body>

<header>
  <section<?= $headerImage ? ' style="background-image: url(' . $headerImage . ');"' : '' ?>>
    <div class="logo">

      <div class="image"><img src="<?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'logo',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_img'
        ])->getResult(); ?>" alt="WAPcombine"></div>
      <span class="tagline"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'logo_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></span>
    </div>

    <div class="how">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'how_it_works_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?>
    </div>

    <ul class="contact">
      <li class="title"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'contacts_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></li>
      <?php if ($mainQuestionPhone): ?>
        <li class="tel">
          <i class="icon-phone"></i><a href="tel:<?= $mainQuestionPhone ?>"><?= $mainQuestionPhone ?></a>
        </li>
      <?php endif ?>
      <?php if ($mainQuestionTelegram): ?>
        <li class="tel">
          <i class="icon-telegram"></i><a href="tel:<?= $mainQuestionTelegram ?>"><?= $mainQuestionTelegram ?></a>
        </li>
      <?php endif ?>
      <?php if ($mainQuestionSkype): ?>
        <li class="skype">
          <i class="icon-skype"></i><a href="skype:<?= $mainQuestionSkype ?>"><?= $mainQuestionSkype ?></a>
        </li>
      <?php endif ?>
      <?php if ($mainQuestionIcq): ?>
        <li class="icq">
          <i class="icon-icq"></i><a href="icq:<?= $mainQuestionIcq ?>"><?= $mainQuestionIcq ?></a>
        </li>
      <?php endif ?>
      <?php if ($mainQuestionJabber): ?>
        <li class="jabber">
          <i class="icon-message"></i><a href="jabber:<?= $mainQuestionJabber ?>"><?= $mainQuestionJabber ?></a>
        </li>
      <?php endif ?>
      <?php if ($mainQuestionEmail): ?>
        <li class="mail">
          <i class="icon-mail"></i><a href="mailto:<?= $mainQuestionEmail ?>"><?= $mainQuestionEmail ?></a>
        </li>
      <?php endif ?>
      <li class="title"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'support_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></li>
      <?php if ($techSupportSkype): ?>
        <li class="skype">
          <i class="icon-skype"></i><a href="skype:<?= $techSupportSkype ?>"><?= $techSupportSkype ?></a>
        </li>
      <?php endif ?>
      <?php if ($techSupportIcq): ?>
        <li class="icq">
          <i class="icon-icq"></i><a href="icq:<?= $techSupportIcq ?>"><?= $techSupportIcq ?></a>
        </li>
      <?php endif ?>
    </ul>

    <div class="countries">
      <b><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'traffic_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></b>
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'traffic_countries',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/traffic_countries'
      ])->getResult(); ?>
      <span data-show-modal="countries"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'view_all_countries',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></span>
    </div>
    <div class="select__lang">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'language_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?>
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'lang_select',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/lang_select'
      ])->getResult(); ?>
    </div>
    <div class="sign">
      <span data-show-modal="rega"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'signup_button_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></span>
      <span data-show-modal="sign"><i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'login_button_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></i></span>
    </div>

    <div class="about">
      <div class="about_box">
        <i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'button_ok_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></i> <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'button_ok_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </div>
    </div>
  </section>
</header>

<footer>
  <div<?= $footerImage ? ' style="background-image: url(' . $footerImage . ');"' : '' ?>>
    <section>
      <b class="title"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'advantages_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></b>
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'advantages',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/advantages'
      ])->getResult(); ?>
      <div class="copy"><a href=""></a>
      </div>
      <span class="regatop"><i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'signup_button_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></i></span>

      <div class="we_paym"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'we_pay_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></div>
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'we_pay',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/we_pay'
      ])->getResult(); ?>
      <a href="http://rgkgroup.com" class="copyright">© RGK Group</a>
    </section>
  </div>
</footer>

<a data-show-modal="sign" class="login-modal-button" href="#"></a>
<div data-yoobe-modal="sign">
  <b class="title" id="title"><?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'login_button_text',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></b>
  <div id="margin">
    <div class="login-cont">
      <?= $moduleUser->api('loginForm')->getResult(); ?>
    </div>
    <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>

  </div>
</div>

<a data-show-modal="rega" class="register-modal-button" href="#"></a>
<div data-yoobe-modal="rega" id="rega">
  <b class="title"><?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'signup_button_text',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></b>
  <?= $moduleUser->api('signupForm')->getResult(); ?>
</div>

<a id="reset-modal-button" href="#"></a>
<div data-yoobe-modal="rega" id="reset-password-modal">
  <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
</div>

<a id="fail-modal-button" class="js-open-fail"></a>
<div data-yoobe-modal id="fail-modal">
  <b class="fail-title title" style="background: #cc4066;"></b>
  <div style="padding:30px;">
    <p><b class="fail-subtitle"></b></p>
    <p class="fail-message"></p>
  </div>
</div>


<a id="success-modal-button" class="js-open-success"></a>
<div data-yoobe-modal id="success-modal">
  <b class="success-title title" style="background: #74b322;"></b>
  <div style="padding:30px;">
    <p><b class="success-subtitle"></b></p>
    <p class="success-action"></p>
    <p class="success-message"></p>
  </div>
</div>

<div data-yoobe-modal="password-reset" id="sign">
  <b class="title">Повторный запрос ссылки</b>
  <form id="resend" action="//wapcombine.com/resend/" method="post">
    <input type="hidden" name="_csrf" value="NEVNczlOc1RYMRUXCjgkJgEmKhRgARodRHIGPk0POwB2Iz48CAUVYw==">
    <div class="form-group custom-field-partners-username">
      <input type="text" id="partners-username" class="form-control" name="Partners[username]" placeholder="E-mail">
      <div class="help-block"></div>
    </div>
    <button type="submit">Отправить повторно</button>
  </form>
</div>

<div data-yoobe-modal="countries" id="countries">
  <b class="title"><?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'traffic_title',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></b>
  <?= $pagesModule->api('pagesWidget', [
    'categoryCode' => 'traffic_countries',
    'viewBasePath' => $viewBasePath,
    'view' => 'widgets/all_traffic_countries'
  ])->getResult(); ?>
</div>

</body>