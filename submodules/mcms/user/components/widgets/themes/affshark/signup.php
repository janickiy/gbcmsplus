<?php

use yii\widgets\ActiveForm;
use himiklab\yii2\recaptcha\ReCaptcha;

/* @var $model \mcms\user\models\SignupForm */
/* @var array $currencyList */
/* @var \mcms\user\Module $module */
$module = Yii::$app->getModule('users');
?>

<?php $form = ActiveForm::begin([
    'action' => '/submit/',
    'id' => 'signup-form',
]); ?>
<div class="container">
    <div class="row" style=" margin-top: 30px;">
        <div class="modal-header"
             style=" padding: 9px!important;background: rgba(255, 255, 255, 1);border-radius: 5px 5px 0px 0px;border-bottom:0px;">
            <button type="button" class="modal_close close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            <h1 class="modal-title text-center" id="myModalLabel">Sign up</h1>
        </div>
        <div class="flowlu-form"
             style="max-width: none!important;-webkit-overflow-scrolling: touch;border-radius: 0px 0px 5px 5px;background: rgba(255, 255, 255, 1);">
            <input type="hidden" name="manager_id" value=""><input type="hidden" name="source_id" value="1"><input
                    type="hidden" name="name" value="Wb Form Ask question 2"><input type="hidden" id="flowlu_host"
                                                                                    value="https://timoshuck.flowlu.ru/"><textarea
                    name="nspm" style="display:none !important;"></textarea>
            <div class="flowlu-row">
                <table width="100%" border="0" class="flowlu-table-rows" style="border-collapse: collapse;">
                    <tr>
                        <td width="48%"><label class="flowlu-label" for="flowlu_contact_name">Name <span
                                        class="flowlu-required">*</span></label>
                            <?= $form->field($model, 'username')->textInput([
                                'placeholder' => 'Your name',
                                'style' => 'display: inherit;width: 100%; margin-bottom: 16px;border-radius: 5px;',
                                'id' => 'flowlu_contact_name',
                                'class' => 'flowlu-input'
                            ])->label(false) ?>
                        </td>
                        <td width="4%">&nbsp;</td>
                        <td width="48%"><label class="flowlu-label" for="flowlu_contact_skype">Skype or ICQ <span
                                        class="flowlu-required">*</span></label>
                            <?= $form->field($model, 'skype')->textInput([
                                'placeholder' => 'Your Skype or ICQ',
                                'style' => 'display: inherit;width: 100%; margin-bottom: 16px;border-radius: 5px;',
                                'class' => 'flowlu-input'
                            ])->label(false) ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="flowlu-row">
                <table width="100%" border="0" class="flowlu-table-rows" style="border-collapse: collapse;">
                    <tr>
                        <td width="48%">
                            <?php if ($module->registrationWithCurrency()): ?>
                                <label class="flowlu-label" for="flowlu_contact_company">Currency <span
                                            class="flowlu-required">*</span></label>
                                <?= $form->field($model, 'currency')->dropDownList($currencyList, [
                                    'class' => 'flowlu-input',
                                    'id' => 'flowlu_contact_company',
                                    'style' => 'color: rgba(000, 000, 000, 0.82);',
                                ])->label(false) ?>
                            <?php endif; ?>
                        </td>
                        <td width="4%">&nbsp;</td>
                        <td width="48%"><label class="flowlu-label" for="flowlu_contact_email">E-mail <span
                                        class="flowlu-required">*</span></label>
                            <?= $form->field($model, 'email')->textInput([
                                'placeholder' => 'my@mail.com',
                                'style' => 'display: inherit;width: 100%; margin-bottom: 16px;border-radius: 5px;',
                                'class' => 'flowlu-input',
                                'id' => 'flowlu_contact_email'
                            ])->label(false) ?>
                        </td>
                    </tr>
                </table>
            </div>
            <input class="flowlu-input" type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>"/>

            <div class="flowlu-row"><label class="flowlu-label" for="flowlu_description">Message</label> <textarea
                        id="flowlu_description" rows="5" style="width: 100%; height: 37px;border-radius: 5px;"
                        name="description" class="flowlu-input" value=""></textarea>
                <script type="text/javascript">
                    window.recaptchaRequiredCaption = '<?= Yii::_t('users.signup.required_field') ?>';
                    var onReturnCallback = function (response) {
                    }; // end of onReturnCallback
                </script>
                <?= $form->field($model, 'captcha', ['inputOptions' => ['required' => 'required']])->widget(ReCaptcha::class, [
                    'widgetOptions' => [
                        'id' => 're-captcha-signup-form',
                    ],
                    'jsCallback' => 'onReturnCallback'
                ])->label(false) ?>
            </div>
            Please note: fields marked with <span class="flowlu-required">*</span> should be filled
            <div class="flowlu-notification" style=" color: #fa9d40;">&nbsp;</div>
            <div class="flowlu-row flowlu-row-submit">
                <input type="submit" id="prov" class="btn bounce-green flowlu-submit"
                       style=" background: #fa9d40; border: none;width: 156px; text-align: center; font-size: 24px;"
                       value="Send">
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end() ?>
<div class="close-form show-hide-form"></div>
<a id="success-modal-button" data-toggle="modal" data-target="#success-modal" style="display:none"></a>