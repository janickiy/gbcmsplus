<?php
use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapclick\LandingAsset;
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
if ($mainQuestionTelegram) {
  $mainQuestionTelegram = $mainQuestionTelegram[0] === '@'
    ? substr($mainQuestionTelegram, 1)
    : $mainQuestionTelegram;
}
$techSupportSkype = ArrayHelper::getValue($contactValues, 'techSupport.skype');
$techSupportIcq = ArrayHelper::getValue($contactValues, 'techSupport.icq');

$viewBasePath = '/' . $this->context->id . '/';

$this->registerMetaTag(['name' => 'telderi', 'content' => '087522c22eb552c60fd2a6e92fb5f18d']);

if ($favicon = $modulePartners->api('getFavicon')->getResult())
  $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);

$modulePartners->api('publication', ['view' => $this])->registerImage();

$this->title = $this->title instanceof LangAttribute && $this->title->getCurrentLangValue()
  ? $this->title
  : $pagesModule->api('pagesWidget', [
 'categoryCode' => 'common',
 'pageCode' => 'landing',
 'fieldCode' => 'name',
 'viewBasePath' => $viewBasePath,
 'view' => 'widgets/field_value'
])->getResult();
?>

<body>

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
  (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
    m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
  (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

  ym(27763632, "init", {
    clickmap:true,
    trackLinks:true,
    accurateTrackBounce:true,
    webvisor:true
  });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/27763632" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->


<div class="header">
  <div class="header-bg">
    <div class="nav">
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-5 col-sm-8 col-xs-12 pull-right header-links">
            <a href="" data-toggle="modal" data-target="#registration" class="reg-link register-modal-button"><i class="icon-registration"></i><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'registration_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></a>
            <a href="" data-toggle="modal" data-target="#auth" class="login-link login-modal-button"><i class="icon-login"></i><?= $pagesModule->api('pagesWidget', [
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
          <div class="col-lg-8 col-md-7 col-sm-4 col-xs-12">

            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/logo_img'
            ])->getResult(); ?>

            <div class="contacts">
              <ul>
                <?php if ($mainQuestionTelegram): ?>
                  <li class="telegram">
                    <a href="https://ttttt.me/<?= $mainQuestionTelegram ?>">@<?= $mainQuestionTelegram ?></a>
                  </li>
                <?php endif;?>
                <?php if ($mainQuestionIcq): ?>
                <li class="isq">
                  <a href="icq:<?= $mainQuestionIcq ?>"><?= $mainQuestionIcq ?></a>
                </li>
                <?php endif;?>
                <?php if ($mainQuestionSkype): ?>
                <li class="skype"><a href="skype:<?= $mainQuestionSkype; ?>?chat"><?= $mainQuestionSkype ?></a></li>
                <?php endif; ?>
                <?php if ($mainQuestionEmail): ?>
                <li class="mail"><a href="mailto:<?= $mainQuestionEmail; ?>"><?= $mainQuestionEmail; ?></a></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>

        </div>
      </div>
    </div>
    <div class="title">
      <div class="container">
        <h1><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'millions_with_us_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h1>

        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'social_networks',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/social_networks'
        ])->getResult(); ?>

      </div>

    </div>
    <div class="main_bear">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/bear_img'
      ])->getResult(); ?>

      <span class="view"><i class="icon-play"></i><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'watch_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></span>
      <div class="quote"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'we_have_right_bee_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></div>
    </div>
    <div class="bee">
      <div class="bee_money bee_money_1"></div>
      <div class="bee_money bee_money_2"></div>
      <div class="bee_money bee_money_3"></div>
      <div class="bee_money bee_money_4"></div>
      <div class="bee_money bee_money_5"></div>
      <div class="bee_money bee_money_6"></div>
    </div>
    <div class="header_bottom ">
      <div class="container">
        <div class="row">
          <div class="col-sm-4 col-xs-12 hidden-xs"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'legal_content_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></div>
          <div class="col-sm-4 col-xs-12 hidden-xs"><span class="toggle_popunder"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'action_text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></span></div>
          <div class="col-sm-4 col-xs-12 hidden-xs"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'profit_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></div>
        </div>
      </div>
    </div>
    <div class="popunder">
      <a href="" class="close"><i class="icon-close"></i></a>
      <b><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'congratulations_you_are_right_realized_title',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></b>
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'just_pressing_button_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></span>
    </div>
  </div>
</div>
<div class="country">
  <div class="container">
    <div class="country_list">
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'accept_traffic_from_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>:</span>

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'accept_traffic',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/accept_traffic'
      ])->getResult(); ?>

    </div>
    <div class="country_list">
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'debiting_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?> :</span>

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'pay_types',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/pay_types'
      ])->getResult(); ?>

    </div>
  </div>
</div>
<div class="section_1">
  <div class="container">
    <h2><b><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'wap_click_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?> —</b> <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'new_innovative_technology_text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></h2>
    <div class="subtitle"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'subscriber_just_press_text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?> <br><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'receives_high_quality_content_text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></div>
    <a href=".section_3" class="how_it_works scroll_to_box"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'how_it_work_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></a>
    <div class="row">

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'who_are_we',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/who_are_we'
      ])->getResult(); ?>

    </div>

    <div class="advantage">

      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'offer',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/offer'
      ])->getResult(); ?>

    </div>
    <a href="" data-toggle="modal" data-target="#registration" class="reg_link"><i class="icon-registration"></i><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'registration_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></a>
  </div>
</div>
<div class="section_2">
  <div class="container">
    <div class="inline-box">
      <span class="warn_ico"></span>
      <span><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'we_are_good_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?><br><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'we_use_legal_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></span>
    </div>

  </div>
</div>
<div class="section_3">
  <h2><?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'landing',
      'propCode' => 'how_it_work_title',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/prop_multivalue'
    ])->getResult(); ?></h2>
  <div class="container">
    <div class="h_it_work">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'how_it_works',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/how_it_works'
      ])->getResult(); ?>
    </div>

    <div class="row">
      <div class="col-md-4 col-sm-6 hidden-sm">
        <div class="wiki">

          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'faq_wap_wiki',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/faq_wap_wiki'
          ])->getResult(); ?>

          <a href="<?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'faq_wapwiki_url',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>" class="wiki_link"><i class="icon-wiki"></i><span><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'faq_wap_wiki_text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?></span></a>
        </div>
      </div>
      <div class="col-md-4 col-sm-12 legal">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/like_img'
        ])->getResult(); ?>

        <span><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'all_perfectly_legal_and_agreed_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></span>
      </div>
      <div class="col-md-4 col-sm-12">
        <a href="" data-toggle="modal" data-target="#registration" class="reg_link"><i class="icon-registration"></i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'registration_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></a>
      </div>
    </div>
  </div>

</div>
<div class="section_4">
  <div class="container border">
    <h2><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'we_are_on_forums_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></h2>
    <div class="row">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'we_are_on_forums',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/we_are_on_forums'
      ])->getResult(); ?>
    </div>
  </div>
  <div class="container">
    <h2><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'about_us_title',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></h2>
    <div class="links_about">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'reviews',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/reviews'
        ])->getResult(); ?>
    </div>

  </div>
</div>
<div class="footer">
  <div class="scroll_up">
    <a href=""><i class="icon-arrow_up"></i><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'landing',
        'propCode' => 'top',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></a>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-sm-6 col-xs-12">
        <h4><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'сontact_us_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h4>

        <div class="contacts">
          <ul>
            <?php if ($mainQuestionTelegram): ?>
              <li class="telegram">
                <a href="https://ttttt.me/<?= $mainQuestionTelegram ?>">@<?= $mainQuestionTelegram ?></a>
              </li>
            <?php endif;?>
            <?php if ($mainQuestionIcq): ?>
              <li class="isq">
                <a href="icq:<?= $mainQuestionIcq ?>"><?= $mainQuestionIcq ?></a>
              </li>
            <?php endif;?>
            <?php if ($mainQuestionSkype): ?>
              <li class="skype"><a href="skype:<?= $mainQuestionSkype; ?>?chat"><?= $mainQuestionSkype ?></a></li>
            <?php endif; ?>
            <?php if ($mainQuestionEmail): ?>
              <li class="mail"><a href="mailto:<?= $mainQuestionEmail; ?>"><?= $mainQuestionEmail; ?></a></li>
            <?php endif; ?>
          </ul>
        </div>

      </div>
      <div class="col-sm-6 col-xs-12 align-right">
        <h4><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'social_networks_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h4>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'social_networks',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/social_networks'
        ])->getResult(); ?>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6 col-xs-12">
        <div class="logo">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/footer_logo_img'
          ])->getResult(); ?>

          <span>&copy; <?= date("Y") ?> <?= $modulePartners->getFooterCopyright() ?></span>
        </div>
      </div>
      <div class="col-sm-6 col-xs-12 align-right">
        <div class="payments">
          <div class="payments_logo">
            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'webmoney',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>
          </div>
          <a href="<?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'notice_url',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?>">
            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'liability_notice_text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade actions" id="registration" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i class="icon-close"></i>
        </button>
        <h4 class="modal-title" id="myModalLabel"><i class="icon-registration"></i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'registration_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h4>
      </div>
      <div class="modal-body">
        <?= $moduleUser->api('signupForm')->getResult(); ?>
      </div>
      <div class="modal-footer">
        <span><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'already_have_account_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></span>
        <a href="" data-modal="auth" class="change-modal"><i class="icon-login"></i><span><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'login_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></span></a>
      </div>
    </div>
  </div>
</div>


<div class="modal fade actions" id="auth" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i class="icon-close"></i>
        </button>
        <h4 class="modal-title" id="myModalLabel"><i class="icon-login"></i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'login_modal_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h4>
      </div>
      <div class="modal-body">
        <?= $moduleUser->api('loginForm')->getResult(); ?>
        <form class="old_panel" id="login-form" action="<?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'landing',
          'propCode' => 'old_panel_url',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>" autocomplete="off">
          <div class="form-group input-email">
            <input id="login-username" class="form-control" name="Login[username]" type="text" placeholder="<?= Yii::_t('users.login.username_email')?>">
          </div>
          <div class="form-group input-password">
            <input class="form-control" name="Login[password]" type="password" placeholder="<?= Yii::_t('users.login.password')?>">
          </div>
          <div class="pass_remember">
            <div class="row">
              <div class="col-xs-6">
              </div>
              <div class="col-xs-6 text-right">
                <div class="form-group checkbox">
                  <input type="checkbox" name="Login[rememberMe]" id="remember" value="1" checked>
                  <label for="remember"><?= Yii::_t('users.login.rememberMe') ?></label>
                </div>
              </div>
            </div>
          </div>
          <input type="hidden" name="ajax" value="login">
          <input type="submit" class="btn" value="<?= Yii::_t('users.login.sign_in'); ?>" />
        </form>
      </div>
      <div class="modal-footer">
        <span><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'need_account_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></span>
        <a href="" data-modal="registration" class="change-modal"><i class="icon-login"></i><span><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'registration_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></span></a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade actions" id="recovery" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i class="icon-close"></i>
        </button>
        <a href="" data-modal="auth" class="go_back change-modal"><i class="icon-arrow_left"></i></a>
        <h4 class="modal-title" id="myModalLabel"><i class="icon-unlock"></i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'recovery_modal_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h4>
      </div>
      <div class="modal-body">
        <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>

      </div>
      <div class="modal-footer">
        <span><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'need_account_text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></span>
        <a href="" data-modal="registration" class="change-modal"><i class="icon-login"></i><span><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'common',
              'pageCode' => 'landing',
              'propCode' => 'registration_title',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?></span></a>
      </div>
    </div>
  </div>
</div>

<a id="reset-modal-button" data-toggle="modal" data-target="#modal-form_reset"></a>
<div class="modal fade actions" id="modal-form_reset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i class="icon-close"></i>
        </button>
        <a href="" data-modal="auth" class="go_back change-modal"><i class="icon-arrow_left"></i></a>
        <h4 class="modal-title" id="myModalLabel"><i class="icon-unlock"></i><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'landing',
            'propCode' => 'reset_modal_title',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/prop_multivalue'
          ])->getResult(); ?></h4>
      </div>
      <div class="modal-body">
        <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
      </div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>


<a id="success-modal-button" data-toggle="modal" data-target="#success-modal"></a>
<div class="modal fade actions" id="success-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <i class="icon-close"></i>
        </button>
        <h4 class="modal-title  success-title" id="myModalLabel"><i class="icon-login"></i></h4>
      </div>
      <div class="modal-body">
        <div class="success_message">
          <div class="mess_title"><i class="icon-success"></i><span class="success-action"></span></div>
          <span class="success-message"></span>
        </div>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<a id="fail-modal-button" data-toggle="modal" data-target="#fail-modal"></a>
<div class="modal fade actions error-msg" id="fail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title fail-title" id="myModalLabel">Error</h4>
      </div>
      <div class="modal-body">
        <div class="success_message">
          <div class="mess_title"> <img src="/img/wapclick/icon_cancel_error.svg" alt=""> <span class="fail-subtitle"></span></div>
          <span class="fail-message"></span>
          <span><b></b></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" data-dismiss="modal">ОК</button>
      </div>
    </div>
  </div>
</div>
</body>
