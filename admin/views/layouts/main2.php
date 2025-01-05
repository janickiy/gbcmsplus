<?php

/* @var $this \yii\web\View */
/* @var $content string */

use admin\assets\AppAsset;
use mcms\common\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\nav\NavX;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use mcms\common\helpers\Menu;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <meta charset="<?= Yii::$app->charset ?>">

  <?= Html::csrfMetaTags() ?>

  <?php $partnersModule = Yii::$app->getModule('partners'); ?>
  <?php if ($favicon = $partnersModule->api('getFavicon')->getResult()): ?>
    <link rel="icon" type="<?= $partnersModule->api('getFavicon')->getIconMimeType()?>" href="<?= $favicon; ?>" />
    <link rel="apple-touch-icon" href="<?= $favicon; ?>"/>
  <?php endif; ?>

  <title><?= Html::encode($this->title) ?></title>
  <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?= ercling\loadingindicator\Indicator::widget(['color' => 'blue', 'theme' => 'barber-shop']); ?>

<div class="global">
  <div class="wrap">
    <?php
    NavBar::begin([
      'brandLabel' => Yii::$app->getModule('partners')->getProjectName(),
      'brandUrl' => Yii::$app->getHomeUrl(),
      'innerContainerOptions' => [
        'class' => 'container-fluid container-admin'
      ]
    ]);
    echo
    NavX::widget([
      'activateParents' => true,
      'options' => ['class' => 'navbar-nav'],
      'items' => Menu::getItems(
        Yii::$app->getModule('notifications')
          ? Yii::$app->getModule('notifications')->api('getBrowserNotificationCount', [
          'user_id' => Yii::$app->user->id
        ])->getResult()
          : false
      ),
      'encodeLabels' => false
    ])  ;

    echo Yii::$app->getModule('users')
      ? Yii::$app->getModule('users')->api('userBack', ['url' => ['/users/users/logout-by-user']])->getResult()
      : '';
    ?>

    <div class="pull-right">
      <?php
      echo Yii::$app->getModule('notifications') ? Yii::$app->getModule('notifications')->api('notifyHeaderWidget', [
        'clear_url' => '/admin/notifications/notifications/clear/',
        'read_all_url' => '/admin/notifications/notifications/read-all/',
      ])->getResult() : '';

      echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
          [
            'label' => Yii::$app->user->identity->username,
            'items' => [
              [
                'label' => Yii::_t('app.common.profile'),
                'url' => Yii::$app->getModule('users')->api('userLink')->buildProfileEditLink()
              ],
              [
                'label' => Yii::_t('docs.menu.changelog'),
                'url' => Url::to(['/docs/changelog/index'])
              ],
              [
                'url' => '/users/site/logout/',
                'label' => Yii::_t('app.common.logout') . ' (' . Yii::$app->user->identity->username . ')',
                'encode' => false,
              ]
            ],
          ],
        ],
      ]);
      ?>
    </div>
    <?php NavBar::end(); ?>

    <div class="container-fluid container-admin">
      <?= Breadcrumbs::widget([
        'itemTemplate' => '<li><i>{link}</i></li>',
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []
      ]) ?>
      <div class="row actions-wrapper">

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
      </div>
      <?php
      $this->render('@app/views/layouts/flash.php');
      ?>
      <?= $content ?>
    </div>
  </div>

  <?php $footerContactValues = Yii::$app->getModule('partners')->getFooterContactValues();?>
  <div class="footer">
    <div class="">
      <div class="row">
        <div class="col-xs-10">

          <?php if (ArrayHelper::keyExists('mainQuestions', $footerContactValues)): ?>
            <div class="footer-links">
              <h5><?= Yii::_t('partners.main.main_questions'); ?></h5>
              <div class="row">
                <?php
                $footerMainQuestionSkype = ArrayHelper::getValue($footerContactValues, 'mainQuestions.skype');
                $footerMainQuestionEmail = ArrayHelper::getValue($footerContactValues, 'mainQuestions.email');
                ?>
                <?php if ($footerMainQuestionSkype || $footerMainQuestionEmail): ?>
                  <div class="col-xs-7">
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
                  </div>
                <?php endif; ?>

                <?php if ($footerMainQuestionIcq = ArrayHelper::getValue($footerContactValues, 'mainQuestions.icq')): ?>
                  <div class="col-xs-5">
                    <div class="footer-link">
                      <i class="icon-icq"></i>
                      <a href="icq:<?= $footerMainQuestionIcq; ?>">
                        <?= $footerMainQuestionIcq; ?>
                      </a>
                    </div>
                  </div>
                <?php endif; ?>
                <?php if ($footerMainQuestionTelegram = ArrayHelper::getValue($footerContactValues, 'mainQuestions.telegram')): ?>
                  <div class="col-xs-5">
                    <div class="footer-link">
                      <i class="icon-telegram"></i>
                      <a href="tg://join?invite=<?= $footerMainQuestionTelegram ?>">
                        <?= $footerMainQuestionTelegram ?>
                      </a>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (ArrayHelper::keyExists('techSupport', $footerContactValues)): ?>
            <div class="footer-links">
              <h5><?= Yii::_t('partners.main.tech_support'); ?></h5>
              <div class="row">
                <?php
                $footerTechSupportSkype = ArrayHelper::getValue($footerContactValues, 'techSupport.skype');
                $footerTechSupportEmail = ArrayHelper::getValue($footerContactValues, 'techSupport.email');
                ?>
                <?php if ($footerTechSupportSkype || $footerTechSupportEmail): ?>
                  <div class="col-xs-6">
                    <?php if($footerTechSupportSkype): ?>
                      <div class="footer-link">
                        <i class="icon-skype"></i>
                        <a href="skype:<?= $footerTechSupportSkype; ?>">
                          <?= $footerTechSupportSkype; ?>
                        </a>
                      </div>
                    <?php endif; ?>
                    <?php if($footerTechSupportEmail): ?>
                      <div class="footer-link">
                        <i class="icon-mail"></i>
                        <a href="mailto:<?= $footerTechSupportEmail; ?>">
                          <?= $footerTechSupportEmail; ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>

                <?php if ($footerTechSupportIcq = ArrayHelper::getValue($footerContactValues, 'techSupport.icq')): ?>
                  <div class="col-xs-6">
                    <div class="footer-link">
                      <i class="icon-icq"></i>
                      <a href="icq:<?= $footerTechSupportIcq; ?>">
                        <?= $footerTechSupportIcq; ?>
                      </a>
                    </div>
                  </div>
                <?php endif; ?>
                <?php if ($footerTechSupportTelegram = ArrayHelper::getValue($footerContactValues, 'techSupport.telegram')): ?>
                  <div class="col-xs-6">
                    <div class="footer-link">
                      <i class="icon-telegram"></i>
                      <a href="tg://join?invite=<?= $footerTechSupportTelegram ?>">
                        <?= $footerTechSupportTelegram ?>
                      </a>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
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
  </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
