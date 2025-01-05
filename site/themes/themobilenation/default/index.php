<?php
/**
 * @var \mcms\common\module\Module $pagesModule
 * @var View $this
 */

use mcms\common\multilang\LangAttribute;
use mcms\common\web\View;
use mcms\partners\assets\landings\themobilenation\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use yii\helpers\ArrayHelper;

$asset = LandingAsset::register($this);
FormAsset::register($this);

/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

/** @var \mcms\user\Module $moduleUser */
$moduleUser = Yii::$app->getModule('users');

$viewBasePath = '/' . $this->context->id . '/';

if (isset($page) && $page->url) {
  echo $this->render($page->url, [
    'page' => $page,
    'viewBasePath' => $viewBasePath,
  ]);
  return;
}

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
  $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => $favicon]);
  $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);
}

/** @var \mcms\partners\components\api\Publication */
$modulePartners->api('publication', ['view' => $this])->registerImage();

$js = <<<JS
var forms = $('.form');
var ChangeForm = function(id) {
  forms.hide().removeClass('fadeInUp').filter(id).show().addClass('fadeInUp');
  $( "body" ).scrollTop( 0 );
}
$('.top_bg').slideDown(0, function () {
  $('.logo, .lang').fadeIn(800);
  ChangeForm('#auth');
});

$('.change-form').on('click', function(e) {
  e.preventDefault();
  ChangeForm($(this).data('target'));
});

//Фикс показа ошибки
$('form').on('afterValidateAttribute', function (e) {
    $(e.target).find('.has-error .help-block').show();
});

$('.help-block').hover(function() {
  $(this).hide();
});


//Селекты
$('select').fancySelect();
JS;
$this->registerJs($js);

?>
<body>
<div class="top_bg">
  <div class="logo_container">
    <a href="" class="logo">
      <img src="<?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'logo',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_img'
      ])->getResult() ?>" alt="">
      <!-- <span><b>LOGO</b>TYPE</span> -->
    </a>
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'lang_select',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/lang_select'
    ])->getResult(); ?>
  </div>
</div>
<div class="form" id="auth">
  <div class="form_container">
    <div class="form-header">
      <img src="<?= $asset->baseUrl ?>/img/form_title.svg" alt="">
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'login_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?></span>
    </div>
    <div class="form-body">
      <?= $moduleUser->api('loginForm')->getResult(); ?>
    </div>
    <div class="form-footer">
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'no_account',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?></span>
      <a class="change-form" data-target="#reg" href=""><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'register',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?></a>
    </div>
  </div>
</div>

<a class="register-modal-button change-form" data-target="#reg"></a>
<div class="form" id="reg">
  <div class="form_container">
    <div class="form-header">
      <img src="<?= $asset->baseUrl ?>/img/form_title_reg.svg" alt="">
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'register_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?></span>
    </div>
    <div class="form-body">
      <?= $moduleUser->api('signupForm')->getResult(); ?>
    </div>
    <div class="form-footer">
      <span><?= Yii::_t('users.signup.have_an_account') ?></span>
      <a class="change-form" data-target="#auth" href=""><?=Yii::_t('users.login.sign_in_cabinet')?></a>
    </div>
  </div>
</div>

<a class="request-password-modal-button change-form" data-target="#recovery"></a>
<div class="form" id="recovery">
  <div class="form_container">
    <div class="form-header">
      <img src="<?= $asset->baseUrl ?>/img/form_title_recovery.svg" alt="">
      <span><?=Yii::_t('users.forms.request_password_title') ?></span>
    </div>
    <div class="form-body">
      <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
    </div>
    <div class="form-footer">
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'no_account',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?></span>
      <a class="change-form" data-target="#reg" href=""><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'register',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult() ?></a>
    </div>
  </div>
</div>

<a class="change-form" id="reset-modal-button" data-target="#reset"></a>
<div class="form" id="reset">
  <div class="form_container">
    <div class="form-header">
      <img src="<?= $asset->baseUrl ?>/img/form_title_recovery.svg" alt="">
      <span><?=Yii::_t('users.forms.please_choose_your_new_password') ?></span>
    </div>
    <div class="form-body">
      <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
    </div>
    <div class="form-footer">
      <span><?=Yii::_t('users.forms.remembered_password') ?>?</span>
      <a class="change-form" data-target="#auth" href=""><?=Yii::_t('users.forms.login') ?></a>
    </div>
  </div>
</div>

<a class="change-form" id="fail-modal-button" data-target="#fail-modal"></a>
<div class="form" id="fail-modal">
  <div class="form_container">
    <div class="form-header">
      <span class="fail-title"></span>
    </div>
    <div class="form-body">
      <p class="fail-subtitle"></p>
      <p class="fail-message"></p>
    </div>
    <div class="form-footer">
      <a class="change-form" data-target="#auth" href=""><?=Yii::_t('partners.main.prev') ?></a>
    </div>
  </div>
</div>

<a class="change-form" id="success-modal-button" data-target="#success-modal"></a>
<div class="form" id="success-modal">
  <div class="form_container">
    <div class="form-header">
      <span class="success-title"></span>
    </div>
    <div class="form-body">
      <p class="success-subtitle"></p>
      <p class="success-action"></p>
      <p class="success-message"></p>
    </div>
    <div class="form-footer">
      <a class="change-form" data-target="#auth" href=""><?=Yii::_t('partners.main.prev') ?></a>
    </div>
  </div>
</div>

<div class="footer">
  <span>&copy; <?= date("Y") ?> <?= $modulePartners->getFooterCopyright() ?></span>
  <a href="<?= $pagesModule->api('pagesWidget', [
    'categoryCode' => 'common',
    'pageCode' => 'landing',
    'propCode' => 'copyright_url',
    'viewBasePath' => $viewBasePath,
    'view' => 'widgets/prop_multivalue'
  ])->getResult() ?>"><?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'copyright_text',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult() ?></a>
</div>
</body>