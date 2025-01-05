<?php
use himiklab\yii2\recaptcha\ReCaptcha;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

/* @var $model \mcms\user\models\LoginForm */
?>

<?php $form = ActiveForm::begin([
  'id' => 'login-form',
  'action' => Url::to(['users/api/login']),
  'options' => ['autocomplete' => 'off'],
]); ?>

<div class="row">
  <div class="col-12">
    <?= $form->field($model, 'username', ['options' => ['class' => 'form-group input-email']])
      ->textInput() ?>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <?= $form->field($model, 'password', ['options' => ['class' => 'form-group input-password']])
      ->passwordInput() ?>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <?php if ($model->shouldUseCaptcha()) : ?>
      <?= $form->field($model, 'captcha', ['options' => ['class' => false]])->widget(ReCaptcha::class)->label(false) ?>
    <?php else: ?>
      <span id="recapcha-<?= $form->id ?>" class="checkbox" data-site-key="<?= Yii::$app->reCaptcha->siteKey ?>"></span>
      <?= Html::activeHiddenInput($model, 'captcha') ?>
    <?php endif; ?>
  </div>
</div>
<div class="align-center">
  <?= Html::submitButton(Yii::_t('users.login.sign_in'), ['class' => 'btn-generic btn-reg']); ?>
</div>
<p><a class="btn-recovery" href="#"><?= Yii::_t('users.login.forgot_your_password') ?></a></p>
<?php ActiveForm::end() ?>
