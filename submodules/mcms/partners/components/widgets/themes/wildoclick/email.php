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
$footerMainQuestionEmail = ArrayHelper::getValue($footerContactValues, 'mainQuestions.email');

$footerCopyright = ArrayHelper::getValue($footerContactValues, 'copyright');

/** @var \mcms\partners\Module $partnersModule */
$partnersModule = Yii::$app->getModule('partners');
$serverName = $partnersModule->getFilledServerNameForEmail();
?>

<style type="text/css">
  body {
    margin: 0;
    padding: 0;
    font-family: 'Open Sans', sans-serif;
    background-size: contain;
    background-color: #1a0239;
  }
</style>
<div style="background: #1a0239;">

  <div style="
    width: 100%;
    max-width: 540px;
    background: #fff;
    margin: 0 auto;
    padding: 15px 30px;
">
    <div style="
		border-bottom: 1px solid #e9e9e9;
    	padding-bottom: 20px;
	">
      <div style="
		    display: inline-block;
		    vertical-align: middle;
		    margin-right: 3%;
		">
        <img src="<?= $serverName ?>/img/wildoclick/email/logo.png" alt="" width="186">
      </div>
      <?php if($footerMainQuestionSkype): ?>
      <div style="
			display:inline-block;
			vertical-align: middle;
			margin-right: 3%;
		">
        <img src="<?= $serverName ?>/img/wildoclick/email/skype.png" alt="" style="vertical-align: top;margin-right: 5px;">
        <a style="color:#3e3c61!important" href="skype:<?= $footerMainQuestionSkype ?>?chat"><?= $footerMainQuestionSkype ?></a>
      </div>
      <?php endif; ?>
      <?php if($footerMainQuestionEmail): ?>
      <div style="
			display:inline-block;
			vertical-align: middle;
		">
        <img src="<?= $serverName ?>/img/wildoclick/email/mail.png" alt="" style="vertical-align: top;margin-right: 5px;">
        <a style="color:#3e3c61!important" href="mailto:<?= $footerMainQuestionEmail ?>"><?= $footerMainQuestionEmail ?></a>
      </div>
      <?php endif; ?>
    </div>

    <h1 style="
	    font-size: 30px;
	    text-transform: uppercase;
	    font-weight: 300;
	    line-height: 32px;
	    margin-top:30px;
	"><?= $title; ?></h1>
    <p style="
		font-size: 18px;
	    line-height: 25px;
    "><?= $text; ?></p>

    <div style="text-align: center;">
      <img src="<?= $serverName ?>/img/wildoclick/email/wm_logo.png" alt="" style="margin-right: 4%; margin-bottom:10px; height: 25px;">
      <img src="<?= $serverName ?>/img/wildoclick/email/wire_logo.png" alt="" style="margin-right: 4%; margin-bottom:10px; height: 25px;">
      <img src="<?= $serverName ?>/img/wildoclick/email/epayments_logo.png" alt="" style="margin-bottom:10px; height: 25px;">
    </div>
  </div>

  <div style="
	    background: #1a2132;
	    padding: 15px 30px;
	    max-width: 542px;
	    margin: 0 auto;
	    font-size: 0;
	">

    <a href="<?= Url::to(['/unsubscribe/index/', 'email' => $email, 'hash' => $unsubscribeHash]) ?>" style="
		    text-decoration: underline;
		    display: inline-block;
		    width: 50%;
		    vertical-align: top;
		    color: #8495a7;
		    font-size: 14px;
		    line-height: 32px;
		">Отписаться от рассылки</a>

    <span style="
			text-align: right;
		    display: inline-block;
		    width: 50%;
		    vertical-align: top;
		">
			<a href="https://vk.com/wildo_click"><img src="<?= $serverName ?>/img/wildoclick/email/vk.png" alt="Вконтакте"></a>
		</span>
  </div>

  <img src="<?= $serverName ?>/img/wildoclick/email/logo2.png" alt="" style="display:block;margin:30px auto 20px;opacity:0.5;color:#808193;">

  <div style="margin:0 auto 50px; max-width:600px;text-align: center;">
    <?php if($footerMainQuestionSkype): ?>
    <div style="
				display:inline-block;
				vertical-align: middle;
				margin-right: 3%;
				color:#808193;
			">
      <img src="<?= $serverName ?>/img/wildoclick/email/skype2.png" alt="" style="vertical-align: top;margin-right: 5px; opacity:0.5;">
      <a style="color:#808193!important" href="skype:<?= $footerMainQuestionSkype ?>?chat"><?= $footerMainQuestionSkype ?></a>
    </div>
    <?php endif; ?>
    <?php if($footerMainQuestionEmail): ?>
    <div style="
				display:inline-block;
				vertical-align: middle;
				color:#808193!important;
			">
      <img src="<?= $serverName ?>/img/wildoclick/email/mail2.png" alt="" style="vertical-align: top;margin-right: 5px; opacity:0.5;">
      <a style="color:#808193!important" href="mailto:<?= $footerMainQuestionEmail ?>"><?= $footerMainQuestionEmail ?></a>
    </div>
    <?php endif; ?>
  </div>
</div>
