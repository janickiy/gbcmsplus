<?php

use mcms\common\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @var string $title
 * @var string $text
 * @var string $email
 * @var string $unsubscribeUrl
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
$serverName = $partnersModule->getServerName();
$cdnHost = 'https://cdn.wap.click';

?>
<table width="100%" bgcolor="#f7f7f7" background="<?= $cdnHost ?>/email/bg_top02.jpg"
       width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f7f7f7;
						background-position: 0 0;
						background-repeat: repeat-x;
						width: 100% !important;
						height: 100% !important;
						border-collapse: collapse;
						color:#000;
						font-size: 14px;
						margin: 0;
						padding: 0;
						font-family: Arial, Helvetica, sans-serif">
  <tr>
    <td valign="top">
      <div style="padding-top: 22px;
									padding-left: 5px;
									padding-right: 5px;">
        <div style="text-align: right;
										
										max-width: 641px;
										
										margin: 0 auto;
										
										font-size: 14px;
										font-weight: 300;
										color: #e4f5ff;
										display: block;">
          <a style="color: #fff" href="<?= $serverName ?>">
            <?= str_replace(['http://', 'https://'], '', $serverName); ?>
          </a>
        </div>
        <div style="max-width: 641px;
										
										
										margin: 0 auto;
										
										position: relative;">
          <a href="<?= $serverName ?>" style="display: inline-block;">
          <img src="<?= $cdnHost ?>/email/img_top.png" alt=""
               style="width: 100%; height: auto; vertical-align: top;">
          </a>
        </div>
        <div style="max-width: 641px;
										margin: 0 auto;
										position: relative;">
          <div style="width: 100%;
												margin-bottom: 5px;
												background-color: #fff;
												position: relative;">
            <div style="padding: 0 4% 4%;">
              <strong style="display: block;
															font-weight: 300;
															margin-bottom: 15px;
															color: #373737;
															line-height: 1.2;
															font-size: 2.25em;"><?= $title; ?></strong>
              <div style="
														font-size: 0.875em;
														
														color: #7c716a;"><?= Yii::$app->getFormatter()->asPartnerDate(time()); ?>
              </div>
            </div>
          </div>
          <div style="max-width: 641px;
												margin: 0 auto 5px;
												background-color: #fff;
												position: relative;">
            <div style="padding: 4%;">
              <?= $text; ?>
            </div>
          </div>
          <div style="width: 100%;
												margin-bottom: 5px;
												background-color: #fff;
												position: relative;">
            <div style="padding: 4%;">
              <?php if ($footerMainQuestionSkype || $footerMainQuestionIcq || $footerMainQuestionEmail) : ?>
                <div style="display: inline-block;
														vertical-align: top;
														margin-right: -4px;
														min-width: 270px;
														margin-bottom: 20px;
														width: 49%;">
												<span style="color: #000;
																display: block;
																margin-bottom: 20px;
																text-transform: uppercase;
																font-size: 1.2em;"><?= Yii::_t('partners.main.main_questions'); ?></span>
                  <div style="width: 100%;">
                    <?php if ($footerMainQuestionSkype) : ?>
                      <a href="skype:<?= $footerMainQuestionSkype ?>?chat" style="display: inline-block;
																	vertical-align: top;
																	text-decoration: none;
																	line-height: 30px;
																	color: #000;
																	font-size: 0.875em;
																	margin-right: -4px;">
                        <img src="<?= $cdnHost ?>/email/skype.png" alt="skype" width="25px"
                             height="25px" style="display: inline-block;vertical-align: middle; margin-right: 3px;">
                        <span
                          style="display: inline-block;vertical-align: middle;"><?= $footerMainQuestionSkype ?></span>

                      </a> <br>
                    <?php endif ?>
                    <?php if ($footerMainQuestionIcq) : ?>
                      <a href="icq:<?= $footerMainQuestionIcq; ?>" style="display: inline-block;
																	vertical-align: top;
																	text-decoration: none;
																	line-height: 30px;
																	color: #000;
																	font-size: 0.875em;
																	margin-right: -4px;">
                        <img src="<?= $cdnHost ?>/email/icq.png" alt="icq" width="25px"
                             height="25px" style="display: inline-block;vertical-align: middle; margin-right: 3px;">
                        <span style="display: inline-block;vertical-align: middle;"><?= $footerMainQuestionIcq ?></span>
                      </a> <br>
                    <?php endif ?>
                    <?php if ($footerMainQuestionEmail) : ?>
                      <a href="mailto:<?= $footerMainQuestionEmail ?>" style="display: inline-block;
																	vertical-align: top;
																	text-decoration: none;
																	line-height: 30px;
																	color: #000;
																	font-size: 0.875em;
																	margin-right: -4px;">
                        <img src="<?= $cdnHost ?>/email/mail.png" alt="email" width="25px"
                             height="25px" style="display: inline-block;vertical-align: middle; margin-right: 3px;">
                        <span
                          style="display: inline-block;vertical-align: middle;"><?= $footerMainQuestionEmail ?></span>
                      </a>
                    <?php endif ?>
                  </div>
                </div>
              <?php endif ?>
              <?php if ($footerTechSupportSkype || $footerTechSupportIcq || $footerTechSupportEmail) : ?>
                <div style="display: inline-block;
														vertical-align: top;
														margin-right: -4px;
														min-width: 270px;
														margin-bottom: 20px;
														width: 49%;">
												<span style="color: #000;
															display: block;
															margin-bottom: 20px;
															text-transform: uppercase;
															font-size: 1.2em;"><?= Yii::_t('partners.main.tech_support'); ?></span>
                  <div style="width: 100%;">
                    <?php if ($footerTechSupportSkype) : ?>
                      <a href="skype:<?= $footerTechSupportSkype ?>?chat" style="display: inline-block;
																	vertical-align: top;
																	text-decoration: none;
																	line-height: 30px;
																	color: #000;
																	font-size: 0.875em;
																	margin-right: -4px;">
                        <img src="<?= $cdnHost ?>/email/skype.png" alt="skype" width="25px"
                             height="25px" style="display: inline-block;vertical-align: middle; margin-right: 3px;">
                        <span
                          style="display: inline-block;vertical-align: middle;"><?= $footerTechSupportSkype ?></span>

                      </a> <br>
                    <?php endif ?>
                    <?php if ($footerTechSupportIcq) : ?>
                      <a href="icq:<?= $footerTechSupportIcq; ?>" style="display: inline-block;
																	vertical-align: top;
																	text-decoration: none;
																	line-height: 30px;
																	color: #000;
																	font-size: 0.875em;
																	margin-right: -4px;">
                        <img src="<?= $cdnHost ?>/email/icq.png" alt="icq" width="25px"
                             height="25px" style="display: inline-block;vertical-align: middle; margin-right: 3px;">
                        <span style="display: inline-block;vertical-align: middle;"><?= $footerTechSupportIcq ?></span>
                      </a> <br>
                    <?php endif ?>
                    <?php if ($footerTechSupportEmail) : ?>
                      <a href="mailto:<?= $footerTechSupportEmail ?>" style="display: inline-block;
																	vertical-align: top;
																	text-decoration: none;
																	line-height: 30px;
																	color: #000;
																	font-size: 0.875em;
																	margin-right: -4px;">
                        <img src="<?= $cdnHost ?>/email/mail.png" alt="email" width="25px"
                             height="25px" style="display: inline-block;vertical-align: middle; margin-right: 3px;">
                        <span
                          style="display: inline-block;vertical-align: middle;"><?= $footerTechSupportEmail ?></span>
                      </a>
                    <?php endif ?>
                  </div>
                </div>
              <?php endif ?>
            </div>
          </div>
          <div style="padding: 30px 0;
												width: 100%;
												margin-bottom: 5px;
												position: relative;">
            <table style="color: #7a7a7a;
													border-collapse: collapse;
													margin: 0;
													width: 100%;
													font-size: 12px;">
              <tr>
                <td>&copy; <?= date('Y') . ' ' ?> <?= $footerCopyright; ?></td>
                <td style="text-align: right;">
                  <?php if (!empty($unsubscribeUrl)): ?>
                    <a href="<?=$unsubscribeUrl ?>" style="color: #7a7a7a; text-decoration: underline;"><?= Yii::_t('partners.main.unsubscribe') ?></a>
                  <?php endif ?>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </td>
  </tr>
</table>