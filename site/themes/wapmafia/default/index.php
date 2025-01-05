<?php
/**
 * @var \mcms\common\module\Module $pagesModule
 */

use mcms\common\multilang\LangAttribute;
use mcms\partners\assets\landings\wapmafia\LandingAsset;
use mcms\partners\assets\landings\FormAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

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

<header>
    <div class="container">
        <div class="row">
            <div class="col-xs-3">
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/logo_img'
                ])->getResult(); ?>
            </div>
            <div class="col-xs-7 pl_0">
                <ul class="header__contacts header__contacts_l">
                    <?php if ($mainQuestionEmail): ?>
                        <li class="link_2"><a href="mailto:<?= $mainQuestionEmail ?>"><?= $mainQuestionEmail ?></a></li>
                    <?php endif ?>
                    <?php if ($mainQuestionSkype): ?>
                        <li class="link_4"><a href="skype:<?= $mainQuestionSkype ?>"><?= $mainQuestionSkype ?></a></li>
                        <br>
                    <?php endif ?>
                    <?php if ($mainQuestionIcq): ?>
                        <li class="link_3 link_icq"><a href="icq:<?= $mainQuestionIcq ?>"><?= $mainQuestionIcq ?></a>
                        </li>
                    <?php endif ?>
                </ul>
                <ul class="header__contacts header__contacts_r">
                    <?php if ($techSupportEmail): ?>
                        <li class="link_2"><a href="mailto:<?= $techSupportEmail ?>"><?= $techSupportEmail ?></a></li>
                        <br>
                    <?php endif ?>
                    <?php if ($techSupportIcq): ?>
                        <li class="link_3"><a href="icq:<?= $techSupportIcq ?>"><?= $techSupportIcq ?></a></li>
                    <?php endif ?>
                </ul>
            </div>
            <div class="col-xs-2">
                <a href="#" class="pull-right login red_gradient button_decoration">
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'login_button',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>
                </a>
                <div class="login_box">
                    <div class="login_box-header red_gradient button_decoration">
                        <span>
                            <?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'login_title',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult(); ?>
                        </span>
                    </div>
                    <div class="login_box-body">
                        <?= $moduleUser->api('loginForm')->getResult(); ?>
                    </div>
                    <div class="login_box-footer">
                        <span><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'login_need_account',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult(); ?>
                            <a class="open__modal" href="#registration">
                                <?= $pagesModule->api('pagesWidget', [
                                    'categoryCode' => 'common',
                                    'pageCode' => 'landing',
                                    'propCode' => 'register_button',
                                    'viewBasePath' => $viewBasePath,
                                    'view' => 'widgets/prop_multivalue'
                                ])->getResult(); ?>
                            </a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section_1">
    <div class="container">
        <div class="row">
            <div class="col-xs-7">
                <div class="video__player">
                    <iframe width="552" height="299" src="<?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'youtube_url',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>" frameborder="0" allowfullscreen=""></iframe>
                </div>
            </div>
            <div class="col-xs-5">
                <div class="famaly">
                    <div class="famaly__top">
                        <div class="span4">
                            <?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'family_icon',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/image'
                            ])->getResult(); ?>
                        </div>
                        <div class="span8">
                            <span><?= $pagesModule->api('pagesWidget', [
                                    'categoryCode' => 'common',
                                    'pageCode' => 'landing',
                                    'propCode' => 'family_title',
                                    'viewBasePath' => $viewBasePath,
                                    'view' => 'widgets/prop_multivalue'
                                ])->getResult(); ?></span>
                        </div>
                    </div>
                    <div class="famaly__bottom">
                        <a data-toggle="modal" href="#registration" class=" red_gradient button_decoration">
                            <?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'common',
                                'pageCode' => 'landing',
                                'propCode' => 'register_button',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/prop_multivalue'
                            ])->getResult(); ?>
                        </a>
                    </div>
                </div>
                <div class="traff__lang">
                    <span class="traff__lang-title"><?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'traffic_countries_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                        ])->getResult() ?></span>
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'traffic_countries',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/traffic_countries'
                    ])->getResult(); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section_2">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h2><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'recommendations_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?></h2>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3">
                <div class="don_carl">
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'recommendations_image',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/image'
                    ])->getResult() ?>
                </div>
            </div>
            <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'recommendations',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/recommendations'
            ])->getResult(); ?>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <h4><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'recommendations_subtitle',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></h4>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2">
                <span class="text__before"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'recommendations_text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section_3">
    <section class="slider">
        <h5><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'landings_title',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></h5>
        <a class="prev" onclick="boutique_ext_prev()"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'landings_button_prev',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></a>
        <a class="next" onclick="boutique_ext_next()"><?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'landing',
                'propCode' => 'landings_button_next',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
            ])->getResult() ?></a>
        <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'landings',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/landings'
        ])->getResult(); ?>
    </section>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <span class="slider__bottom"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'landings_text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></span>
            </div>
        </div>
    </div>
</section>

<section class="section_4">
    <div class="container container1">
        <div class="row">
            <div class="col-xs-3 no_pad">
                <div class="photo__box">
                    <div class="photo_box_2">
                        <?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'footer_image',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/image'
                        ])->getResult() ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-6">
                <h3><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'footer_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></h3>
                <a data-toggle="modal" href="#registration"
                   class=" red_gradient button_decoration"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'register_button',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></a>
                <span class="quote"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'footer_quote',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></span>
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'common',
                    'pageCode' => 'landing',
                    'cssClass'=> 'text_img',
                    'propCode' => 'footer_image_text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/image'
                ])->getResult() ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 footer">
                <span class="copy">&copy; <?= date("Y") ?> <?= $modulePartners->getFooterCopyright() ?></span>

                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'footer_links',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/footer_links'
                ])->getResult(); ?>
            </div>
        </div>
    </div>
</section>

<div class="overlay"></div>

<div class="modal fade" id="password__reset">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header red_gradient button_decoration">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'password_reset_request_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></h4>
            </div>
            <div class="modal-body">
                <?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="registration">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header red_gradient button_decoration">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'register_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></h4>
            </div>

            <div class="modal-body">
                <?= $moduleUser->api('signupForm')->getResult(); ?>
            </div>
        </div>
    </div>
</div>

<a id="reset-modal-button" data-toggle="modal" data-target="#modal-form_reset"></a>
<div class="modal fade" id="modal-form_reset">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header red_gradient button_decoration">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'reset_modal_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult() ?></h4>
            </div>

            <div class="modal-body">
                <?= $moduleUser->api('resetPasswordForm')->getResult(); ?>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<a id="fail-modal-button" data-toggle="modal" data-target="#fail-modal"></a>
<div class="modal fade actions error-msg" id="fail-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header red_gradient button_decoration">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title fail-title">Error</h4>
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

<a id="success-modal-button" data-toggle="modal" data-target="#success-modal"></a>
<div class="modal fade actions" id="success-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header red_gradient button_decoration">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="icon-close"></i>
                </button>
                <h4 class="modal-title success-title"><i class="icon-login"></i></h4>
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

</body>