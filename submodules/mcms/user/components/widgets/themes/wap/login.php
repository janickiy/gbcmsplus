<?php

use mcms\user\components\widgets\recaptcha\ReCaptcha2 as ReCaptcha;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $model \mcms\user\models\LoginForm */
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


?>

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'action' => Url::to(['users/api/login']),
    'options' => ['class' => 'form form-signin form-login-signin', 'autocomplete' => 'off'],
]); ?>
    <div class="form__backdrop"></div>
    <div class="container form__box">
        <div class="form__image-box">
            <?= $pagesModule->api('pagesWidget', [
                'categoryCode' => 'common',
                'pageCode' => 'main',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/logo_light',
                'imageOptions' => ['class' => 'form__image', 'alt' => "Логотип Wapclick"]
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
                    'pageCode' => 'login_modal_title',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult() ?></h2>
            <?= $form->field($model, 'username', ['options' => ['class' => 'form__field form-group input-email'],
                'template' => '<svg class="form__field-icon" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.777 4.722c0 .938-.29 1.754-.82 2.329C10.43 7.62 9.625 8 8.498 8c-1.126 0-1.93-.38-2.456-.95-.532-.574-.821-1.39-.821-2.328s.289-1.753.82-2.328c.527-.57 1.331-.95 2.457-.95 1.127 0 1.931.38 2.457.95.532.575.821 1.39.821 2.328ZM1.461 16.5c.221-3.216 2.559-5.611 5.36-5.611h3.358c2.8 0 5.138 2.395 5.36 5.611H1.46Z"
                      stroke="#28282E" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>' . "{label}\n{input}\n{hint}\n{error}"])
                ->textInput(['placeholder' => $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'translate',
                    'pageCode' => 'username_email',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult(), 'class' => 'form__field-input form-control'])
                ->label(false) ?>

            <?= $form->field($model, 'password', ['options' => ['class' => 'form__field form-group input-password'],
                'template' => '<svg class="form__field-icon" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                ])->getResult(), 'class' => 'form__field-input form-control'])->label(false) ?>
            <div class="form__controls">
                <?= $form->field($model, 'rememberMe', [
                    'options' => ['class' => 'form__controls-remember'],
                    'template' => '{input}<label for="loginform-rememberme" class="form__controls-remember-label">' . $pagesModule->api('pagesWidget', [
                            'categoryCode' => 'translate',
                            'pageCode' => 'remember_me',
                            'fieldCode' => 'text',
                            'viewBasePath' => $viewBasePath,
                            'view' => 'widgets/field_value'
                        ])->getResult() . '</label>',
                ])->checkbox(['label' => null, 'class' => 'form__controls-remember-checkbox visuallyhidden']); ?>
                <a class="form__controls-forget btn--forget-form" href="#"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'forgot_your_password',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                    ])->getResult() ?></a>
            </div>


            <?php if ($model->shouldUseCaptcha()) : ?>
                <?= $form->field($model, 'captcha', ['options' => ['class' => false], 'enableAjaxValidation' => false])->widget(ReCaptcha::class, [
                    'jsCallback' => 'signupCallback',
                    'jsExpiredCallback' => 'signupExpiredCallback',
                    'siteKey' => Yii::$app->reCaptcha->siteKeyV2, // unnecessary is reCaptcha component was set up
                    'widgetOptions' => ['id' => "recapcha-{$form->getId()}"]

//          'jsCallback' => '(function(){$(document).trigger("captchaValid", true);})',
//          'jsExpiredCallback' => '(function(){$(document).trigger("captchaValid", false);})',
//          'widgetOptions' => ['id' => 'recapcha-login-form']
                ])->label(false) ?>
            <?php else: ?>
                <span id="recapcha-<?= $form->id ?>" class="checkbox"
                      data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
                <?= Html::activeHiddenInput($model, 'captcha') ?>
            <?php endif; ?>
            <?= Html::submitButton($pagesModule->api('pagesWidget', [
                'categoryCode' => 'translate',
                'pageCode' => 'sign_in',
                'fieldCode' => 'text',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
            ])->getResult(), ['class' => 'btn orm__btn']); ?>
            <div class="form__bottom-block">
                <?= $pagesModule->api('pagesWidget', [
                    'categoryCode' => 'translate',
                    'pageCode' => 'have_an_account',
                    'fieldCode' => 'text',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                ])->getResult() ?>&nbsp;-&nbsp;<a class="form__bottom-link btn--registration-form"
                                                  href="#"><?= $pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'registration',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                    ])->getResult() ?></a>
            </div>
        </div>

    </div>
<?php ActiveForm::end() ?>