<?php
use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapcash\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use yii\helpers\ArrayHelper;

/** @var $pagesModule \mcms\common\module\Module */

LandingAsset::register($this);
FormAsset::register($this);
$modulePartners = Yii::$app->getModule('partners');
$moduleUser = Yii::$app->getModule('users');

$contactValues = Yii::$app->getModule('partners')->getFooterContactValues();
$mainQuestionExists = ArrayHelper::keyExists('mainQuestions', $contactValues);
$mainQuestionSkype = ArrayHelper::getValue($contactValues, 'mainQuestions.skype');
$mainQuestionEmail = ArrayHelper::getValue($contactValues, 'mainQuestions.email');
$mainQuestionIcq = ArrayHelper::getValue($contactValues, 'mainQuestions.icq');
$mainQuestionTelegram = ArrayHelper::getValue($contactValues, 'mainQuestions.telegram');
$techSupportSkype = ArrayHelper::getValue($contactValues, 'techSupport.skype');
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

if ($favicon = $modulePartners->api('getFavicon')->getResult())
  $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);

$modulePartners->api('publication', ['view' => $this])->registerImage();
?>

<body>

<header class="header uk-container uk-container-center uk-flex uk-flex-wrap uk-flex-middle uk-flex-right" id="header">
  <h1 class="logo-title uk-flex uk-flex-middle uk-flex-item-auto">

    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/logo_img'
    ])->getResult(); ?>



  </h1>
  <div class="header-nav uk-flex uk-flex-right">
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'lang_select',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/lang_select'
    ])->getResult(); ?>

    <div class="auth-buttons uk-flex">

      <div class="auth-button">
        <button class="js-reg-form uk-button uk-button-primary uk-button-large register-modal-button" data-uk-modal="{target: '#reg-modal', center:true}">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'register_button_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></button>
      </div>

      <div class="auth-button auth-button_last uk-flex">
        <button class="uk-button button button_login" data-uk-modal="{target: '#login-modal', center:true}"><span>
            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'login_button_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></span></button>
        <button id="reset-modal-button" data-uk-modal="{target: '#reset-modal', center:true}" style="display: none"></button>
        <button id="success-modal-button" data-uk-modal="{target: '#success-modal', center:true}" style="display: none"></button>
        <button id="fail-modal-button" data-uk-modal="{target: '#fail-modal', center:true}" style="display: none"></button>
      </div>


    </div>
  </div>
</header>
<div class="how-it-works">
  <div class="uk-container uk-container-center">
    <div class="mobile-column uk-grid uk-grid-collapse">
      <div class="uk-width-medium-1-3">

        <?php if ($mainQuestionExists || $techSupportSkype || $techSupportIcq): ?>
        <div class="uk-panel uk-panel_how uk-contrast uk-text-bold">
          <?php if ($mainQuestionExists): ?>
          <div class="uk-panel__column">
            <h3 class="uk-panel-title uk-align-center"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'contacts_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></h3>
            <ul class="uk-panel-list">
              <?php if ($mainQuestionSkype): ?>
                <li class="uk-panel-list__item">
                  <i class="uk-panel-list__i sp-icons sp-icons__skype"></i>
                  <a href="skype:<?= $mainQuestionSkype; ?>?chat" class="uk-panel-list__link">
                    <?= $mainQuestionSkype; ?>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($mainQuestionIcq): ?>
                <li class="uk-panel-list__item">
                  <i class="uk-panel-list__i sp-icons sp-icons__icq"></i>
                  <a href="icq:<?= $mainQuestionIcq; ?>" class="uk-panel-list__link">
                    <?= $mainQuestionIcq; ?>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($mainQuestionEmail): ?>
                <li class="uk-panel-list__item">
                  <i class="uk-panel-list__i sp-icons sp-icons__email"></i>
                  <a href="mailto:<?= $mainQuestionEmail; ?>" class="uk-panel-list__link">
                    <?= $mainQuestionEmail; ?>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($mainQuestionTelegram): ?>
                <li class="uk-panel-list__item">
                  <i class="sp-icons sp-icons__telegram"></i>
                  <a target="_blank" href="https://telegram.me/<?= $mainQuestionTelegram; ?>" class="uk-panel-list__link">
                    <?= $mainQuestionTelegram; ?>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </div>
          <?php endif; ?>
          <?php if ($techSupportSkype || $techSupportIcq): ?>
          <div class="uk-panel__column">
            <h3 class="uk-panel-title uk-align-center"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'tech_support_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></h3>
            <ul class="uk-panel-list" style="margin-bottom: 0;">
              <?php if($techSupportSkype): ?>
                <li class="uk-panel-list__item">
                  <i class="uk-panel-list__i sp-icons sp-icons__skype"></i>
                  <a href="skype:<?= $techSupportSkype; ?>?chat" class="uk-panel-list__link">
                    <?= $techSupportSkype; ?>
                  </a>
                </li>
              <?php endif; ?>
              <?php if($techSupportIcq): ?>
                <li class="uk-panel-list__item">
                  <i class="uk-panel-list__i sp-icons sp-icons__icq"></i>
                  <a href="icq:<?= $techSupportIcq; ?>" class="uk-panel-list__link">
                    <?= $techSupportIcq; ?>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'accept_traffic',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/accept_traffic'
        ])->getResult(); ?>


      </div>

      <div class="uk-width-medium-2-3 uk-flex uk-width-1-1 uk-hidden-small">
        <div class="how-container uk-container-center uk-flex uk-flex-column uk-width-1-1 uk-position-relative">


          <div class="phone-img-cont uk-flex uk-flex-item-auto">


            <div class="phone-box uk-position-relative">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'possibilities',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/possibilites'
              ])->getResult(); ?>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $pagesModule->api('pagesWidget', [
  'categoryCode' => 'advantages',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/advantages'
])->getResult(); ?>



<div class="example uk-hidden-small">
  <div class="uk-container uk-container-center">
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/example_header'
    ])->getResult(); ?>

    <div class="devider"><i class="sp-icons sp-icons__devider_green uk-align-center"></i>
    </div>

    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'example',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/example'
    ])->getResult(); ?>


  </div>
</div>


<footer>
  <div class="uk-container uk-container-center uk-hidden-small">
    <div class="sup-footer uk-flex uk-flex-top">
      <div class="sup-footer__contacts">
        <h3 class="uk-panel-title uk-align-center"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'contacts_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h3>
        <ul class="uk-panel-list uk-panel-list_footer uk-text-bold">
          <?php if ($mainQuestionSkype): ?>
            <li class="uk-panel-list__item">
              <i class="sp-icons sp-icons__skype"></i>
              <a href="skype:<?= $mainQuestionSkype ?>?chat" class="uk-panel-list__link">
                <?= $mainQuestionSkype; ?>
              </a>
            </li>
          <?php endif; ?>
          <?php if($mainQuestionIcq): ?>
            <li class="uk-panel-list__item">
              <i class="sp-icons sp-icons__icq"></i>
              <a href="icq:<?= $mainQuestionIcq; ?>" class="uk-panel-list__link">
                <?= $mainQuestionIcq; ?>
              </a>
            </li>
          <?php endif; ?>
          <?php if ($mainQuestionEmail): ?>
            <li class="uk-panel-list__item">
              <i class="sp-icons sp-icons__email"></i>
              <a href="mailto:<?= $mainQuestionEmail; ?>" class="uk-panel-list__link">
                <?= $mainQuestionEmail; ?>
              </a>
            </li>
          <?php endif; ?>
          <?php if ($mainQuestionTelegram): ?>
            <li class="uk-panel-list__item">
              <i class="sp-icons sp-icons__telegram"></i>
              <a target="_blank" href="https://telegram.me/<?= $mainQuestionTelegram; ?>" class="uk-panel-list__link">
                <?= $mainQuestionTelegram; ?>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="sup-footer__button-reg">
        <button class="js-goreg-form sup-footer__reg-button uk-button uk-button-primary uk-button-large uk-button-large_footer uk-align-center">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'register_button_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>
        </button>
      </div>
      <div class="sup-footer__wm sup-footer__contacts uk-flex text-r">
        <div class="cont_r">
          <h3 class="uk-panel-title "><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'tech_support_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></h3>
          <ul class="uk-panel-list uk-panel-list_footer uk-text-bold ">
            <?php if($techSupportSkype): ?>
              <li class="uk-panel-list__item">
                <i class="sp-icons sp-icons__skype"></i>
                <a href="skype:<?= $techSupportSkype; ?>?chat" class="uk-panel-list__link">
                  <?= $techSupportSkype; ?>
                </a>
              </li>
            <?php endif; ?>
            <?php if($techSupportIcq): ?>
              <li class="uk-panel-list__item">
                <i class="sp-icons sp-icons__icq"></i>
                <a href="icq:<?= $techSupportIcq; ?>" class="uk-panel-list__link">
                  <?= $techSupportIcq; ?>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

      </div>
    </div>
  </div>
  <a id="totop" class="header" data-uk-smooth-scroll></a>
  <div class="sub-footer">
    <div class="uk-container uk-container-center">
      <div class="sub-footer__inner  uk-flex uk-flex-middle">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/logo_img_footer'
        ])->getResult(); ?>
        <div class="uk-width-medium-1-3">
          <div class="copyright uk-text-center uk-text-bold">
            &copy;  <?= date("Y") ?> <?= $modulePartners->getFooterCopyright() ?>
          </div>
        </div>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/pay_through'
        ])->getResult(); ?>

      </div>
    </div>
  </div>
</footer>


<div id="reg-modal" class="uk-modal">
  <div class="modal uk-modal-dialog">
    <a class="modal__close uk-modal-close">&#10006;</a>
    <?= $moduleUser->api('signupForm')->getResult(); ?>
  </div>
</div>


<div id="login-modal" class="uk-modal">
  <div class="modal uk-modal-dialog">
    <a class="modal__close uk-modal-close">&#10006;</a>
    <?= $moduleUser->api('loginForm')->getResult(); ?>

  </div>
</div>

<div id="remember-modal" class="uk-modal">
  <div class="modal uk-modal-dialog">
    <a class="modal__close uk-modal-close">&#10006;</a>
    <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
  </div>
</div>

<div id="reset-modal" class="uk-modal">
  <div class="modal uk-modal-dialog">
    <a class="modal__close uk-modal-close">&#10006;</a>
    <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
  </div>
</div>

<div id="success-modal" class="uk-modal reg-success" >
  <div class="modal uk-modal-dialog">
    <a class="modal__close uk-modal-close">&#10006;</a>
    <div class="title title_mini title_modal success-title"></div>
    <div class="text-center">
      <h3 class="success-subtitle"></h3>
      <div class="success-action"></div>
      <i><img src="/img/wapcash/checked.svg" alt=""></i>
      <div class="success-message"></div>
      <button type="submit" class="btn custom-submit uk-button uk-modal-close uk-button-large uk-button-success">Ок</button>
    </div>

    <div class="modal__footer">
    </div>

  </div>
</div>

<div id="fail-modal" class="uk-modal reg-success error-msg" aria-hidden="false">
  <div class="modal uk-modal-dialog">
    <a class="modal__close uk-modal-close">✖</a>
    <div class="title title_mini title_modal fail-title"></div>
    <div class="text-center">
      <h3 class="fail-subtitle"></h3>
      <i></i>
      <div class="success-message fail-message"></div>
      <button type="submit" class="btn custom-submit uk-button uk-modal-close uk-button-large uk-button-success">Ок</button>
    </div>

    <div class="modal__footer">
    </div>

  </div>
</div>
</body>