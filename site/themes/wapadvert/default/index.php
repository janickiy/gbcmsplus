<?php
use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapadvert\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use mcms\common\SystemLanguage;

LandingAsset::register($this);
FormAsset::register($this);
/* @var mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');
$moduleUser = Yii::$app->getModule('users');

$currentLang = (new SystemLanguage())->getCurrent();
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
<body data-lang="<?= $currentLang ?>">

<header>
  <div class="uk-container_header uk-container uk-container-center">
    <div class="head-column uk-grid uk-grid-collapse">
      <h1 class="logo-title uk-width-medium-1-4 uk-width-large-1-6 uk-flex uk-flex-middle">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/logo_img'
        ])->getResult(); ?>
      </h1>

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'accept_countries',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/accept_countries'
      ])->getResult(); ?>


      <div class="head-column__main uk-width-medium-1-2 uk-width-large-2-6 uk-flex uk-flex-column">
        <div class="login-block uk-flex-item-auto">
          <div class="title title_mini only-not-s">
            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'login_button_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>:
          </div>
          <h2 class="title title_mobile only-s">
            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'authorization_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></h2>
          <?= $moduleUser->api('loginForm')->getResult(); ?>
        </div>
        <a href="#reg-modal" class="link register-modal-button link_registration only-s" data-uk-modal>
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'register_button_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></a>
      </div>


      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'lang_select',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/lang_select'
      ])->getResult(); ?>

    </div>


    <div id="reg-modal" class="uk-modal">
      <div class="modal uk-modal-dialog">
        <a class="modal__close uk-modal-close uk-close"></a>
        <?= $moduleUser->api('signupForm')->getResult(); ?>
      </div>
    </div>


    <div id="remember-modal" class="uk-modal">
      <div class="modal modal_mini uk-modal-dialog">
        <a class="modal__close uk-modal-close uk-close"></a>
        <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
      </div>
    </div>


    <div id="reset-modal" class="uk-modal">
      <div class="modal modal_mini uk-modal-dialog">
        <a class="modal__close uk-modal-close uk-close"></a>
        <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
      </div>
    </div>

    <div id="success-modal" class="uk-modal reg-success">
      <div class="modal modal_mini uk-modal-dialog">

        <div class="title title_mini title_modal"><span class="success-title"></span> <a class="modal__close uk-modal-close uk-close"></a></div>
        <div class="text-center">
          <h3 class="success-subtitle"></h3>
          <div class="success-action"></div>
          <i><img src="/img/wapadvert/checked.svg" alt=""></i>
          <div class="success-message"></div>
          <button type="submit" class="btn custom-submit uk-button uk-modal-close uk-button-large uk-button-success">Ок</button>
        </div>
      </div>
    </div>

    <div id="fail-modal" class="uk-modal reg-success error-msg" aria-hidden="false">
      <div class="modal modal_mini uk-modal-dialog">

        <div class="title title_mini title_modal"><span class="fail-title"></span> <a class="modal__close uk-modal-close uk-close"></a></div>
        <div class="text-center">
          <h3 class="fail-subtitle"></h3>
          <div class="success-action"></div>
          <i><img src="img/checked.svg" alt=""></i>
          <div class="success-message fail-message"></div>
          <button type="submit" class="btn custom-submit uk-button uk-modal-close uk-button-large uk-button-success">Ок</button>
        </div>
      </div>
    </div>

  </div>
</header>

<main class="only-not-s">
  <div class="how-wrap">
    <div class="uk-container uk-container-center">
      <h2 class="title title_big"><span class="title__border">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'how_it_works_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></span></h2>
    </div>

    <div class="how-block">
      <div class="uk-container uk-container-center">
        <div class="uk-flex">
          <div class="how-list-block">
            <div class="how-list-block__list uk-grid uk-grid-width-small-1-2 uk-grid-width-medium-1-4 uk-grid-collapse">
              <div class="uk-flex uk-flex-column how-list-block__column">
                <div class="how-icon-block how-icon-block_first how-icon-block_red">
                  <div class="sp-icons-wrap">
                    <i class="sp-icons sp-icons__code" style="background-image: url(<?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'code_image',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/how_img'
                    ])->getResult(); ?>); background-position: 0 0;" data-uk-scrollspy="{cls:'uk-animation-scale-up', delay:100}"></i>
                  </div>
                </div>
                <div class="how-text uk-flex uk-flex-item-auto uk-flex-middle"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'code_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?></div>
              </div>
              <div class="uk-flex uk-flex-column how-list-block__column">
                <div class="how-icon-block how-icon-block_purple">
                  <div class="sp-icons-wrap">
                    <i class="sp-icons sp-icons__map" style="background-image: url(<?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'map_image',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/how_img'
                    ])->getResult(); ?>); background-position: 0 0;" data-uk-scrollspy="{cls:'uk-animation-scale-up', delay:200}"></i>
                  </div>
                </div>
                <div class="how-text uk-flex uk-flex-item-auto uk-flex-middle"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'map_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?></div>
              </div>
              <div class="uk-flex uk-flex-column how-list-block__column">
                <div class="how-icon-block how-icon-block_blue">
                  <div class="sp-icons-wrap">
                    <i class="sp-icons sp-icons__click" style="background-image: url(<?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'click_image',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/how_img'
                    ])->getResult(); ?>); background-position: 0 0;" data-uk-scrollspy="{cls:'uk-animation-scale-up', delay:300}"></i>
                  </div>
                </div>
                <div class="how-text uk-flex uk-flex-item-auto uk-flex-middle"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'click_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?></div>
              </div>
              <div class="uk-flex uk-flex-column how-list-block__column">
                <div class="how-icon-block how-icon-block_last how-icon-block_green">
                  <div class="sp-icons-wrap">
                    <i class="sp-icons sp-icons__money" style="background-image: url(<?= $pagesModule->api('pagesWidget', [
                      'categoryCode' => 'common',
                      'pageCode' => 'landing',
                      'propCode' => 'money_image',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/how_img'
                    ])->getResult(); ?>); background-position: 0 0;" data-uk-scrollspy="{cls:'uk-animation-scale-up', delay:400}"></i>
                  </div>
                </div>
                <div class="how-text uk-flex uk-flex-item-auto uk-flex-middle"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'money_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?></div>
              </div>
            </div>
          </div>

          <div class="how-phone-block uk-position-relative uk-visible-large">
            <div class="how-phone-block__phone" style="background-image: url(<?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'phone_image',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/how_img'
            ])->getResult(); ?>);">
              <div class="balance__add"><?= $pagesModule->api('pagesWidget', [
                  'categoryCode' => 'common',
                  'pageCode' => 'landing',
                  'propCode' => 'balance_credited_to_title',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?> +<i>600</i> <div class="balance-block__cur">₶</div></div>
              <div class="timer_top">17:20</div>
              <div class="how-phone-block__date-block">
                <div class="how-phone-block__date uk-flex uk-flex-center">
                  <div class="js-date-num how-phone-block__animate">1</div><span>&nbsp;</span>
                  <div class="r_month">января</div>
                </div>
                <div class="how-phone-block__time uk-flex uk-flex-center">
                  <div class="js-date-day how-phone-block__animate">Пн</div>, <span class="r_time">17:20</span>
                </div>
              </div>
              <div class="how-phone-block__balance-block balance-block uk-flex uk-flex-middle">

                <div class="balance-block__word">
                  <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'propCode' => 'balance_title',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?>
                </div>
                <div class="balance-block__value uk-flex-item-auto">
                  <div class=" balance_exp uk-animation-1">-20<div class="balance-block__cur">₶</div></div>
                  <div class="uk-flex uk-flex-center">
                    <div class="uk-flex uk-flex-center">
                      <div class="js-balance-num how-phone-block__animate">300</div></div>
                    <div class="balance-block__cur">₶</div>
                  </div>
                  <i class="balance-block__icon sp-icons sp-icons__update"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="uk-container uk-container-center">
      <div class="how-note">
        <?= $pagesModule->api('pages', ['conditions' => ['code' => 'landing']])->setResultTypeDataProvider()->getResult()->getModels()[0]->text; ?>
      </div>
    </div>
  </div>

  <?= $pagesModule->api('pagesWidget', [
    'categoryCode' => 'advantages',
    'viewBasePath' => $viewBasePath,
    'view' => 'widgets/advantages'
  ])->getResult(); ?>

</main>

<footer class="only-not-s">
  <div class="uk-container uk-container-center">
    <div class="uk-grid uk-grid-collapse">
      <div class="uk-width-large-1-3 uk-flex uk-flex-item-auto">
        <?php
          $footerTechSupportSkype = $modulePartners->getFooterMainQuestionSkype();
          $footerTechSupportEmail = $modulePartners->getFooterMainQuestionEmail();

          if ($footerTechSupportSkype || $footerTechSupportEmail):
        ?>
          <div class="support-block">
            <div class="title title_mini title_footer">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'support_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?>
            </div>
            <?php if ($footerTechSupportSkype): ?>
              <a href="skype:<?= $footerTechSupportSkype; ?>" class="support-block__item uk-flex uk-flex-middle link link_decor-none">
                <i class="sp-icons sp-icons__skype"></i>
                <span class="link"><?= $footerTechSupportSkype; ?></span>
              </a>
            <?php endif; ?>
            <?php if ($footerTechSupportEmail): ?>
              <a href="mailto:<?= $footerTechSupportEmail; ?>" class="support-block__item uk-flex uk-flex-middle link link_decor-none">
                <i class="sp-icons sp-icons__mail"></i>
                <span class="link"><?= $footerTechSupportEmail; ?></span>
              </a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="uk-container uk-container-center uk-width-large-1-3 uk-flex uk-flex-center uk-flex-middle">
        <a href="#reg-modal" class="button button_reg uk-button" data-uk-modal=""><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'sign_up_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></a>
      </div>
      <div class="uk-width-large-1-3 uk-flex uk-flex-right">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'partners',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/partners'
        ])->getResult(); ?>


      </div>
    </div>
  </div>
</footer>
</body>