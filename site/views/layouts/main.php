<?php

/* @var $this \yii\web\View */
/* @var $content string */

use site\assets\LPAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;
use yii\bootstrap\Modal;


LPAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= Html::csrfMetaTags() ?>
  <title><?= Html::encode($this->title) ?></title>
  <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
  <?php
  NavBar::begin([
    'brandLabel' => Yii::$app->getModule('partners')->getProjectName(),
    'brandUrl' => Yii::$app->getHomeUrl(),
  ]);

  $menuItems = Yii::$app->user->getIsGuest()
    ? [
      ['label' => Yii::_t('app.common.login'), 'url' => Url::to(['/users/site/login'])],
      ['label' => Yii::_t('app.common.registration'), 'url' => Url::to(['/users/site/signup'])],
      ['label' => Yii::_t('app.common.remember password'), 'url' => Url::to(['/users/site/request-password-reset'])],
    ]
    : [
      [
        'label' => Yii::_t('app.common.lk'),
        'url' => Yii::$app->getModule('users')->urlCabinet
      ],
      ['label' => Yii::_t('app.common.logout') . ' (' . Yii::$app->user->identity->username . ')', 'url' => ['/users/site/logout']]
    ]
    ;

  echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => array_merge($menuItems, [
      ['label' => Yii::_t('app.common.language'),
        'items' => [
          ['label' => Yii::_t('app.common.russian language'), 'url' => Url::to(['/users/site/lang/','language'=>'ru'])],
          ['label' => Yii::_t('app.common.english language'), 'url' => Url::to(['/users/site/lang/','language'=>'en'])]
        ]
      ]
    ])
  ]);
  NavBar::end();
  ?>

  <div class="container">
    <?= $this->render('@app/views/layouts/flash.php'); ?>
    <?= $content ?>
  </div>
</div>

<?php Modal::begin([
  'header' => '<h3></h3>',
  'id' => 'main-modal'
]); ?>
<?php Modal::end(); ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
