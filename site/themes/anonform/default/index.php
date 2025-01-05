<?php

use mcms\partners\assets\landings\wapclick\LandingAsset;
use mcms\partners\assets\landings\FormAsset;

/** @var $pagesModule \mcms\common\module\Module */

LandingAsset::register($this);
FormAsset::register($this);

$moduleUser = Yii::$app->getModule('users');

$viewBasePath = '/' . $this->context->id . '/';

$this->title = "Партнерская программа";

?>

<body>

<div class="section">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'login_modal_title',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>
                </h4>
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
                        <input id="login-username" class="form-control" name="Login[username]" type="text"
                               placeholder="<?= Yii::_t('users.login.username_email') ?>">
                    </div>
                    <div class="form-group input-password">
                        <input class="form-control" name="Login[password]" type="password"
                               placeholder="<?= Yii::_t('users.login.password') ?>">
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
                    <input type="submit" class="btn" value="<?= Yii::_t('users.login.sign_in'); ?>"/>

                </form>

            </div>

            <div class="modal-footer">

                <span>
                    <?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'common',
                        'pageCode' => 'landing',
                        'propCode' => 'need_account_text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/prop_multivalue'
                    ])->getResult(); ?>
                </span>

                <a href="" data-toggle="modal" data-target="#registration" class="change-modal">
                    <i class="icon-registration"></i>
                    <span>
                        <?= $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'common',
                            'pageCode' => 'landing',
                            'propCode' => 'registration_title',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/prop_multivalue'
                        ])->getResult(); ?>
                    </span>
                </a>

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
                <h4 class="modal-title" id="myModalLabel"><i
                            class="icon-registration"></i><?= $pagesModule->api('pagesWidget', [
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
                <a href="" data-dismiss="modal" aria-label="Close" class="go_back change-modal"><i
                            class="icon-arrow_left"></i></a>
                <h4 class="modal-title" id="myModalLabel"><i
                            class="icon-unlock"></i><?= $pagesModule->api('pagesWidget', [
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
                <a href="" data-modal="registration" class="change-modal"><i
                            class="icon-login"></i><span><?= $pagesModule->api('pagesWidget', [
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
                <a href="" data-dismiss="modal" aria-label="Close" class="go_back change-modal"><i
                            class="icon-arrow_left"></i></a>
                <h4 class="modal-title" id="myModalLabel"><i
                            class="icon-unlock"></i><?= $pagesModule->api('pagesWidget', [
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
<div class="modal fade actions error-msg" id="fail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title fail-title" id="myModalLabel">Error</h4>
            </div>
            <div class="modal-body">
                <div class="success_message">
                    <div class="mess_title"><img src="/img/wapclick/icon_cancel_error.svg" alt=""> <span
                                class="fail-subtitle"></span></div>
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
