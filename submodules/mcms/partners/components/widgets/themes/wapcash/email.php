<?php

use mcms\common\helpers\ArrayHelper;
use yii\helpers\Html;
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
?>

<div style="background-color: #f4f4f3; width: 100%;">
  <div style="width: 100%; max-width: 700px; margin: 0 auto; background-color: #f4f4f3;">
    <div style="background-color: #16A085; ">
      <div style="overflow: hidden;background-color: #fff;padding: 15px 20px;">
          <?php if ($logoUrl = $partnersModule->api('getLogoEmailImage')->getResult()): ?>
            <div style="float: left; display: inline-block; vertical-align: middle; width: auto; height: auto; max-width: 50%; max-height: 40px;">
              <a href="<?= $partnersModule->getServerName(); ?>" target="_blank" style="display: block; height: 100%;">
                <?= Html::img(Url::to($logoUrl, true), [
                  'width' => 'auto',
                  'height' => 'auto',
                  'style' => 'max-width: 100%; max-height: 40px;',
                ]); ?>
              </a>
            </div>
          <?php endif; ?>
        <div style="float: right; display: inline-block; vertical-align: middle; width: auto; height: auto;">
          <a target="_blank" href="<?= $partnersModule->getServerName(); ?>" style="color: #9d9d9d; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 40px; text-decoration: none;"><?= str_replace(['http://', 'https://'], '', $partnersModule->getServerName()); ?></a>
        </div>
      </div>
      <div style="margin-bottom:10px;padding: 15px 20px 0 20px">
        <strong style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; line-height: 30px; color: #fff;"><?= $title; ?></strong>
      </div>
      <div style="padding: 0 20px 15px 20px;">
        <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 15px; color: #fff; background-image: url(http://resourcesfiles.com/panel_delivery/white-dp.png); background-repeat: no-repeat; background-position: 0 50%; padding-left: 20px;"><?= Yii::$app->getFormatter()->asPartnerDate(time()); ?></p>
      </div>
    </div>
    <div style="background-color: #1ABC9C; height: 7px;"></div>
    <div style="padding: 30px 25px; border-left: 1px solid #d9d9d9; border-right: 1px solid #d9d9d9; background-color: #fff;">
      <?= $text; ?>
    </div>
    <div style="background-color: #1ABC9C; height: 7px;"></div>
    <div style="padding: 20px 25px; border-left: 1px solid #d9d9d9; border-right: 1px solid #d9d9d9; border-bottom: 1px solid #d9d9d9; background-color: #fff;">

      <?php if (ArrayHelper::keyExists('mainQuestions', $footerContactValues)): ?>
        <div style="display: inline-block; vertical-align: top; margin-right: 15%;">
          <p style="color: #8b8888; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 18px; marign: 0 0 20px;">
            <?= Yii::_t('partners.main.main_questions'); ?>
          </p>
          <?php if ($footerMainQuestionSkype || $footerMainQuestionIcq): ?>
            <div>
              <?php if ($footerMainQuestionSkype): ?>
                <a target="_blank" href="skype:<?= $footerMainQuestionSkype; ?>?chat" style="display: inline-block; vertical-align: middle; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 20px; color: #8b8888; background-image: url(http://resourcesfiles.com/panel_delivery/skype.png); background-repeat: no-repeat; background-position: 0 50%; padding-left: 25px; margin: 0 20px 20px 0;">
                  <?= $footerMainQuestionSkype; ?>
                </a>
              <?php endif; ?>
              <?php if ($footerMainQuestionIcq): ?>
                <a target="_blank" href="icq:<?= $footerMainQuestionIcq; ?>" style="display: inline-block; vertical-align: middle; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 20px; color: #8b8888; background-image: url(http://resourcesfiles.com/panel_delivery/isq.png); background-repeat: no-repeat; background-position: 0 50%; padding-left: 25px; margin: 0 20px 20px 0;">
                  <?= $footerMainQuestionIcq; ?>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <?php if ($footerMainQuestionEmail): ?>
            <div>
              <a target="_blank" href="mailto:<?= $footerMainQuestionEmail; ?>" style="display: inline-block; vertical-align: middle; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 20px; color: #8b8888; background-image: url(http://resourcesfiles.com/panel_delivery/mail.png); background-repeat: no-repeat; background-position: 0 50%; padding-left: 25px; margin: 0 20px 20px 0;">
                <?= $footerMainQuestionEmail; ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (ArrayHelper::keyExists('techSupport', $footerContactValues)): ?>
        <div style="display: inline-block; vertical-align: top;">
          <p style="color: #8b8888; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 18px; marign: 0 0 20px;">
            <?= Yii::_t('partners.main.tech_support'); ?>
          </p>
          <?php if ($footerTechSupportSkype || $footerTechSupportIcq): ?>
            <div>
              <?php if ($footerTechSupportSkype): ?>
                <a target="_blank" href="skype:<?= $footerTechSupportSkype; ?>" style="display: inline-block; vertical-align: middle; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 20px; color: #8b8888; background-image: url(http://resourcesfiles.com/panel_delivery/skype.png); background-repeat: no-repeat; background-position: 0 50%; padding-left: 25px; margin: 0 20px 20px 0;">
                  <?= $footerTechSupportSkype; ?>
                </a>
              <?php endif; ?>
              <?php if ($footerTechSupportIcq): ?>
                <a target="_blank" href="icq:<?= $footerTechSupportIcq; ?>" style="display: inline-block; vertical-align: middle; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 20px; color: #8b8888; background-image: url(http://resourcesfiles.com/panel_delivery/isq.png); background-repeat: no-repeat; background-position: 0 50%; padding-left: 25px; margin: 0 20px 20px 0;">
                  <?= $footerTechSupportIcq; ?>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <?php if ($footerTechSupportEmail): ?>
            <div>
              <a target="_blank" href="mailto:<?= $footerTechSupportEmail; ?>" style="display: inline-block; vertical-align: middle; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 20px; color: #8b8888; background-image: url(http://resourcesfiles.com/panel_delivery/mail.png); background-repeat: no-repeat; background-position: 0 50%; padding-left: 25px; margin: 0 20px 20px 0;">
                <?= $footerTechSupportEmail; ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <div style="text-align: center; padding: 20px;">
      <div style="text-align: center; margin-bottom: 20px;">
        <a target="_blank" href="<?= Url::to(['/unsubscribe/index/', 'email' => $email, 'hash' => $unsubscribeHash]) ?>" style="display: inline-block; vertical-align: middle; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 18px; color: #2b75d1;">
          <?=Yii::_t('partners.main.unsubscribe')?>
        </a>
      </div>
      <?php if ($footerCopyright): ?>
        <div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 18px; color: #8b8888;">
          &copy; <?= date('Y') . ' ' ?> <?= $footerCopyright; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>