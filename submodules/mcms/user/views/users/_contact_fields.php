<?php

/**
 * @var mcms\user\models\User $user
 * @var \yii\web\View $this
 */

use mcms\user\models\UserContact;
use rgk\utils\widgets\AjaxRequest;
use yii\bootstrap\Html;

// Добавление валидации к добавленным полям
$js = <<<JS
function() {
  var key = $('.contact-type').length - 1;
  
   $('#profile-form').yiiActiveForm('add', {
     id: 'usercontact-' + key + '-data',
     name: '[' + key + '][data]',
     container: '.field-usercontact-' + key + '-data',
     input: '#usercontact-' + key + '-data',
     error: '.help-block',
     enableAjaxValidation: true
   });
   
   $('#profile-form').yiiActiveForm('add', {
     id: 'usercontact-' + key + '-type',
     name: '[' + key + '][type]',
     container: '.field-usercontact-' + key + '-type',
     input: '#usercontact-' + key + '-type',
     error: '.help-block',
     enableAjaxValidation: true
   });
}
JS;
?>

<h4 class="form-group"><?= Yii::_t('users.forms.user_contacts_title') ?></h4>

<div class="well">
  <?php foreach ($user->activeContacts as $key => $contact): ?>
    <?php /** @var UserContact $contact */ ?>
    <div class="row contacts-row">
      <?= $form->field($contact, "[{$key}]type", ['options' => ['class' => 'contact-type form-group col-md-5']])
        ->dropDownList(UserContact::getTypes(true), ['prompt' => Yii::_t('app.common.choose')])
        ->label(false)
      ?>
      <?= $form->field($contact, "[{$key}]data", ['options' => ['class' => 'form-group col-md-6']])->label(false) ?>
      <div class="col-md-1">
        <?= AjaxRequest::widget([
          'title' => Html::icon('trash'),
          'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
          'url' => ['users/remove-contact', 'id' => $contact->id],
          'pjaxReloadUrl' => ['users/profile/'],
          'pjaxId' => '#contactsPjax',
          'pjaxPush' => false,
          'successCallback' => $js,
        ]) ?>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="row">
    <div class="col-md-11">
      <?= AjaxRequest::widget([
        'title' => Yii::_t('users.forms.user_contacts_create'),
        'url' => ['users/create-contact'],
        'pjaxReloadUrl' => ['users/profile/'],
        'pjaxId' => '#contactsPjax',
        'buttonClass' => 'btn btn-primary pull-right ' . AjaxRequest::BUTTON_CLASS,
        'pjaxPush' => false,
        'successCallback' => $js,
      ]) ?>
    </div>
    <div class="col-md-1">
    </div>
  </div>
</div>