<?php
/**
 * @var array $currencyList
 * @var $model
 */

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/** @var \mcms\user\Module $module */
$module = Yii::$app->getModule('users');

$languages = ['ru' => Yii::_t('users.signup.russian'), 'en' => Yii::_t('users.signup.english')];
?>
<?php
$form = ActiveForm::begin([
    'id' => 'signup-form',
    'action' => Url::to(['users/api/signup']),
    'options' => ['class' => 'form-modal', 'autocomplete' => 'off']
]);
?>

<?php if ($module->isRegistrationTypeClosed()): ?>
    <?= Yii::_t('users.signup.closed') ?>
<?php else: ?>
    <?= $form->field($model, 'email', [
        'template' => "<div class='form-group-icon'>@</div><div class='form-group-field'>{input}</div>{hint}\n{error}",
    ])->textInput([
        'placeholder' => Yii::_t('users.signup.email'),
    ])->label(false) ?>

    <?= $form->field($model, 'password', [
        'template' => "<div class='form-group-icon'><i class='fa fa-unlock-alt' aria-hidden='true'></i></div><div class='form-group-field'>{input}</div>{hint}\n{error}",
    ])->passwordInput([
        'placeholder' => Yii::_t('users.signup.password'),
    ])->label(false) ?>
    <?= $form->field($model, 'passwordRepeat', [
        'template' => "<div class='form-group-icon'><i class='fa fa-unlock-alt' aria-hidden='true'></i></div><div class='form-group-field'>{input}</div>{hint}\n{error}",
    ])->passwordInput([
        'placeholder' => Yii::_t('users.signup.passwordRepeat'),
    ])->label(false) ?>
    <?= $form->field($model, 'skype', [
        'template' => "<div class='form-group-icon'><i class='fa fa-commenting-o' aria-hidden='true'></i></div><div class='form-group-field'>{input}</div>{hint}\n{error}",
    ])->textInput([
        'placeholder' => Yii::_t('users.signup.skype'),
    ])->label(false) ?>
    <?php if ($module->registrationWithLanguage()): ?>
        <?= $form->field($model, 'language', [
            'template' => "<div class='form-group-icon'><i class='fa fa-globe' aria-hidden='true'></i></div>
        <div class='form-group-field'><a href='#' class='form-select'>
          <span class='text'>" . $languages[Yii::$app->language] . "</span>
          <span class='icon'><i class='fa fa-chevron-down' aria-hidden='true'></i></span>
        </a>{input}</div>{hint}\n{error}",
        ])->dropDownList($languages, [
            'placeholder' => Yii::_t('users.signup.language'),
            'class' => 'form-control',
        ])->label(false) ?>
    <?php endif ?>
    <?php if ($module->registrationWithCurrency()): ?>
        <?= $form->field($model, 'currency', [
            'template' => "<div class='form-group-icon'><i class='fa fa-database' aria-hidden='true'></i></div>
        <div class='form-group-field'><a href='#' class='form-select'>
          <span class='text'>" . array_values($currencyList)[0] . "</span>
          <span class='icon'><i class='fa fa-chevron-down' aria-hidden='true'></i></span>
        </a>{input}</div>{hint}\n{error}",
        ])->dropDownList($currencyList, [
            'placeholder' => Yii::_t('users.signup.currency'),
            'class' => 'form-control',
        ])->label(false) ?>
    <?php endif ?>
    <?= Html::submitButton('<i class="fa fa-check-circle" aria-hidden="true"></i> ' .
        Yii::_t('users.signup.register'), ['class' => 'btn btn-green btn-reg']) ?>
    <ul class="form-modal-link">
        <li><?= Yii::_t('users.signup.have_an_account') ?></li>
        <li><a href="#" class="js-open-login"><?= Yii::_t('users.login.sign_in') ?></a></li>
    </ul>

<?php endif; ?>
<?php ActiveForm::end() ?>
