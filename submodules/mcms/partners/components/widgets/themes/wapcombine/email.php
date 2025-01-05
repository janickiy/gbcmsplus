<?php

use mcms\common\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @var string $title
 * @var string $text
 * @var string $email
 * @var string $unsubscribeHash
 */

$footerContactValues = Yii::$app->getModule('partners')->getFooterContactValues();
$footerMainQuestionSkype = ArrayHelper::getValue($footerContactValues, 'mainQuestions.skype');
$footerMainQuestionIcq = ArrayHelper::getValue($footerContactValues, 'mainQuestions.icq');
$footerMainQuestionEmail = ArrayHelper::getValue($footerContactValues, 'mainQuestions.email');

$footerTechSupportSkype = ArrayHelper::getValue($footerContactValues, 'techSupport.skype');
$footerTechSupportIcq = ArrayHelper::getValue($footerContactValues, 'techSupport.icq');
$footerTechSupportEmail = ArrayHelper::getValue($footerContactValues, 'techSupport.email');

$footerCopyright = ArrayHelper::getValue($footerContactValues, 'copyright');

/** @var \mcms\partners\Module $partnersModule */
$partnersModule = Yii::$app->getModule('partners');
$serverName = $partnersModule->getServerName()
?>

<style type="text/css">
  body {
    background: url(<?= $serverName ?>/img/wapcombine/email/body.jpg), #E7E7E7;
    margin: 0;
  }
  @media (max-width: 780px) {
    body {
      padding: 0;
    }
    .content {
      padding: 15px;
    }
    .header_right_bg {
      background-image: none; padding-bottom: 20px;
    }
    .header {
      padding: 20px;
    }
    .footer {
      padding: 15px;
    }
  }
</style>
<div class="container" style="max-width: 780px; font-family: Arial; background: #ffffff; margin: auto;">
  <div class="header" style="padding-top: 5%; padding-left: 5%;  background: #F8F8F8 url(<?= $serverName ?>/img/wapcombine/email/header.jpg);">
    <div class="header_right_bg" style="padding-bottom: 5%; padding-right: 5%;">
      <a href="<?= $partnersModule->getServerName(); ?>" target="_blank">
        <img src="<?= $serverName ?>/img/wapcombine/email/logo.png" alt="wapcombine" style="max-width: 100%;">
      </a>
    </div>
  </div>
  <div class="content" style="padding: 4%;">
    <?php if ($email): ?>
      <span class="userName" style="display: block; font-weight: bold; font-size: 18px; color: #da6a0f;">Здравствуйте, <?= $email ?>!</span>
    <?php endif; ?>
    <span class="mailTitle" style="display: block; font-weight: bold; font-size: 18px; color: #000000; margin-top: 28px;"><?= $title; ?></span>
    <?= $text; ?>
    <div class="signature" style="font-weight: bold; text-align: right; display: block; color: #3D3D3D; margin-top: 50px;" align="right">C уважением, WAPcombine Support.</div>
  </div>
  <div class="footer" style="background-color: #F8FBFC; padding: 4%; border: 1px solid #e9ebee;">
    <span class="footer__title" style="font-weight: bold; display: block; color: #3D3D3D; margin-bottom: 10px;">Контакты:</span>
    <ul style="font-size: 15px; color: #999999; margin: 0; padding: 0; list-style: none;">
      <?php if($footerMainQuestionEmail): ?>
      <li style="padding: 7px 0;">
        <i style="font-style: normal; color: #191919;">E-mail</i>  /  <a href="mailto:<?= $footerMainQuestionEmail ?>" style="color: #da6a0f; text-decoration: underline;"><?= $footerMainQuestionEmail ?></a> — ежедневно с 10.00 до 24.00 (мск)
      </li>
      <?php endif; ?>
      <?php if($footerMainQuestionSkype): ?>
      <li style="padding: 7px 0;">
        <i style="font-style: normal; color: #191919;">Skype</i>  /  <a href="skype:<?= $footerMainQuestionSkype ?>" style="color: #da6a0f; text-decoration: underline;"><?= $footerMainQuestionSkype ?></a> — по будням с 10.00 до 24.00 (мск)
      </li>
      <?php endif; ?>
      <?php if($footerMainQuestionIcq): ?>
      <li style="padding: 7px 0;">
        <i style="font-style: normal; color: #191919;">ICQ</i>  /  <a href="<?= $footerMainQuestionIcq ?>" style="color: #da6a0f; text-decoration: underline;"><?= $footerMainQuestionIcq ?></a> — по будням с 12.00 до 24.00 (мск)
      </li>
      <?php endif; ?>
    </ul>
  </div>
</div>
<div class="bottom" style="max-width: 780px; text-align: center; font-family: Arial; padding-bottom: 40px; padding-top: 70px; margin: 0px auto auto; background-image: url(<?= $serverName ?>/img/wapcombine/email/bottom_sh.png); background-repeat: no-repeat; background-position: 50% 0px;" align="center">
  <span style="display: block; text-align: center; font-size: 15px; color: #BDBDBD; margin-bottom: 10px;">Это сообщение отправлено автоматически и не требует ответа.</span>
  <a style="display: block; text-align: center; font-size: 15px; color: #BDBDBD; margin-bottom: 15px;" href="<?= Url::to(['/unsubscribe/index/', 'email' => $email, 'hash' => $unsubscribeHash]) ?>"><?=Yii::_t('partners.main.unsubscribe')?></a>
  <a href="https://rgkgroup.com/"><img src="<?= $serverName ?>/img/wapcombine/email/company.png" alt="RGK Group"></a>
</div>