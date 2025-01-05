<?php
use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\FormAsset;
use mcms\partners\assets\landings\wildoclick\LandingAsset;
use yii\helpers\ArrayHelper;


$viewBasePath = '/' . $this->context->id . '/';

if (isset($page) && $page->url) {
  echo $this->render($page->url, [
    'page' => $page,
    'viewBasePath' => $viewBasePath,
  ]);
  return;
}

$asset = LandingAsset::register($this);
FormAsset::register($this);

$moduleUser = Yii::$app->getModule('users');
/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');
$contactValues = $modulePartners->getFooterContactValues();
$mainQuestionSkype = ArrayHelper::getValue($contactValues, 'mainQuestions.skype');
$mainQuestionEmail = ArrayHelper::getValue($contactValues, 'mainQuestions.email');

$this->title = $this->title instanceof LangAttribute && $this->title->getCurrentLangValue()
  ? $this->title
  : $pagesModule->api('pagesWidget', [
  'categoryCode' => 'common',
  'pageCode' => 'landing',
  'fieldCode' => 'name',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/field_value'
])->getResult();

if ($favicon = $modulePartners->api('getFavicon')->getResult()) {
  $this->registerLinkTag(['rel' => 'icon', 'type' =>  $modulePartners->api('getFavicon')->getIconMimeType(), 'href' => $favicon]);
  $this->registerLinkTag(['rel' => 'apple-touch-icon', 'href' => $favicon]);
}
?>

<body>
<div id="page-wrapper">
  <div id="parallax-top" class="parallax lazy-bg" data-start-offset="0" data-background="<?= $asset->baseUrl ?>/img/bg-top.jpg"></div>
  <div id="parallax-middle" class="parallax lazy-bg" data-start-offset="1600" data-background="<?= $asset->baseUrl ?>/img/bg-middle.jpg"></div>

  <header class="main">
    <div class="wrapper">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/logo_img'
      ])->getResult(); ?>
      <div class="contacts">
        <a href="skype:<?= $mainQuestionSkype ?>?chat" class="skype">
          <span>
            <?= $mainQuestionSkype ?>
          </span>
        </a>
        <a href="mailto:<?= $mainQuestionEmail ?>" class="email"><span><?= $mainQuestionEmail ?></span></a>
      </div>
      <div class="buttons">
        <a href="#" class="btn-generic btn-login login-modal-button">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'enter_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>
        </a>
        <a href="#" class="btn-generic btn-registration register-modal-button">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'registration_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>
          </a>
      </div>
    </div>
  </header>

  <section class="content why-us">
    <div class="wrapper">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'why_us',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/why_us'
      ])->getResult(); ?>
      <div class="align-center">
        <a href="#" class="btn-generic btn-registration btn-check">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'try_yourself',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>
        </a>
      </div>
    </div>
  </section>

  <section class="content how-it-works">
    <div class="wrapper">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'how_work',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/how_work'
      ])->getResult(); ?>
    </div>
  </section>

  <section class="content profit">
    <div class="wrapper">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'profit_convert',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/profit_convert'
      ])->getResult(); ?>
    </div>
  </section>

  <section class="content details">
    <div class="wrapper">
      <div class="row countries-block">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'countries',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/countries'
        ])->getResult(); ?>

        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'from_where',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/from_where'
        ])->getResult(); ?>
      </div>
    </div>
  </section>

  <section class="content responses">
    <div class="wrapper">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'reviews',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/reviews'
      ])->getResult(); ?>
    </div>
  </section>

  <section class="content find-us">
    <div class="wrapper">
      <h2>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'look_for_us',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </h2>

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'solar_system',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/solar_system'
      ])->getResult(); ?>
    </div>
  </section>

  <section class="content rate">
    <div class="wrapper">
      <h2>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'advantages',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </h2>
      <div class="form-wrapper">
        <?= $moduleUser->api('signupForm', ['secondForm' => true])->getResult(); ?>
        <p><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'add_skype',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?> — <span class="icon-skype"></span><a href="skype:<?= $mainQuestionSkype ?>?chat"><?= $mainQuestionSkype ?></a>, <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'make_max',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></p>
      </div>
    </div>
  </section>

  <footer class="main">
    <div class="wrapper">
      <div class="footer-top">
        <div class="contacts">
          <a href="skype:<?= $mainQuestionSkype ?>?chat" class="skype"><span><?= $mainQuestionSkype ?></span></a>
          <a href="mailto:<?= $mainQuestionEmail ?>" class="email"><span><?= $mainQuestionEmail ?></span></a>
        </div>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'socials',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/socials'
        ])->getResult(); ?>
        <?php if (!empty($pagesModule->api('pagesWidget', [
          'categoryCode' => 'info_protection',
          'pageCode' => 'info_protection',
          'propCode' => 'page_body',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult())
        ) { ?>
          <a href="/info_protection/" style="float: right; padding-top: 5px;color: #93949c;"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'info_protection_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></a>
        <?php } ?>
      </div>
      <div class="footer-bottom">
        <div class="logo">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/logo_img'
          ])->getResult(); ?>
        </div>
      </div>
    </div>
  </footer>
</div>

<div class="popup-wrapper c-container" id="reg-popup-wrapper">
  <div class="popup-body c-block" id="reg-popup">
    <a href="#" class="btn-popup-close close"></a>
    <p class="title"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'registration_text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></p>
    <div class="form-wrapper">
      <?= $moduleUser->api('signupForm')->getResult(); ?>
      <p><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'add_skype',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?> — <span class="icon-skype"></span><a href="skype:<?= $mainQuestionSkype ?>?chat"><?= $mainQuestionSkype ?></a>, <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'make_max',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></p>
    </div>
  </div>
</div>

<div class="popup-wrapper c-container" id="login-popup-wrapper">
  <div class="popup-body c-block" id="login-popup">
    <a href="#" class="btn-popup-close"></a>
    <p class="title"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'enter_text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></p>
    <div class="form-wrapper">
      <?= $moduleUser->api('loginForm')->getResult(); ?>
    </div>
  </div>
</div>

<div class="popup-wrapper c-container" id="recovery-popup-wrapper">
  <div class="popup-body c-block" id="recovery-popup">
    <a href="#" class="btn-popup-close"></a>
    <p class="title"><?=Yii::_t('users.forms.request_password_title') ?></p>
    <div class="form-wrapper">
      <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
    </div>
  </div>
</div>

<a class="btn-reset" id="reset-modal-button"></a>
<div class="popup-wrapper c-container" id="reset-popup-wrapper">
  <div class="popup-body c-block" id="reset-popup">
    <a href="#" class="btn-popup-close"></a>
    <p class="title"><?=Yii::_t('users.forms.reset_password_title') ?></p>
    <div class="form-wrapper">
      <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
    </div>
  </div>
</div>

<a class="change-form" id="fail-modal-button"></a>
<div class="popup-wrapper c-container" id="fail-modal-wrapper">
  <div class="popup-body c-block form-wrapper" id="fail-modal">
    <div class="form_container">
      <div class="form-header">
        <p class="fail-title title"></p>
      </div>
      <div class="form-body">
        <label class="fail-subtitle"></label>
        <label class="fail-action"></label>
        <label class="fail-message"></label>
      </div>
      <div class="form-footer align-center">
        <a class="btn-generic btn-reg btn-message-close" data-target="#auth" href="#"><?=Yii::_t('partners.main.prev') ?></a>
      </div>
    </div>
  </div>
</div>

<a class="change-form" id="success-modal-button"></a>
<div class="popup-wrapper c-container" id="success-modal-wrapper">
  <div class="popup-body c-block form-wrapper" id="success-modal">
    <div class="form_container">
      <div class="form-header">
        <p class="success-title title"></p>
      </div>
      <div class="form-body">
        <label class="success-subtitle"></label>
        <label class="success-action"></label>
        <label class="success-message"></label>
      </div>
      <div class="form-footer align-center">
        <a class="btn-generic btn-reg btn-message-close" data-target="#auth" href="#"><?=Yii::_t('partners.main.prev') ?></a>
      </div>
    </div>
  </div>
</div>

</body>