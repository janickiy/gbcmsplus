<?php

use mcms\user\components\widgets\recaptcha\ReCaptcha2 as Recaptcha;
use mcms\user\models\UserContact;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $model \mcms\user\models\SignupForm */
/* @var array $currencyList */
/* @var \mcms\user\Module $module */
/* @var $this \mcms\common\web\View */

$pagesModule = Yii::$app->getModule('pages');
$viewBasePath = '@app/themes/wap/default/';
$requiredMessage = $pagesModule->api('pagesWidget', [
    'categoryCode' => 'translate',
    'pageCode' => 'required_field',
    'fieldCode' => 'text',
    'viewBasePath' => $viewBasePath,
    'view' => 'widgets/field_value'
])->getResult();
$js = <<<JS
window.recaptchaRequiredCaption = '{$requiredMessage}';
JS;

$this->registerJs($js, \mcms\common\web\View::POS_BEGIN);

$module = Yii::$app->getModule('users');

?>

<?php $form = ActiveForm::begin([
    'id' => 'signup-form',
    'action' => Url::to(['users/api/signup']),
    'options' => ['class' => 'form form-signup form-login-signup', 'autocomplete' => 'off'],
    //'enableAjaxValidation' => true
]); ?>
    <div class="form__backdrop"></div>
    <div class="container form__box">
        <div class="form__image-box">
            <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'main',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/logo_light',
                'imageOptions' => ['class' => 'form__image', 'alt' => "WAP.Click логотип"]
            ])->getResult(); ?>
        </div>
        <div class="form__container">
            <button class="form__close-btn" type="button">
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'translate',
                    'pageCode' => 'close',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult() ?>
                <svg class="form__close-btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="m1 1 9 9m9 9-9-9m0 0 9-9L1 19" stroke-width="1.5"></path>
                </svg>
            </button>
            <h2 class="form__heading"><?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'translate',
                    'pageCode' => 'publisher_signup_form',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult() ?></h2>
            <?php if ($module->isRegistrationTypeClosed()) { ?>
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'translate',
                    'pageCode' => 'closed',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult() ?>
            <?php } else { ?>
                <?= $form->field($model, 'email', ['options' => ['class' => 'form__field form-group input-email'],
                    'template' => '<svg class="form__field-icon" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.593 16.5H2.407c-.476 0-.952-.373-.952-.933V3.5c0-.467.38-1 .952-1h17.186c.476 0 .953.44.953 1v12.067c0 .56-.381.933-.953.933Z" stroke="#28282E" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="m18.273 5-6.788 6.766c-.324.312-.728.312-.97 0L3.727 5" stroke="#28282E" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>' . "{label}\n{input}\n{hint}\n{error}"])
                    ->textInput(['placeholder' => $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'email',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                    ])->getResult(), 'class' => 'form__field-input form-control'])->label(false) ?>

                <?= $form->field($model, 'password', ['options' => ['class' => 'form__field form-group input-password'],
                    'template' => ' <svg class="form__field-icon" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
          <mask id="a" fill="#fff">
            <path d="M11.179 7.882c.659-1.846.197-3.955-1.253-5.406-2.044-2.043-5.405-2.043-7.449 0-2.044 2.044-2.044 5.406 0 7.45a5.215 5.215 0 0 0 6.263.857"></path>
          </mask>
          <path d="M10.237 7.546a1 1 0 0 0 1.883.672l-1.883-.672Zm-1.004 4.106a1 1 0 1 0-.986-1.74l.986 1.74Zm2.887-3.434c.791-2.214.233-4.729-1.487-6.449L9.22 3.184c1.18 1.18 1.545 2.884 1.018 4.362l1.883.672ZM10.633 1.77C8.2-.665 4.204-.665 1.77 1.77l1.414 1.415c1.653-1.653 4.382-1.653 6.035 0l1.414-1.415Zm-8.863 0c-2.434 2.434-2.434 6.43 0 8.864l1.414-1.415c-1.653-1.653-1.653-4.381 0-6.034L1.77 1.769Zm0 8.864a6.215 6.215 0 0 0 7.463 1.02l-.986-1.74a4.215 4.215 0 0 1-5.063-.695L1.77 10.633Z"
                fill="#28282E" mask="url(#a)"></path>
          <mask id="b" fill="#fff">
            <path d="m11.179 7.882 4.878 4.878v3.296H13.42"></path>
          </mask>
          <path d="M11.886 7.175a1 1 0 1 0-1.414 1.414l1.414-1.414Zm4.17 5.585h1a1 1 0 0 0-.292-.707l-.707.707Zm0 3.296v1a1 1 0 0 0 1-1h-1Zm-2.636-1a1 1 0 0 0 0 2v-2ZM10.472 8.59l4.878 4.878 1.414-1.414-4.878-4.878-1.414 1.414Zm4.585 4.171v3.296h2V12.76h-2Zm1 2.296H13.42v2h2.637v-2Z"
                fill="#28282E" mask="url(#b)"></path>
          <path d="M9.465 10.783H8.74M9.465 10.783V12.1M10.783 12.1H9.465M10.784 12.1v1.32M12.103 13.42h-1.319M12.102 13.42v1.318M13.421 14.738h-1.319M13.42 16.056v-1.318M5.668 4.85a.818.818 0 1 1-1.637 0 .818.818 0 0 1 1.637 0Z" stroke="#28282E" stroke-miterlimit="10"
                stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>' . "{label}\n{input}\n{hint}\n{error}"])
                    ->passwordInput(['placeholder' => $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'password',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                    ])->getResult(), 'class' => 'form__field-input  form-control'])->label(false) ?>

                <?= $form->field($model, 'passwordRepeat', ['options' => ['class' => 'form__field form-group input-password'],
                    'template' => ' <svg class="form__field-icon" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
          <mask id="a" fill="#fff">
            <path d="M11.179 7.882c.659-1.846.197-3.955-1.253-5.406-2.044-2.043-5.405-2.043-7.449 0-2.044 2.044-2.044 5.406 0 7.45a5.215 5.215 0 0 0 6.263.857"></path>
          </mask>
          <path d="M10.237 7.546a1 1 0 0 0 1.883.672l-1.883-.672Zm-1.004 4.106a1 1 0 1 0-.986-1.74l.986 1.74Zm2.887-3.434c.791-2.214.233-4.729-1.487-6.449L9.22 3.184c1.18 1.18 1.545 2.884 1.018 4.362l1.883.672ZM10.633 1.77C8.2-.665 4.204-.665 1.77 1.77l1.414 1.415c1.653-1.653 4.382-1.653 6.035 0l1.414-1.415Zm-8.863 0c-2.434 2.434-2.434 6.43 0 8.864l1.414-1.415c-1.653-1.653-1.653-4.381 0-6.034L1.77 1.769Zm0 8.864a6.215 6.215 0 0 0 7.463 1.02l-.986-1.74a4.215 4.215 0 0 1-5.063-.695L1.77 10.633Z"
                fill="#28282E" mask="url(#a)"></path>
          <mask id="b" fill="#fff">
            <path d="m11.179 7.882 4.878 4.878v3.296H13.42"></path>
          </mask>
          <path d="M11.886 7.175a1 1 0 1 0-1.414 1.414l1.414-1.414Zm4.17 5.585h1a1 1 0 0 0-.292-.707l-.707.707Zm0 3.296v1a1 1 0 0 0 1-1h-1Zm-2.636-1a1 1 0 0 0 0 2v-2ZM10.472 8.59l4.878 4.878 1.414-1.414-4.878-4.878-1.414 1.414Zm4.585 4.171v3.296h2V12.76h-2Zm1 2.296H13.42v2h2.637v-2Z"
                fill="#28282E" mask="url(#b)"></path>
          <path d="M9.465 10.783H8.74M9.465 10.783V12.1M10.783 12.1H9.465M10.784 12.1v1.32M12.103 13.42h-1.319M12.102 13.42v1.318M13.421 14.738h-1.319M13.42 16.056v-1.318M5.668 4.85a.818.818 0 1 1-1.637 0 .818.818 0 0 1 1.637 0Z" stroke="#28282E" stroke-miterlimit="10"
                stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>' . "{label}\n{input}\n{hint}\n{error}"])
                    ->passwordInput(['placeholder' => $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'password_repeat',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                    ])->getResult(), 'class' => 'form__field-input  form-control'])->label(false) ?>

                <?= $module->registrationWithLanguage()
                    ? $form->field($model, 'language', ['options' => ['class' => 'form__field form-group select input-language']])
                        ->dropDownList(['ru' => $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'translate',
                            'pageCode' => 'russian',
                            'fieldCode' => 'text',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/field_value'
                        ])->getResult(), 'en' => $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'translate',
                            'pageCode' => 'english',
                            'fieldCode' => 'text',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/field_value'
                        ])->getResult()],
                            ['placeholder' => $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'translate',
                                'pageCode' => 'language',
                                'fieldCode' => 'text',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/field_value'
                            ])->getResult(), 'style' => 'height: 49px;border: 0;width: 85%;background: none;padding-left: 0'])->label(false)
                    : '' ?>
                <?php if ($module->registrationWithCurrency()) { ?>
                    <div class="currency">
                        <h3 class="currency__heading"><?= $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'translate',
                                'pageCode' => 'currency',
                                'fieldCode' => 'text',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/field_value'
                            ])->getResult() ?></h3>
                        <?php echo $form->field($model, 'currency', ['options' => ['class' => 'form-group select input-currency'],
                            'template' => "{label}\n{input}\n{hint}\n{error}"])
                            ->radioList($currencyList, ['placeholder' => $pagesModule->api('pagesWidget', [
                                'categoryCode' => 'translate',
                                'pageCode' => 'currency',
                                'fieldCode' => 'text',
                                'viewBasePath' => $viewBasePath,
                                'view' => 'widgets/field_value'
                            ])->getResult(),
                                'tag' => 'ul',
                                'class' => 'currency__list',
                                'item' => function ($index, $label, $name, $checked, $value) {
                                    Yii::debug(['index' => $index, 'label' => $label, 'name' => $name, 'checked' => $checked, 'value' => $value]);
                                    $input = Html::radio($name, $checked, ['class' => 'currency__item-radio visuallyhidden', 'id' => 'currency-ruble-' . $value, 'value' => $value]);
                                    $input .= Html::label($label, 'currency-ruble-' . $value, ['class' => 'currency__item-label']);
                                    return Html::tag('li', $input, ['class' => 'currency__item']);
                                }])->label(false) ?>
                    </div>
                <?php } ?>

                <?= $form->field($model, 'contact_type', ['options' => ['class' => 'form__field form-group select input-contacts'],
                    'template' => '<svg class="form__field-icon" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M19.472.337a1.485 1.485 0 0 0-1.498-.23L.912 6.927c-.276.111-.51.304-.673.551A1.444 1.444 0 0 0 .283 9.13c.176.238.42.418.702.515l3.682 1.266 1.995 6.53c.004.013.016.022.022.034a.467.467 0 0 0 .304.276c.01.004.016.012.026.014h.005l.003.001a.43.43 0 0 0 .222-.011c.008-.002.015-.002.024-.005a.471.471 0 0 0 .182-.115c.006-.007.015-.007.02-.013l2.87-3.136 4.188 3.21a1.474 1.474 0 0 0 2.336-.857l3.107-15.102a1.431 1.431 0 0 0-.5-1.401ZM7.703 12.15l-.673 3.24-1.405-4.6L12.592 7.2l-4.76 4.712a.468.468 0 0 0-.129.239Zm8.228 4.499a.506.506 0 0 1-.33.376.504.504 0 0 1-.49-.073l-4.536-3.479a.48.48 0 0 0-.644.057L7.934 15.71l.672-3.231 6.847-6.78a.47.47 0 0 0-.229-.791.48.48 0 0 0-.328.04l-9.869 5.09-3.73-1.285a.5.5 0 0 1-.344-.463.498.498 0 0 1 .318-.489L18.33.981a.515.515 0 0 1 .532.082.493.493 0 0 1 .173.488l-3.105 15.1v-.001Z"
                fill="#28282E"></path>
        </svg>' . "{label}\n{input}\n{hint}\n{error}"])
                    ->dropDownList(UserContact::getTypes(true),
                        ['placeholder' => $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'translate',
                            'pageCode' => 'contact_type',
                            'fieldCode' => 'text',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/field_value'
                        ])->getResult(), 'style' => 'height: 49px;border: 0;width: 85%;background: none;padding-left: 0'])->label(false) ?>

                <?= $form->field($model, 'contact_data', ['options' => ['class' => 'form__field form-group input-contacts'],
                    'template' => '<svg class="form__field-icon" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M11.777 4.722c0 .938-.29 1.754-.82 2.329C10.43 7.62 9.625 8 8.498 8c-1.126 0-1.93-.38-2.456-.95-.532-.574-.821-1.39-.821-2.328s.289-1.753.82-2.328c.527-.57 1.331-.95 2.457-.95 1.127 0 1.931.38 2.457.95.532.575.821 1.39.821 2.328ZM1.461 16.5c.221-3.216 2.559-5.611 5.36-5.611h3.358c2.8 0 5.138 2.395 5.36 5.611H1.46Z" stroke="#28282E" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> </svg>' . "{label}\n{input}\n{hint}\n{error}"])
                    ->textInput(['placeholder' => $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'contact_data',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                    ])->getResult(), 'class' => 'form__field-input form-control'])->label(false) ?>

                <?php if ($model->isRecaptchaValidator) { ?>
                    <?= $form->field($model, 'captcha', ['options' => ['class' => false], 'enableAjaxValidation' => false])->widget(ReCaptcha::class, [
//      'jsCallback' => '(function(){$(document).trigger("captchaValid", true);})',
//      'jsExpiredCallback' => '(function(){$(document).trigger("captchaValid", false);})',
                        'jsCallback' => 'signupCallback',
                        'jsExpiredCallback' => 'signupExpiredCallback',
                        'siteKey' => Yii::$app->reCaptcha->siteKeyV2, // unnecessary is reCaptcha component was set up
                        'widgetOptions' => ['id' => "recapcha-{$form->getId()}"]
                    ])->label(false) ?>
                <?php } ?>


                <?= Html::submitButton($pagesModule->api('pagesWidget', [
                    'categoryCode' => 'translate',
                    'pageCode' => 'register',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult(), ['class' => 'btn form__btn']); ?>

            <?php } ?>
            <div class="form__bottom-block">
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'translate',
                    'pageCode' => 'have_an_account',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult() ?>&nbsp;-&nbsp;<a class="form__bottom-link"
                                                  href="#"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'sign_in',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                    ])->getResult() ?></a>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>
<?php
$js = <<<JS
function signupExpiredCallback(){
        $('#recapcha-{$form->getId()}').val("");
    $(document).trigger("captchaValid", false);
}
function signupCallback(response){
    $('#recapcha-{$form->getId()}').val(response);
    $(document).trigger("captchaValid", true);
}
JS;

$this->registerJs($js, \yii\web\View::POS_BEGIN);
