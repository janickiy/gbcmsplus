<?php

/* @var $this \yii\web\View */
/* @var $content string */

use mcms\partners\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use mcms\common\helpers\Link;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= Html::csrfMetaTags() ?>
  <title><?= Html::encode($this->context->controllerTitle) ?></title>
  <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<nav id="w0" class="navbar-inverse navbar" role="navigation">
  <div class="">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#w0-collapse"><span
          class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span></button>
      <a class="navbar-brand" href="/"></a></div>
    <div id="w0-collapse" class="collapse navbar-collapse">

      <ul class="navbar-nav navbar-right nav">
        <li>
          <?= Yii::$app->getModule('notifications')->api('notifyHeaderWidget', [
            'show_all_url' => '/partners/notification/index/',
            'clear_url' => '/partners/notification/clear/',
            'read_all_url' => '/partners/notification/read-all/'
          ])->getResult()?>
        </li>
        <li class="navbar-user">
          <div class="navbar-user__avatar">
            <img src="/img/avatar.png">
          </div>
          <div class="navbar-user__login">
            <?= Link::get('/partners/default/profile/', [], [], Yii::$app->user->identity->email)?>
            <div>(<?= Yii::_t('main.partner') ?>)</div>
          </div>
        </li>
        <li><?=Html::a(Yii::_t('partners.main.logout'), '/users/site/logout/', ['data-method' => 'post'])?></li>
      </ul>
      <ul class="navbar-nav navbar-right nav">
        <li class="header-balance">
          <?php $userBalance = Yii::$app->getModule('payments')->api('userBalance', ['userId' => Yii::$app->user->id])->getResult()?>
          <span><?= Yii::_t('partners.main.balance') . ': ' . Yii::$app->getFormatter()->asPrice($userBalance->getMain(), $userBalance->currency); ?></span>
          <span class="today-balance"><?= Yii::_t('partners.main.today-balance') . ': ' .
            Yii::$app->getFormatter()->asPrice($userBalance->getTodayProfit(), $userBalance->currency); ?></span>
        </li>
      </ul>

      <?= Yii::$app->getModule('users')->api('userBack', ['url' => ['/users/users/logout-by-user']])->getResult()?>
</nav>


<section class="category bg-primary">
  <h1><?=$this->context->controllerTitle?></h1>

  <?php if($this->context->menu):?>
    <?=Tabs::widget([
      'items' => $this->context->menu,
    ])?>
  <?php endif;?>
</section>


<div id="wrapper" class="toggled-2">
  <!-- Sidebar -->
  <div id="sidebar-wrapper">
    <ul class="sidebar-nav" id="menu">
      <li>
        <a id="menu-toggle-2" href="#"><span class="glyphicon glyphicon-menu-hamburger"></span>&nbsp;</a>
      </li>
      <li>
        <?= \mcms\common\helpers\Html::a('<span class="glyphicon glyphicon-th-list"></span> ' . Yii::_t('partners.main.statistic'), '/partners/statistic/index/'); ?>
      </li>
      <li>
        <?=Link::get('/partners/promo/index/', [], '', '<span class="glyphicon glyphicon-promo"></span> ' . Yii::_t('partners.main.promo') )?>
      </li>
      <li>
        <?= Link::get('/partners/payments/balance/', [], '', '<span class="glyphicon glyphicon-usd"></span> ' . Yii::_t('partners.main.payments') )?>
      </li>
      <li>
        <?=Link::get('/partners/support/index/', [], '', '<span class="glyphicon glyphicon-support"></span> ' . Yii::_t('partners.main.support') )?>
      </li>
      <li>
        <?=Link::get('/partners/notification/index/', [], '', '<span class="glyphicon glyphicon-notifications"></span> ' . Yii::_t('partners.main.notification') )?>
      </li>
      <li>
        <?=Link::get('/partners/referrals/today/', [], '', '<span class="glyphicon glyphicon-usd"></span> ' . Yii::_t('partners.main.referrals') )?>
      </li>
    </ul>
  </div>
  <!-- /#sidebar-wrapper -->
  <!-- Page Content -->
  <div id="page-content-wrapper">
    <div class="container-fluid xyz">
      <?php
      $this->render('flash.php');
      ?>
      <?= $content ?>
    </div>
  </div>
  <!-- /#page-content-wrapper -->
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
