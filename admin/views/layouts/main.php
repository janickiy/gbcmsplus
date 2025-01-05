<?php

/* @var $this \yii\web\View */

/* @var $content string */

use admin\assets\AppAsset;
use common\modules\changelog\models\UserChangelogSetting;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Menu;
use mcms\common\helpers\SmartMenuHelper;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\user\models\User;
use mcms\common\helpers\Html;
use rgk\theme\smartadmin\widgets\menu\SmartAdminMenu;
use rgk\theme\smartadmin\widgets\menu\SmartAdminTabs;
use yii\bootstrap\Html as BsHtml;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\Html as YiiHtml;

AppAsset::register($this);
$this->registerJs('pageSetUp();');

/** @var \mcms\partners\Module $modulePartners */
$modulePartners = Yii::$app->getModule('partners');

$newChangelog = UserChangelogSetting::currentUserHasUnreadedChangeLog();

AjaxButtons::widget();
$smartadminTabs = SmartAdminTabs::widget(['items' => Yii::$app->params['tabs']]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <?= Html::csrfMetaTags() ?>

    <?php $partnersModule = Yii::$app->getModule('partners'); ?>
    <?php if ($favicon = $partnersModule->api('getFavicon')->getResult()): ?>
        <link rel="icon" type="<?= $partnersModule->api('getFavicon')->getIconMimeType() ?>" href="<?= $favicon; ?>"/>
        <link rel="apple-touch-icon" href="<?= $favicon; ?>"/>
    <?php endif; ?>

    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="<?= ArrayHelper::getValue($this->params, 'bodyClass', '') ?>">
<?php $this->beginBody() ?>

<?= ercling\loadingindicator\Indicator::widget(['color' => 'blue', 'theme' => 'barber-shop']); ?>

<div class="wrapper">
    <header id="header">
        <div id="logo-group" class="logo-group">
            <?php $logoUrl = $modulePartners->api('getAdminPanelLogoImage')->getResult(); ?>
            <?php if (empty($logoUrl)) $logoUrl = $modulePartners->api('getLogoImage')->getResult(); ?>
            <span id="logo" class="logo"><a href="/admin">
        <?= $_SERVER['HTTP_HOST'] == 'app.bmr.agency' ? "" : ($logoUrl ? Html::img($logoUrl) : $modulePartners->getProjectName()) ?>
      </a></span>

        </div>

        <div class="container-fluid widgets-form-wrapper">
            <?php if (isset($this->blocks['headerData'])): ?>
                <?= $this->blocks['headerData'] ?>
            <?php endif; ?>
        </div>

        <div class="pull-right">
            <div id="hide-menu" class="btn-header pull-right">
                <span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"
                          class="cursor-pointer"><i class="fa fa-reorder"></i></a> </span>
            </div>
            <div id="logout" class="btn-header pull-right">
                <?php
                $logoutUrl = Yii::$app->session->get(User::SESSION_BACK_IDENTITY_ID) ?
                    ['/users/users/logout-by-user'] :
                    ['/users/site/logout/'];
                $logoutTitle = Yii::$app->session->get(User::SESSION_BACK_IDENTITY_ID) ?
                    Yii::_t('app.common.return') :
                    Yii::_t('app.common.logout');
                ?>
                <span> <a href="<?= Url::toRoute($logoutUrl) ?>" title="<?= $logoutTitle ?>" class="cursor-pointer">
            <i class="fa fa-sign-out"></i></a> </span>
            </div>
            <div id="changelog-link" class="btn-header pull-right">
        <span>
          <?php if (Yii::$app->user->can('ChangelogRead')) { ?>
              <?= YiiHtml::a(
                  '<i class="fa fa-info"></i>',
                  ['/changelog/default/index'],
                  ['title' => 'Changelog', 'class' => 'cursor-pointer' . ($newChangelog ? ' blue-link-button' : '')]
              ) ?>
          <?php } ?>
        </span>
            </div>
            <div id="modules-link"
                 class="btn-header pull-right <?= Yii::$app->user->can('AppBackendDefaultSettings') ? '' : 'hidden' ?>">
        <span> <?= BsHtml::a(
                '<i class="fa fa-gear"></i>',
                ['/settings/'],
                ['title' => Yii::_t('app.common.Settings'), 'class' => 'cursor-pointer']
            ) ?> </span>
            </div>
            <?php Pjax::begin(['id' => 'profile-summary', 'options' => ['class' => 'login-info pull-right']]) ?>
            <span>
          <?= Html::hasUrlAccess(Yii::$app->getModule('users')->api('userLink')->buildProfileEditLink())
              ? Modal::widget([
                  'id' => 'profile-summary-modal',
                  'toggleButtonOptions' => [
                      'tag' => 'a',
                      'id' => 'show-shortcut',
                      'label' =>
                          Html::tag('img', null, [
                              'src' => '/img/avatar.png',
                              'alt' => Yii::$app->user->identity->username,
                              'class' => 'online',
                          ])
                          . Html::tag('b', Yii::$app->user->identity->username, ['class' => 'hidden-sm hidden-xs']),
                  ],
                  'url' => Yii::$app->getModule('users')->api('userLink')->buildProfileEditLink(),
              ])
              : BsHtml::a(Html::tag('img', null, [
                      'src' => '/img/avatar.png',
                      'alt' => Yii::$app->user->identity->username,
                      'class' => 'online',
                  ]) . Html::tag('span', Yii::$app->user->identity->usename)) ?>
        </span>
            <?php Pjax::end() ?>
        </div>
    </header>

    <div class="content-wrapper">
        <aside id="left-panel">
            <nav>
                <?php if (Yii::$app->request->get('oldMenu')) { ?>
                    <?= mcms\common\helpers\MainMenu::widget([
                        'activateParents' => true,
                        'options' => ['class' => false],
                        'items' => Menu::getItems(
                            Yii::$app->getModule('notifications')
                                ? Yii::$app->getModule('notifications')->api('getBrowserNotificationCount', [
                                'user_id' => Yii::$app->user->id,
                            ])->getResult()
                                : false
                        ),
                        'linkTemplate' => '<a href="{url}">{label}</a>',
                        'encodeLabels' => false,
                    ]) ?>
                <?php } else { ?>
                    <?= isset(Yii::$app->mainMenu) ? Yii::$app->mainMenu->render() : SmartAdminMenu::widget([
                        'items' => SmartMenuHelper::format(Yii::$app->params['menu']),
                        'tabs' => Yii::$app->params['tabs'],
                    ]) ?>
                <?php } ?>
            </nav>
            <!--<span class="minifyme" data-action="minifyMenu"> <i class="icon-nav-open"></i> </span>-->
        </aside>

        <div class="main-wrapper">
            <div id="main" role="main">
                <div class="actions-wrapper">
                    <?php if (
                        $smartadminTabs ||
                        (isset($this->blocks['actions']) && !empty($this->blocks['actions'])) ||
                        (isset($this->blocks['subHeader']) && !empty($this->blocks['subHeader'])) ||
                        (isset($this->blocks['info']) && !empty($this->blocks['info']))
                    ): ?>
                        <div id="ribbon">
                            <?php if (isset($this->blocks['subHeader']) && $this->blocks['subHeader']): ?>
                                <div class="ribbon-header"><?= $this->blocks['subHeader'] ?></div>

                            <?php endif; ?>
                            <?= $smartadminTabs ?>
                        </div>
                    <?php endif; ?>

                    <div class="actions-buttons">
                        <div class="btn-group btn-group-xs pull-right">
                            <?php if (isset($this->blocks['actions'])): ?>
                                <?php echo $this->blocks['actions'] ?>
                            <?php endif; ?>
                        </div>
                        <span class="info">
              <?php if (isset($this->blocks['info'])): ?>
                  <?php echo $this->blocks['info'] ?>
              <?php endif; ?>
            </span>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div id="content" class="<?= (Yii::$app->request->url === '/admin/' ? 'main' : '') ?>">
                    <?php
                    $this->render('@app/views/layouts/flash.php');
                    ?>
                    <?= $content ?>
                </div>
            </div>
            <?php $footerContactValues = Yii::$app->getModule('partners')->getFooterContactValues(); ?>
            <div class="page-footer clearfix">
                <?php if ($_SERVER['HTTP_HOST'] != 'app.bmr.agency') { ?>
                    <?php if ($footerCopyright = ArrayHelper::getValue($footerContactValues, 'copyright')) : ?>
                        <div class="footer-link pull-left">
                            <?php if (Yii::$app->user->can('ChangelogRead')) { ?>
                                <?= YiiHtml::a(
                                    Html::tag('span', date('Y') . ' ' . $footerCopyright, ['class' => 'copyright']),
                                    ['/changelog/default/index']
                                ) ?>
                            <?php } ?>
                        </div>
                    <?php endif; ?>
                    <div class="footer-company pull-right">
                        <?php /*
            <?= Html::img('@web/img/footer-logo.png', [
            'alt' => Yii::_t('app.common.rgk_engine'),
            'class' => 'footer-company__logo',
            ]) ?>
            */ ?>
                        <span class="footer-company__caption">Wap.Click</span>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php if (isset($this->blocks['modals'])): ?>
    <div id="modals-wrapper">
        <?php echo $this->blocks['modals'] ?>
    </div>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
