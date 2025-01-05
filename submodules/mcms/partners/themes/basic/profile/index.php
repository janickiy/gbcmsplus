<?php
use mcms\user\models\UserContact;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mcms\partners\assets\basic\ProfileAsset;
use mcms\partners\components\widgets\FileApiWidget;

ProfileAsset::register($this);

/**
 * @var mcms\user\models\User $user
 * @var \mcms\partners\models\ProfileForm $model
 * @var array $languagesArray
 */


$this->registerCss("
#contacts-wrapper .close {
    text-decoration: none;
    position: absolute;
    right: 0;
}

@media (max-width: 786px) {
  .profile__lang.profile__contacts .form-group .bootstrap-select:not(.select-colors) .btn span.filter-option:before {
      content: \"\\e956\";
  }
  
  .profile__contacts .row {
    position:relative;
  }
  
  #contacts-wrapper .close {
    right: 30px;
    bottom: 10px;
    font-size: 22px;
  }
  
  .profile__contacts .col-xs-6:not(:first-child) .form-group {
    border-top: none;
  }
  
  #add-contact-wrapper {
    padding: 10px;
    display: block;
    border-top: 5px solid #F2F2F0;
  }
  
  .profile__contacts .form-group.has-error {
    background: #f7dedd;
  }
}
");

$number = 0;
?>
<?php /** @var \mcms\partners\Module $partnersModule */?>
<?php $partnersModule = Yii::$app->getModule('partners'); ?>
<?php /** @var \mcms\user\Module $usersModule */?>
<?php $usersModule = Yii::$app->getModule('users'); ?>
<?php $form = ActiveForm::begin(['id' => 'profile-form']); ?>

<div class="container-fluid">
  <div class="bgf profile user__info">
    <div class="title">
      <h2><?= Yii::_t('main.profile') ?> <span class="pull-right">ID <?= $user->id ?></span></h2>
    </div>
    <div class="content__position">
      <div class="row">
        <div class="col-xs-6">

            <?= $form->field($model, 'email', [
              'options' => [
                'class' => 'form-group input_readonly'
              ],
              'template' => "{label}\n{input}\n<span data-toggle='tooltip' data-placement='top' title='" .
                Yii::_t('profile.contact_support_for_change') . "'><i class='icon-lock'></i></span>{hint}\n{error}"
            ])->textInput(['readonly' => true]) ?>

            <div class="row">
              <div class="col-xs-6">
                <?= $form->field($model, 'oldPassword')->passwordInput(['placeholder' => Yii::_t('profile.password')]) ?>
              </div>
              <div class="col-xs-6">
                <?= $form->field($model, 'newPassword')->passwordInput(['placeholder' => Yii::_t('profile.password')]) ?>
              </div>
            </div>
        </div>

        <div class="col-xs-6 mw600">
            <div class="row">
                <div class="col-xs-6">
                  <?= $form->field($model, 'topname')->textInput(); ?>
                </div>
              <?php if ($usersModule->canPartnerChangeLanguage()) { ?>
                <div class="col-xs-6">
                  <?= $form->field($model, 'language')->dropDownList($languagesArray, ['class' => 'selectpicker', 'data-width' => '100%']); ?>
                </div>
              <?php } ?>
            </div>


          <div class="profile__lang">
            <div class="row">
              <?php if ($partnersModule->isThemeEnabled()) { ?>
                <div class="col-xs-6">
                  <div class="form-group">
                    <?= $form->field($model, 'color')->dropDownList(
                      [
                        'cerulean' => '',
                        'blue' => '',
                        'amethyst' => '',
                        'alizarin' => '',
                        'orange' => '',
                        'green' => '',
                        'grey' => '',
                      ],
                      [
                        'class' => 'selectpicker select-colors bs-select-hidden',
                        'id' => 'change_theme',
                        'encode' => false,
                        'data-width' => '100%',
                        'options' => [
                          'cerulean' => ['data-content' => "<i class='color color_1'>" . Yii::_t('profile.cerulean') . "</i>"],
                          'blue' => ['data-content' => "<i class='color color_2'>" . Yii::_t('profile.blue') . "</i>"],
                          'amethyst' => ['data-content' => "<i class='color color_3'>" . Yii::_t('profile.amethyst') . "</i>"],
                          'alizarin' => ['data-content' => "<i class='color color_4'>" . Yii::_t('profile.alizarin') . "</i>"],
                          'orange' => ['data-content' => "<i class='color color_5'>" . Yii::_t('profile.orange') . "</i>"],
                          'green' => ['data-content' => "<i class='color color_6'>" . Yii::_t('profile.green') . "</i>"],
                          'grey' => ['data-content' => "<i class='color color_7'>" . Yii::_t('profile.grey') . "</i>"],
                        ]
                      ]); ?>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 profile__lang profile__contacts" id="contacts-wrapper">
          <?php foreach ($model->getContactModels() as $contact): ?>
            <div class="row" data-number="<?= $number ?>" data-id="<?= $contact->id ?>">
              <div class="col-xs-6">
                <?php
                $fieldName = $contact->getFormFieldName('type');
                $hasErrorClass = $contact->hasErrors('type') ? ' has-error' : null;

                echo $form->field($model, $fieldName, ['options' => [
                  'class' => 'form-group' . $hasErrorClass
                ]])->begin();
                echo Html::activeLabel($contact, 'type', ['class' => 'control-label']);
                echo Html::activeDropDownList($model, $fieldName, $contact::getTypes(true), [
                  'class' => 'selectpicker',
                  'encode' => false,
                  'data-width' => '100%',
                  'value' => $contact->type,
                  'prompt' => '',
                ]);
                echo Html::error($contact,'type', ['class' => 'help-block']);
                echo $form->field($model, $fieldName)->end();
                ?>
              </div>
              <div class="col-xs-6">
                <?php
                $fieldName = $contact->getFormFieldName('data');
                $hasErrorClass = $contact->hasErrors('data') ? ' has-error' : null;

                echo $form->field($model, $fieldName, ['options' => [
                  'class' => 'form-group' . $hasErrorClass
                ]])->begin();
                echo Html::activeLabel($contact, 'data', ['class' => 'control-label']);
                echo Html::activeTextInput($model, $fieldName, ['value' =>  $contact->data, 'class' => 'form-control']);
                echo Html::error($contact,'data', ['class' => 'help-block']);
                echo $form->field($model, $fieldName)->end();
                ?>
              </div>
              <?php if ($number > 0): ?>
                <a href class="close">×</a>
              <?php else: ?>
                <div></div>
              <?php endif ?>
            </div>
            <?php $number++ ?>
          <?php endforeach; ?>
          <div id="add-contact-wrapper">
            <a href="#" id="add-contact">Добавить контакт</a>
          </div>
        </div>
      </div>
    </div>
    <div class="content__position profile_button text-right">
      <div class="form-buttons">
        <div class="form-group">
          <input type="submit" value="<?= Yii::_t('app.common.Save') ?>" class="btn btn-success"/>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="hidden" id="contact-form-template">
  <?php $contact = new UserContact() ?>
  <div class="row" data-number="0">
    <div class="col-xs-6">
      <?php
      $fieldName = $contact->getFormFieldName('type');
      $hasErrorClass = $contact->hasErrors('type') ? ' has-error' : null;

      echo $form->field($model, $fieldName, ['options' => [
        'class' => 'form-group' . $hasErrorClass
      ]])->begin();
      echo Html::activeLabel($contact, 'type', ['class' => 'control-label']);
      echo Html::activeDropDownList($model, $fieldName, $contact::getTypes(true), [
        'class' => 'selectpicker2',
        'encode' => false,
        'data-width' => '100%',
        'value' => $contact->type,
        'name' => '',
        'data-name' => Html::getInputName($model, $fieldName),
        'prompt' => '',
      ]);
      echo Html::error($contact, 'type', ['class' => 'help-block']);
      echo $form->field($model, $fieldName)->end();
      ?>
    </div>
    <div class="col-xs-6">
      <?php
      $fieldName = $contact->getFormFieldName('data');
      $hasErrorClass = $contact->hasErrors('data') ? ' has-error' : null;

      echo $form->field($model, $fieldName, ['options' => [
        'class' => 'form-group' . $hasErrorClass,
      ]])->begin();
      echo Html::activeLabel($contact, 'data', ['class' => 'control-label']);
      echo Html::activeTextInput($model, $fieldName, [
        'value' => $contact->data,
        'class' => 'form-control',
        'name' => '',
        'data-name' => Html::getInputName($model, $fieldName),
      ]);
      echo Html::error($contact, 'data', ['class' => 'help-block']);
      echo $form->field($model, $fieldName)->end();
      ?>
    </div>
    <a href class="close">×</a>
  </div>
</div>
<?php ActiveForm::end(); ?>