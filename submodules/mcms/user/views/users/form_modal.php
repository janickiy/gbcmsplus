<?php
/**
 * @var \mcms\common\web\View $this
 * @var \mcms\user\models\UserForm $model
 * @var $promoRebillCorrect
 * @var \mcms\statistic\components\api\LabelStatisticEnable $labelStatisticEnableApi
 * @var UserPromoSettings $userPromoSettingsApi
 */

use mcms\common\form\AjaxActiveForm;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\api\UserPromoSettings;
use mcms\user\assets\UserFormAsset;
use mcms\user\models\search\User;
use mcms\user\Module;
use yii\bootstrap\Html;
use mcms\common\widget\Select2;

UserFormAsset::register($this);
$form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#usersPjaxGrid'),
]);
?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?= $form->field($model, 'email') ?>

    <div class="row">
      <div class="col-xs-12 col-sm-6">

        <!-- disables autocomplete -->
        <?= Html::activeTextInput($model, 'password', ['autocomplete' => 'off', 'style' => 'display:none;', 'id' => 'userform-password-hidden']) ?>

        <label class="control-label" for="userform-password"><?= $model->getAttributeLabel('password') ?></label>
        <?= $form->field($model, 'password', [
          'template' => '<div class="form-group input-group">{input}<span class="input-group-btn">' .
            Html::button(Html::icon('refresh', ['prefix' => 'fa fa-']), ['id' => 'pass-generate', 'class' => 'btn btn-info', 'title' => Yii::_t('forms.password_generate')]) .
            Html::button(Html::icon('eye', ['prefix' => 'fa fa-']), ['id' => 'pass-show', 'class' => 'btn btn-success', 'title' => Yii::_t('forms.password_show')]) .
        '</span></div>{error}'
        ])->passwordInput(['autocomplete' => 'off'])->label(false); ?>

        <?= $form->field($model, 'phone') ?>

        <?php $this->beginBlockAccessVerifier('roles', ['UsersUsersUpdateUserRoles']) ?>
        <?= $form->field($model, 'roles')->widget(Select2::class, [
          'data' => $model::getRolesList(),
          'options' => [
            'multiple' => true,
          ]
        ]); ?>
        <?php $this->endBlockAccessVerifier() ?>

      </div>
      <div class="col-xs-12 col-sm-6">
        <!-- disables autocomplete -->
        <?= $form->field($model, 'topname')->textInput(['autocomplete' => 'off']) ?>

        <?= $form->field($model, 'language')->widget(Select2::class, [
          'data' => ['ru' => Yii::_t('forms.russian'), 'en' => Yii::_t('forms.english')],
        ]); ?>

        <?= $form->field($model, 'status')->widget(Select2::class, [
          'data' => User::filterStatuses(),
        ]); ?>

        <?php if (!$model->user->isNewRecord): ?>
          <div id="status-reason" class="hide" data-active-status="<?= User::STATUS_ACTIVE; ?>"
               data-current-status="<?= $model->user->status; ?>">
            <?= $form->field($model, 'moderationReason')->textarea()
              ->label($model->getAttributeLabel('moderationReason') . ' (' . Yii::_t("commonMsg.lang.{$model->user->language}") . ')'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($model->canResellerHidePromo()): ?>
      <?= $form->field($model, 'partner_hide_promo')->checkbox() ?>
    <?php endif; ?>

    <?php if ($labelStatisticEnableApi->getIsEnabledGlobally()): ?>
      <?= $form->field($model, 'is_label_stat_enabled')->checkbox() ?>
    <?php endif; ?>
    <?php if ($model->canAddReferrer()): ?>
      <?= $form->field($model, 'referrer_id')->widget(UserSelect2::class, [
        'initValueUserId' => $model->referrer_id,
        'ignoreIds' => [$model->user->id],
        'roles' => [Module::PARTNER_ROLE],
        'options' => [
          'placeholder' => Yii::_t('users.forms.enter_login_or_email') . ':'
        ]
      ]); ?>
    <?php endif; ?>

    <?= $form->field($model, 'comment')->textarea(); ?>
  </div>

  <div class="modal-footer">
    <div class="row">
      <div class="col-md-12">
        <?= Html::submitButton(
          '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Save'),
          ['class' => 'btn btn-primary']
        ) ?>
      </div>
    </div>
  </div>
<?php $form->end() ?>