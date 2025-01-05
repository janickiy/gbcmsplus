<?php

/* @var $this \yii\web\View */
/* @var $content string */

use mcms\common\helpers\ArrayHelper;
use mcms\pages\models\PartnerCabinetStyle;
use mcms\partners\assets\BasicAsset;
use mcms\partners\assets\PartnerCabinetStyleAsset;
use mcms\partners\components\widgets\PriceWidget;
use mcms\user\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use\yii\widgets\Menu;
use mcms\common\helpers\Link;

/** @var mcms\pages\Module $pagesModule */
$pagesModule = Yii::$app->getModule('pages');
/** @var \mcms\partners\Module $partnersModule */
$partnersModule = Yii::$app->getModule('partners');
/** @var mcms\payments\Module $paymentsModule */
$paymentsModule = Yii::$app->getModule('payments');

// перезагрузка баланса и нотификаций по таймеру и при фильтрации статистики
$url = Url::to(['statistic/get-balance']);
$js = <<<JS
var reloadNavbar = function () {
  $.ajax({
      url: '$url',
      success: function(result){
        $('#today-profit-field').html(result.todayProfit);
        $('#balance-field').html(result.balance);
      }
    });
}

$('#statistic-pjax').on('pjax:end', reloadNavbar);
setInterval(reloadNavbar, 300000);
JS;
$this->registerJs($js);

$previewStyleId = null;
$previewStyles = null;
if ($previewStyleId = PartnerCabinetStyle::getPreview()) {
  $previewStyle = PartnerCabinetStyle::findOne($previewStyleId);
  if ($previewStyle) {
    $previewStyles = $previewStyle->generateCss();
  } else {
    Yii::error('Оформление #' . $previewStyleId . ' не найдено');
    PartnerCabinetStyle::disablePreview();
  }
}

if (!$previewStyleId) {
  PartnerCabinetStyleAsset::register($this);
}
BasicAsset::register($this);

$currencyIconList = [
  'rub' => '<i class="icon-ruble_l"></i>',
  'eur' => '<i class="icon-euro_l"></i>',
  'usd' => '$',
];
$logoutUrl = Yii::$app->session->get(User::SESSION_BACK_IDENTITY_ID) ?
  ['/users/users/logout-by-user'] :
  ['/users/site/logout/'];
$logoutText = Yii::$app->session->get(User::SESSION_BACK_IDENTITY_ID) ?
  Yii::_t('users.menu.sign_switch') :
  Yii::_t('partners.main.logout');

if (Yii::$app->getModule('partners')->isAliveSubscriptionsEnabled()) {
  $activeSubs = Yii::$app->getModule('statistic')->api('userDayGroupStatistic', [
    'userId' => Yii::$app->user->id,
  ])->getPartnersAliveSubscriptions();
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <?php if (isset($this->blocks['viewport'])): ?>
    <?php echo $this->blocks['viewport'] ?>
  <?php else: ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <?php endif; ?>
  <?= Html::csrfMetaTags() ?>
  <?php if ($favicon = $partnersModule->api('getFavicon')->getResult()): ?>
    <link rel="icon" type="<?= $partnersModule->api('getFavicon')->getIconMimeType() ?>" href="<?= $favicon; ?>"/>
    <link rel="apple-touch-icon" href="<?= $favicon; ?>"/>
  <?php endif; ?>

  <title><?= Html::encode($this->context->pageTitle) ?></title>
  <?php $this->head() ?>

  <?php if ($previewStyles): ?>
    <style type="text/css">
      <?= $previewStyles ?>
    </style>
  <?php endif; ?>
</head>


<body class="<?= $partnersModule->isThemeEnabled() && Yii::$app->user->getIdentity()->color
  ? Yii::$app->user->getIdentity()->color
  : 'cerulean' ?>">
<?php $this->beginBody() ?>

<nav class="navbar navbar-default">
  <div class="header-navbar-wrapper">
    <div class="panel_nav-mobile toggle_nav">
      <i class="icon-nav-close"></i>
    </div>
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
              data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <?php if($_SERVER['HTTP_HOST'] != 'app.bmr.agency') { ?>
        <a class="navbar-brand" href="<?= Yii::$app->getHomeUrl(); ?>">
          <?php if ($logoUrl = $partnersModule->api('getLogoImage')->getResult()) echo Html::img($logoUrl); ?>
        </a>
      <?php } ?>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav navbar-right">
        <?php if ($partnersModule->isShownPersonalManager() && ($manager = Yii::$app->user->identity->manager)): ?>
        <?php /** @var User $manager */ ?>
          <li class="dropdown manager-dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
               aria-expanded="false">
            <span class="dropdown_profile-logo">
              <img
                src="/img/avatar.png"
                alt="">
            </span>
              <span class="dropdown_profile-user">
              <span
                class="dropdown_profile-username"><?= Yii::$app->formatter->asText($manager->topname ?: $manager->email); ?></span>
              <span class="dropdown_profile-userstatus">(<?= Yii::_t('main.personal_manager') ?>)</span>
            </span>
              <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <?php if ($manager->email):?>
                <li><?= Html::mailto('<i class="icon-mail"></i>' . $manager->email, $manager->email) ?></li>
              <?php endif;?>
              <?php if ($manager->phone):?>
                <li><?= Html::a('<i class="icon-mobile"></i>' . $manager->phone, 'tel:' . $manager->phone) ?></li>
              <?php endif;?>
              <?php foreach ($manager->getContactsArray() as $contact): // TODO: убрать блок после того, как менеджеры заполнят свои контакты по человечески (через UserContact)  ?>
                <?php if ($contact): // иногда контакт может быть пустой строкой ?>
                  <li><?= Html::a('<i class="icon-bubble"></i>' . $contact, null, [
                      'onclick' => 'event.stopPropagation();'
                    ]) ?></li>
                <?php endif ?>
              <?php endforeach;?>

              <?php if (false): // TODO: убрать условие после того, как менеджеры заполнят свои контакты по человечески (через UserContact) ?>
                <?php foreach ($manager->contacts as $contact): ?>
                  <?php if ($contact->data): ?>
                    <li><?= Html::a('<i class="icon-bubble"></i>' . $contact->data, $contact->getBuiltData(), [
                        'target' => '__blank'
                      ]) ?></li>
                  <?php endif ?>
                <?php endforeach; ?>
              <?php endif ?>

            </ul>
          </li>
        <?php endif; ?>
        <?php if ($partnersModule->isPromoUrlEnabled()): ?>
          <li><?= $partnersModule->getPromoUrlHtml(); ?></li>
        <?php endif; ?>
        <?php
        $userBalance = $paymentsModule
          ->api('userBalance', [
            'userId' => Yii::$app->user->id,
            'currency' => $paymentsModule
              ->api('userSettingsData', ['userId' => Yii::$app->user->id])
              ->getResult()->currency
          ])
          ->getResult();
        ?>
        <li class="user_balance">
          <i class="navbar-icon icon-balance" title="<?= Yii::_t('partners.main.balance') ?>"></i>
          <span id="balance-field">
            <?= Yii::$app->formatter->asLandingPrice($userBalance->getBalance(), $userBalance->currency); ?>
          </span>
        </li>
        <li class="user_balance">
          <i class="navbar-icon icon-activeperday" title="<?= Yii::_t('partners.main.today-balance') ?>"></i>
          <span id="today-profit-field">
            <?= Yii::$app->formatter->asLandingPrice($userBalance->getTodayProfit(), $userBalance->currency); ?>
           </span>
        </li>
        <?php if (isset($activeSubs)): ?>
          <li class="user_balance">
            <i class="navbar-icon icon-activedb" title="<?= Yii::_t('partners.main.alive-subscriptions') ?>"></i>
            <span id="alive-subscriptions"><?= $activeSubs ?></span>
          </li>
        <?php endif; ?>
        <?=
        Yii::$app->getModule('notifications')->api('notifyHeaderWidget', [
          'show_all_url' => Url::to(['notification/index/']),
          'clear_url' => Url::to(['notification/clear/']),
          'read_all_url' => Url::to(['notification/read-all/']),
          'settings_url' => Url::to(['profile/notifications/']),
          'theme' => 'basic'
        ])->getResult()
        ?>
        <li class="dropdown user-dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
             aria-expanded="false">
            <span class="dropdown_profile-logo">
              <img
                src="/img/avatar.png"
                alt="">
            </span>
            <span class="dropdown_profile-user">
              <span
                class="dropdown_profile-username"><?= Yii::$app->formatter->asText(Yii::$app->user->identity->email); ?></span>
              <span class="dropdown_profile-userstatus">(<?= Yii::_t('main.partner') ?>)</span>
            </span>
            <span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><?= Link::get(
                '/partners/profile/index',
                [],
                [],
                '<i class="icon-options"></i>' . Yii::_t('main.profile'));
              ?></li>
            <li><?= Html::a(
                '<i class="icon-exit"></i>' . $logoutText,
                $logoutUrl,
                ['data-method' => 'post']);
              ?></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<section
  class="category<?= isset($this->context->categoryNoNav) && $this->context->categoryNoNav == true ? ' category_no-nav' : '' ?>">
  <h1><?= $this->context->controllerTitle ?></h1>

  <?php if ($this->context->menu): ?>
    <?= Tabs::widget([
      'items' => $this->context->menu,
    ]) ?>
  <?php endif; ?>
</section>

<div class="global">
  <section class="main">
    <div class="sidebar-nav">
      <?= Menu::widget([
        'encodeLabels' => false,
        'items' => [
          [
            'label' => '<i class="icon-nav-close"></i>',
            'url' => '',
            'options' => [
              'class' => 'toggle_nav',
            ]
          ],
          [
            'label' => '<div class="user_info-mobile-wrap">
							<img src="/img/avatar.png">
							<span class="user_info-mobile-user">
								<a href="/partners/profile/index/" class="user_info-mobile-username">' . Yii::$app->formatter->asText(Yii::$app->user->identity->email) . '</a>
								<span class="user_info-mobile-userstatus">(' . Yii::_t('main.partner') . ')</span>
							</span>
						</div>
						<div class="user_info-mobile-balance">
							<div class="row">
								<div class="col-xs-6">
									<span class="mobile-balance__price">' .
                    PriceWidget::widget([
                      'currency' => $userBalance->currency,
                      'value' => $userBalance->getMain(),
                    ]) .
                  '</span>
									<i>' . Yii::_t('partners.main.balance') . '</i>
								</div>
								<div class="col-xs-6">
									<span class="mobile-balance__price">' .
              PriceWidget::widget([
                'currency' => $userBalance->currency,
                'value' => $userBalance->getTodayProfit(),
              ]) . '
                  </span>' .
              (isset($activeSubs)
                ? '<span>' .
                Html::a("($activeSubs)", null, [
                  'title' => Yii::_t('partners.main.alive-subscriptions'),
                  'style' => 'color: inherit;',
                ]) . '</span>'
                : ''
              ) .
              '<i>' . Yii::_t('partners.main.today-balance') . '</i>
								</div>
							</div>
						</div>',
            'options' => [
              'class' => 'user_info-mobile',
            ]
          ],
          [
            'label' => '<i class="icon-statistic"></i>' . Yii::_t('partners.main.statistic'),
            'url' => ['/partners/statistic/index/'],
            'options' => [
              'class' => Yii::$app->controller->id == 'statistic' ? 'active' : '',
            ]
          ],
          [
            'label' => '<i class="icon-promo"></i>' . Yii::_t('partners.main.promo'),
            'url' => ['/partners/promo/index/'],
            'options' => [
              'class' => in_array(Yii::$app->controller->id, ['promo', 'links', 'sources', 'domains']) ||
              (Yii::$app->controller->id === 'profile' && Yii::$app->controller->action->id === 'postback-settings')
                ? 'active'
                : '',
            ],
            'visible' => Yii::$app->user->identity->canViewPromo()
          ],
          [
            'label' => '<i class="icon-payments"></i>' . Yii::_t('partners.main.finance'),
            'url' => ['/partners/payments/balance/'],
            'options' => [
              'class' => Yii::$app->controller->id == 'payments' ? 'active' : '',
            ]
          ],
          [
            'label' => '<i class="icon-faq"></i>' . Yii::_t('partners.main.faq'),
            'url' => ['/partners/faq/index/'],
            'options' => [
              'class' => Yii::$app->controller->id == 'faq' ? 'active' : '',
            ]
          ],
          [
            'label' => '<i class="icon-support"></i>' . Yii::_t('partners.main.support') .
              Html::tag('span', $partnersModule->api('getCountOfTickets')->getResult() ?: '', ['class' => 'badge']),
            'url' => ['/partners/support/index/'],
            'options' => [
              'class' => Yii::$app->controller->id == 'support' ? 'support active' : 'support',
            ]
          ],
          [
            'label' => '<i class="icon-options"></i>' . Yii::_t('partners.main.profile'),
            'url' => ['/partners/profile/index/'],
            'options' => [
              'class' => 'li-profile ' . (Yii::$app->controller->id == 'profile' ? 'active' : ''),
            ],
          ],
          [
            'label' => '<i class="icon-user"></i>' . Yii::_t('partners.main.referrals'),
            'url' => ['/partners/referrals/income/'],
            'options' => [
              'class' => Yii::$app->controller->id == 'referrals' ? 'active' : '',
            ],
            'visible' => Yii::$app->getModule('users')->isRegistrationWithReferrals()
          ],
          [
            'label' => '<i class="icon-exit"></i>' . $logoutText,
            'url' => $logoutUrl,
            'options' => [
              'class' => 'li-exit',
            ],
          ],
        ]
      ]) ?>
    </div>

    <div id="content" class="content">
      <?php
      $this->render('flash');
      ?>
      <?= $content ?>
    </div>

    <?php $footerContactValues = $partnersModule->getFooterContactValues(); ?>
    <div class="footer">
      <?php if($_SERVER['HTTP_HOST'] != 'app.bmr.agency') { ?>
        <?php if (ArrayHelper::keyExists('mainQuestions', $footerContactValues)): ?>

        <div class="mobile__contacts">
          <?= Yii::_t('partners.main.main_support') ?>: <span data-target="1"
                                                              class="active"><?= Yii::_t('partners.main.main') ?></span>
          <span data-target="2"><?= Yii::_t('partners.main.tech') ?></span>
        </div>
      <?php endif ?>
        <div class="">
        <div class="row">
          <div class="col-xs-10">
            <?php if (ArrayHelper::keyExists('mainQuestions', $footerContactValues)): ?>
              <div class="footer-links vis">
                <h5><?= Yii::_t('partners.main.main_questions'); ?></h5>

                <?php
                $footerMainQuestionSkype = ArrayHelper::getValue($footerContactValues, 'mainQuestions.skype');
                $footerMainQuestionEmail = ArrayHelper::getValue($footerContactValues, 'mainQuestions.email');
                ?>

                <?php if ($footerMainQuestionSkype = ArrayHelper::getValue($footerContactValues, 'mainQuestions.skype')): ?>
                  <div class="footer-link">
                    <i class="icon-skype"></i>
                    <a href="skype:<?= $footerMainQuestionSkype; ?>">
                      <?= $footerMainQuestionSkype; ?>
                    </a>
                  </div>
                <?php endif; ?>
                <?php if ($footerMainQuestionEmail = ArrayHelper::getValue($footerContactValues, 'mainQuestions.email')): ?>
                  <div class="footer-link">
                    <i class="icon-mail"></i>
                    <a href="mailto:<?= $footerMainQuestionEmail; ?>">
                      <?= $footerMainQuestionEmail; ?>
                    </a>
                  </div>
                <?php endif; ?>

                <?php if ($footerMainQuestionIcq = ArrayHelper::getValue($footerContactValues, 'mainQuestions.icq')): ?>
                  <div class="footer-link">
                    <i class="icon-icq"></i>
                    <a href="icq:<?= $footerMainQuestionIcq; ?>">
                      <?= $footerMainQuestionIcq; ?>
                    </a>
                  </div>
                <?php endif; ?>
                <?php if ($footerMainQuestionTelegram = ArrayHelper::getValue($footerContactValues, 'mainQuestions.telegram')): ?>
                  <div class="footer-link">
                    <i class="icon-telegram"></i>
                    <a href="tg://join?invite=<?= $footerMainQuestionTelegram ?>">
                      <?= $footerMainQuestionTelegram ?>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if (ArrayHelper::keyExists('techSupport', $footerContactValues)): ?>
              <div class="footer-links">
                <h5><?= Yii::_t('partners.main.tech_support'); ?></h5>
                <?php
                $footerTechSupportSkype = ArrayHelper::getValue($footerContactValues, 'techSupport.skype');
                $footerTechSupportEmail = ArrayHelper::getValue($footerContactValues, 'techSupport.email');
                ?>

                <?php if ($footerTechSupportSkype): ?>
                  <div class="footer-link">
                    <i class="icon-skype"></i>
                    <a href="skype:<?= $footerTechSupportSkype; ?>">
                      <?= $footerTechSupportSkype; ?>
                    </a>
                  </div>
                <?php endif; ?>
                <?php if ($footerTechSupportEmail): ?>
                  <div class="footer-link">
                    <i class="icon-mail"></i>
                    <a href="mailto:<?= $footerTechSupportEmail; ?>">
                      <?= $footerTechSupportEmail; ?>
                    </a>
                  </div>
                <?php endif; ?>


                <?php if ($footerTechSupportIcq = ArrayHelper::getValue($footerContactValues, 'techSupport.icq')): ?>
                  <div class="footer-link">
                    <i class="icon-icq"></i>
                    <a href="icq:<?= $footerTechSupportIcq; ?>">
                      <?= $footerTechSupportIcq; ?>
                    </a>
                  </div>
                <?php endif; ?>
                <?php if ($footerTechSupportTelegram = ArrayHelper::getValue($footerContactValues, 'techSupport.telegram')): ?>
                  <div class="footer-link">
                    <i class="icon-telegram"></i>
                    <a href="tg://join?invite=<?= $footerTechSupportTelegram ?>">
                      <?= $footerTechSupportTelegram ?>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

          </div>
          <?php if ($footerCopyright = ArrayHelper::getValue($footerContactValues, 'copyright')): ?>
            <div class="col-xs-2 text-right">
              <span class="copy">© <?= date("Y") . ' ' . $footerCopyright; ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php } ?>
    </div>
  </section>
</div>

<?= \mcms\partners\components\widgets\ProgressWidget::widget(); ?>
<?= \mcms\partners\components\widgets\NotifierWidget::widget(); ?>
<?= \mcms\partners\components\widgets\ConfirmWidget::widget(); ?>

<?php if ($partnersModule->showPromoModal()): ?>
  <?php \yii\bootstrap\Modal::begin([
    'id' => 'promoModal',
    'closeButton' => false,
    'header' => Html::tag('h4', $partnersModule->getPromoModalHeader(), ['class' => 'modal-title']),
    'footer' => Html::button(Yii::_t('partners.main.examined'), ['class' => 'btn btn-success', 'data-dismiss' => 'modal'])
  ]) ?>
  <?= $partnersModule->getPromoModalBody() ?>
  <?php \yii\bootstrap\Modal::end() ?>
  <?php $this->registerJs('$(function() {
    $("#promoModal").modal("show").find("[data-dismiss]").on("click", function() {
      $.post("' . Url::to(['default/hide-promo-modal']) . '")
    });
  })') ?>
<?php endif ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
