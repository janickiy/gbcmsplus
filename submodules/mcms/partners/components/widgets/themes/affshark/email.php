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
?>

<style>@media only screen and (max-width:596px){img{max-width: 100%;} .small-float-center{margin:0 auto!important;float:none!important;text-align:center!important}.small-text-center{text-align:center!important}.small-text-left{text-align:left!important}.small-text-right{text-align:right!important}}@media only screen and (max-width:596px){table.body table.container .hide-for-large{display:block!important;width:auto!important;overflow:visible!important}}@media only screen and (max-width:596px){table.body table.container .row.hide-for-large{display:table!important;width:100%!important}}@media only screen and (max-width:596px){table.body table.container .show-for-large{display:none!important;width:0;mso-hide:all;overflow:hidden}}@media only screen and (max-width:596px){table.body img{width:auto!important;height:auto!important}table.body center{min-width:0!important}table.body .container{width:95%!important}table.body .column,table.body .columns{height:auto!important;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;padding-left:16px!important;padding-right:16px!important}table.body .column .column,table.body .column .columns,table.body .columns .column,table.body .columns .columns{padding-left:0!important;padding-right:0!important}table.body .collapse .column,table.body .collapse .columns{padding-left:0!important;padding-right:0!important}td.small-1,th.small-1{display:inline-block!important;width:8.33333%!important}td.small-2,th.small-2{display:inline-block!important;width:16.66667%!important}td.small-3,th.small-3{display:inline-block!important;width:25%!important}td.small-4,th.small-4{display:inline-block!important;width:33.33333%!important}td.small-5,th.small-5{display:inline-block!important;width:41.66667%!important}td.small-6,th.small-6{display:inline-block!important;width:50%!important}td.small-7,th.small-7{display:inline-block!important;width:58.33333%!important}td.small-8,th.small-8{display:inline-block!important;width:66.66667%!important}td.small-9,th.small-9{display:inline-block!important;width:75%!important}td.small-10,th.small-10{display:inline-block!important;width:83.33333%!important}td.small-11,th.small-11{display:inline-block!important;width:91.66667%!important}td.small-12,th.small-12{display:inline-block!important;width:100%!important}.column td.small-12,.column th.small-12,.columns td.small-12,.columns th.small-12{display:block!important;width:100%!important}.body .column td.small-1,.body .column th.small-1,.body .columns td.small-1,.body .columns th.small-1,td.small-1 center,th.small-1 center{display:inline-block!important;width:8.33333%!important}.body .column td.small-2,.body .column th.small-2,.body .columns td.small-2,.body .columns th.small-2,td.small-2 center,th.small-2 center{display:inline-block!important;width:16.66667%!important}.body .column td.small-3,.body .column th.small-3,.body .columns td.small-3,.body .columns th.small-3,td.small-3 center,th.small-3 center{display:inline-block!important;width:25%!important}.body .column td.small-4,.body .column th.small-4,.body .columns td.small-4,.body .columns th.small-4,td.small-4 center,th.small-4 center{display:inline-block!important;width:33.33333%!important}.body .column td.small-5,.body .column th.small-5,.body .columns td.small-5,.body .columns th.small-5,td.small-5 center,th.small-5 center{display:inline-block!important;width:41.66667%!important}.body .column td.small-6,.body .column th.small-6,.body .columns td.small-6,.body .columns th.small-6,td.small-6 center,th.small-6 center{display:inline-block!important;width:50%!important}.body .column td.small-7,.body .column th.small-7,.body .columns td.small-7,.body .columns th.small-7,td.small-7 center,th.small-7 center{display:inline-block!important;width:58.33333%!important}.body .column td.small-8,.body .column th.small-8,.body .columns td.small-8,.body .columns th.small-8,td.small-8 center,th.small-8 center{display:inline-block!important;width:66.66667%!important}.body .column td.small-9,.body .column th.small-9,.body .columns td.small-9,.body .columns th.small-9,td.small-9 center,th.small-9 center{display:inline-block!important;width:75%!important}.body .column td.small-10,.body .column th.small-10,.body .columns td.small-10,.body .columns th.small-10,td.small-10 center,th.small-10 center{display:inline-block!important;width:83.33333%!important}.body .column td.small-11,.body .column th.small-11,.body .columns td.small-11,.body .columns th.small-11,td.small-11 center,th.small-11 center{display:inline-block!important;width:91.66667%!important}table.body td.small-offset-1,table.body th.small-offset-1{margin-left:8.33333%!important;Margin-left:8.33333%!important}table.body td.small-offset-2,table.body th.small-offset-2{margin-left:16.66667%!important;Margin-left:16.66667%!important}table.body td.small-offset-3,table.body th.small-offset-3{margin-left:25%!important;Margin-left:25%!important}table.body td.small-offset-4,table.body th.small-offset-4{margin-left:33.33333%!important;Margin-left:33.33333%!important}table.body td.small-offset-5,table.body th.small-offset-5{margin-left:41.66667%!important;Margin-left:41.66667%!important}table.body td.small-offset-6,table.body th.small-offset-6{margin-left:50%!important;Margin-left:50%!important}table.body td.small-offset-7,table.body th.small-offset-7{margin-left:58.33333%!important;Margin-left:58.33333%!important}table.body td.small-offset-8,table.body th.small-offset-8{margin-left:66.66667%!important;Margin-left:66.66667%!important}table.body td.small-offset-9,table.body th.small-offset-9{margin-left:75%!important;Margin-left:75%!important}table.body td.small-offset-10,table.body th.small-offset-10{margin-left:83.33333%!important;Margin-left:83.33333%!important}table.body td.small-offset-11,table.body th.small-offset-11{margin-left:91.66667%!important;Margin-left:91.66667%!important}table.body table.columns td.expander,table.body table.columns th.expander{display:none!important}table.body .right-text-pad,table.body .text-pad-right{padding-left:10px!important}table.body .left-text-pad,table.body .text-pad-left{padding-right:10px!important}table.menu{width:100%!important}table.menu td,table.menu th{width:auto!important;display:inline-block!important}table.menu.small-vertical td,table.menu.small-vertical th,table.menu.vertical td,table.menu.vertical th{display:block!important}table.menu[align=center]{width:auto!important}table.button.expand{width:100%!important}}</style>
<table class="body" style="Margin:0;background:#f3f3f3;border-collapse:collapse;border-spacing:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;height:100%;line-height:19px;margin:0;padding:0;text-align:left;vertical-align:top;width:100%">
  <tbody>
  <tr style="padding:0;text-align:left;vertical-align:top">
    <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:19px;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
      <center data-parsed="" style="min-width:580px;width:100%">
        <table align="center" class="container float-center" style="Margin:0 auto;background:#0a0a0a;border-collapse:collapse;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:580px">
          <tbody>
          <tr style="padding:0;text-align:left;vertical-align:top">
            <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:19px;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
              <table class="row" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                <tbody>
                <tr style="padding:0;text-align:left;vertical-align:top">
                  <th class="small-12 large-12 columns first last" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:16px;padding-right:16px;padding-top:16px;text-align:left;width:564px">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:left;vertical-align:top">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:left"><img class="float-right main_logo" src="http://www.updates-whatsapp.com/app/email/img/aff_yellow.png" style="-ms-interpolation-mode:bicubic;clear:both;display:block;float:right;max-width:100%;outline:0;text-align:right;text-decoration:none;width:40%"></th>
                        <th class="expander" style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0!important;text-align:left;visibility:hidden;width:0"></th>
                      </tr>
                      </tbody>
                    </table>
                  </th>
                </tr>
                </tbody>
              </table>
              <table class="row" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                <tbody>
                <tr style="padding:0;text-align:left;vertical-align:top">
                  <th class="small-12 large-12 columns first last" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:16px;padding-right:16px;padding-top:16px;text-align:left;width:564px; background: rgb(0,0,0);background: -moz-linear-gradient(left, rgba(0,0,0,1) 0%, rgba(255,171,20,1) 45%, rgba(255,171,20,1) 55%, rgba(0,0,0,1) 100%);background: -webkit-linear-gradient(left, rgba(0,0,0,1) 0%,rgba(255,171,20,1) 45%,rgba(255,171,20,1) 55%,rgba(0,0,0,1) 100%);background: linear-gradient(to right, rgba(0,0,0,1) 0%,rgba(255,171,20,1) 45%,rgba(255,171,20,1) 55%,rgba(0,0,0,1) 100%);filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#000000', endColorstr='#000000',GradientType=1">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:left;vertical-align:top">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:left">
                          <h1 class="text-center" style="Margin:0;Margin-bottom:10px;color:inherit;font-family:Open Sans,sans-serif;font-size:34px;font-weight:400;line-height:1.3;margin:0;margin-bottom:10px;padding:0;text-align:center;word-wrap:normal">

                            <!--  email topic begin -->

                            <?= $title; ?>

                            <!--  email topic end  -->

                          </h1>
                        </th>
                        <th class="expander" style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0!important;text-align:left;visibility:hidden;width:0"></th>
                      </tr>
                      </tbody>
                    </table>
                  </th>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>
          </tbody>
        </table>
        <table align="center" class="container float-center" style="Margin:0 auto;background:#0a0a0a;border-collapse:collapse;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:580px">
          <tbody>
          <tr style="padding:0;text-align:left;vertical-align:top">
            <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:19px;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word">
              <table class="row" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%">
                <tbody>
                <tr style="padding:0;text-align:left;vertical-align:top">
                  <th class="small-0 large-1 columns first" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:16px;padding-right:8px;padding-top:16px;text-align:left;width:32.33px">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:left;vertical-align:top">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:left"></th>
                      </tr>
                      </tbody>
                    </table>
                  </th>
                  <th class="main_text small-12 large-10 columns" valign="middle" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:8px;padding-right:8px;padding-top:16px;text-align:left;width:467.33px">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:left;vertical-align:top">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:left">
                          <p style="Margin:0;Margin-bottom:10px;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;margin-bottom:10px;padding:0;text-align:justify">
                            <!--  email text beging -->
                            <?= $text; ?>
                            <!--  email text beging end -->
                          </p>
                        </th>

                      </tr>
                      </tbody>
                    </table>
                  </th>
                  <th class="small-0 large-1 columns last" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:8px;padding-right:16px;padding-top:16px;text-align:left;width:32.33px">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:left;vertical-align:top">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:left"></th>
                      </tr>
                      </tbody>
                    </table>
                  </th>
                </tr>
                </tbody>
              </table>
              <table class="spacer" style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top">
                <tbody>
                <tr style="padding:0;text-align:left;vertical-align:top">
                  <td height="50>px" width="580>px" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#fefefe;font-family:Open Sans,sans-serif;font-size:0;font-weight:400;hyphens:auto;line-height:50>px;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word;background: rgb(0,0,0);background: -moz-linear-gradient(left, rgba(0,0,0,1) 0%, rgba(255,171,20,1) 45%, rgba(255,171,20,1) 55%, rgba(0,0,0,1) 100%);background: -webkit-linear-gradient(left, rgba(0,0,0,1) 0%,rgba(255,171,20,1) 45%,rgba(255,171,20,1) 55%,rgba(0,0,0,1) 100%);background: linear-gradient(to right, rgba(0,0,0,1) 0%,rgba(255,171,20,1) 45%,rgba(255,171,20,1) 55%,rgba(0,0,0,1) 100%);filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#000000', endColorstr='#000000',GradientType=1">&nbsp;</td>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>
          </tbody>
        </table>
        <table align="center" class="container float-center" style="Margin:0 auto;background:#0a0a0a;border-collapse:collapse;border-spacing:0;float:none;margin:0 auto;padding:0;text-align:center;vertical-align:top;width:580px">
          <tbody>
          <tr style="padding:0;text-align:center;vertical-align:top">
            <td style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;hyphens:auto;line-height:19px;margin:0;padding:0;text-align:center;vertical-align:top;word-wrap:break-word">
              <table class="row" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:center;vertical-align:top;width:100%">
                <tbody>
                <tr style="padding:0;text-align:center;vertical-align:top">
                  <th class="small-12 large-4 columns first" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:16px;padding-right:8px;padding-top:16px;text-align:center;width:177.33px">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:center;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:center;vertical-align:top">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:center"></th>
                      </tr>
                      </tbody>
                    </table>
                  </th>
                  <th class="small-12 large-4 columns" valign="middle" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:8px;padding-right:8px;padding-top:16px;text-align:center;width:100%">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:center;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:center;vertical-align:top;width: 100%">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:left;">
                          <h6 class="text-center footer_headers" style="Margin:0;Margin-bottom:10px;color:#FFAB14;font-family:Open Sans,sans-serif;font-size:18px;font-weight:400;line-height:1.3;margin:0;margin-bottom:10px;padding:0;text-align:left;word-wrap:normal">Main questions:</h6>
                          <a href="mailto:<?= $footerMainQuestionEmail ?>" class="text-center" style="Margin:0;Margin-bottom:10px;color:#fff !important;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;margin-bottom:10px;padding:0;text-align:left"><?= $footerMainQuestionEmail ?></a>
                        </th>
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:right;">
                          <h6 class="text-center footer_headers" style="Margin:0;Margin-bottom:10px;color:#FFAB14;font-family:Open Sans,sans-serif;font-size:18px;font-weight:400;line-height:1.3;margin:0;margin-bottom:10px;padding:0;text-align:right;word-wrap:normal">Technical support:</h6>
                          <a href="mailto:<?= $footerTechSupportEmail ?>" class="text-center" style="Margin:0;Margin-bottom:10px;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;margin-bottom:10px;padding:0;text-align:right"><?= $footerTechSupportEmail ?></a>
                        </th>
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:right;width: 15px">
                        </th>
                      </tr>

                      </tbody>
                    </table>
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:center;vertical-align:center;width:100%;margin-top: 50px;">
                      <tbody>
                      <tr style="padding:0;text-align:center;vertical-align:top;width: 100%;">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:15px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:center;">
                          <a href="<?= Url::to(['/unsubscribe/index/', 'email' => $email, 'hash' => $unsubscribeHash]) ?>" style="color:#969292" target="_blank">Unsubscribe</a>
                        </th>
                      </tr>
                      </tbody>
                    </table>
                    <table class="spacer" style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top">
                      <tbody>
                      <tr style="padding:0;text-align:left;vertical-align:top">
                        <td height="50>px" width="580>px" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#fefefe;font-family:Open Sans,sans-serif;font-size:0;font-weight:400;hyphens:auto;line-height:50px;margin:0;padding:0;text-align:left;vertical-align:top;word-wrap:break-word;"></td>
                      </tr>
                      </tbody>
                    </table>
                  </th>
                  <th class="small-12 large-4 columns last" style="Margin:0 auto;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0 auto;padding:0;padding-bottom:16px;padding-left:8px;padding-right:16px;padding-top:16px;text-align:left;width:177.33px">
                    <table style="border-collapse:collapse;border-spacing:0;padding:0;text-align:left;vertical-align:top;width:100%">
                      <tbody>
                      <tr style="padding:0;text-align:left;vertical-align:top">
                        <th style="Margin:0;color:#fefefe;font-family:Open Sans,sans-serif;font-size:16px;font-weight:400;line-height:19px;margin:0;padding:0;text-align:left"></th>
                      </tr>
                      </tbody>
                    </table>
                  </th>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>
          </tbody>
        </table>
      </center>
    </td>
  </tr>
  </tbody>
</table>
