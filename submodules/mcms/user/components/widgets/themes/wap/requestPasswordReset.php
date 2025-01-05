<?php

use mcms\user\components\widgets\recaptcha\ReCaptcha2 as Recaptcha;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var \mcms\user\Module $module */
/* @var $model \mcms\user\models\PasswordResetRequestForm */
$module = Yii::$app->getModule('users');
$pagesModule = Yii::$app->getModule('pages');
$viewBasePath = '@app/themes/wap/default/';
?>

<?php $form = ActiveForm::begin([
  'id' => 'password-reset-request-form',
  'action' => Url::to(['users/api/request-password-reset']),
  'options' => ['class' => 'form form-restore login-form form-request-password-reset', 'autocomplete' => 'off']]); ?>
<div class="form__backdrop"></div>
<div class="container form__box">
     <div class="form__image-box">
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'common',
            'pageCode' => 'main',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/logo_light',
            'imageOptions' => ['class' => 'form__image','alt' => "Логотип Wapclick"]
          ])->getResult(); ?>
        </div>
        <div class="form__container">
            <button class="form__close-btn" type="button">
              <?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'translate',
                'pageCode' => 'close',
                'fieldCode' => 'text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult()?>
                <svg class="form__close-btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="m1 1 9 9m9 9-9-9m0 0 9-9L1 19" stroke-width="1.5"></path>
                </svg>
            </button>
            <h2 class="form__heading"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'translate',
                'pageCode' => 'recover_password',
                'fieldCode' => 'text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult()?></h2>
          <?php if (!$module->isRestorePasswordSupport()) { ?>
            <p class="form__description"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'translate',
                'pageCode' => 'text_email_instructions_reset_password',
                'fieldCode' => 'text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult()?></p>
          <?= $form->field($model, 'email', ['options' => ['class' => 'form__field form-group input-email'],
            'template' => '<svg class="form__field-icon" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.593 16.5H2.407c-.476 0-.952-.373-.952-.933V3.5c0-.467.38-1 .952-1h17.186c.476 0 .953.44.953 1v12.067c0 .56-.381.933-.953.933Z" stroke="#28282E" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="m18.273 5-6.788 6.766c-.324.312-.728.312-.97 0L3.727 5" stroke="#28282E" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>'."{label}\n{input}\n{hint}\n{error}"])
            ->textInput(['placeholder' => $pagesModule->api('pagesWidget', [
              'categoryCode' => 'translate',
              'pageCode' => 'email',
              'fieldCode' => 'text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/field_value'
            ])->getResult(), 'class' => 'form__field-input form-control'])->label(false) ?>
  
          <?php if ($model->shouldUseCaptcha()) { ?>
            <?= $form->field($model, 'captcha', ['options' => ['class' => false],'enableAjaxValidation' => false])->widget(ReCaptcha::class,[
              //'jsCallback' => 'function(response){console.log(response); console.log(this) $(document).trigger("captchaValid", true);}',
              'jsCallback' => 'resetPassCallback',
              //'jsExpiredCallback' => '(function(){$(document).trigger("captchaValid", false);})',
              'jsExpiredCallback' => 'resetPassExpiredCallback',
              'siteKey' => Yii::$app->reCaptcha->siteKeyV2, // unnecessary is reCaptcha component was set up
              'widgetOptions' => ['id' => 'recapcha-'.$form->getId()]
            ])->label(false) ?>
          <?php }else { ?>
              <span id="recapcha-<?= $form->id ?>" class="checkbox" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
            <?= Html::activeHiddenInput($model, 'captcha') ?>
          <?php } ?>
  
          <?= Html::submitButton($pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'send',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult(), ['class' => 'btn form__btn']); ?>
            <div class="form__bottom-block">
              <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'translate',
                'pageCode' => 'dont_have_an_account_yet',
                'fieldCode' => 'text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult() ?>&nbsp;-&nbsp;<a class="form__bottom-link" href="#"><?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'translate',
                  'pageCode' => 'registration',
                  'fieldCode' => 'text',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/field_value'
                ])->getResult()?></a>
            </div>
          <?php }else{ ?>
            <?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'translate',
              'pageCode' => 'for_change_password_please_contact_administrator',
              'fieldCode' => 'text',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/field_value'
            ])->getResult() ?>
          <?php } ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
<?php
$js = <<<JS
function resetPassExpiredCallback(){
        $('#recapcha-{$form->getId()}').val("");
    $(document).trigger("captchaValid", false);
}
function resetPassCallback(response){
    $('#recapcha-{$form->getId()}').val(response);
    $(document).trigger("captchaValid", true);
}
JS;

$this->registerJs($js,\yii\web\View::POS_BEGIN);
