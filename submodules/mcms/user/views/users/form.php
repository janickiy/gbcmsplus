<?php

use mcms\common\form\ActiveKartikForm;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\api\UserPromoSettings;
use mcms\promo\components\widgets\TrafficFiltersOffWidget;
use mcms\user\assets\UserFormAsset;
use mcms\user\models\search\User;
use mcms\common\helpers\Link;
use mcms\user\Module;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\grid\GridView;
use yii\widgets\Pjax;


/** @var \mcms\common\web\View $this */
/** @var \mcms\user\models\UserForm $model */
/** @var $promoRebillCorrect */
/** @var \mcms\statistic\components\api\LabelStatisticEnable $labelStatisticEnableApi */
/** @var UserPromoSettings $userPromoSettingsApi */
/** @var array $notificationModules */
/** @var \mcms\promo\models\UserFakeSetting $fakeSettingsWidget */
/** @var string $userContacts */

/** @var \mcms\promo\Module $promoModule */
$promoModule = Yii::$app->getModule('promo');
UserFormAsset::register($this);

$this->registerJs(<<<JS
var hash = location.hash;
if (hash) {
  $(hash).collapse("show");
  $("html, body").animate({ scrollTop: $(hash).offset().top }, 1000);
}
JS
);
?>

<?php $this->beginBlock('actions') ?>
<?php if (!$model->user->isNewRecord): ?>
  <?= $model->user->id ? Html::a(Yii::_t('login_logs.log'), '#', ['data-toggle' => 'modal', 'data-target' => '#login_log', 'class' => 'btn btn-success btn-xs']) : '' ?>
  <?= $model->user->id ? Link::get('/users/users/login-by-user/', ['id' => $model->user->id], ['class' => 'btn btn-success btn-xs'], Yii::_t('login.login_by_user')) : '' ?>
<?php endif ?>
<?php $this->endBlock() ?>

<?php $this->beginBlock('subHeader'); // переопределяем заголовок 2го уровня (по дефолту равен активному пункту меню)?>
<?php $this->endBlock(); ?>

<div class="row">
  <?php $this->beginBlockAccessVerifier('userEditForm', [\mcms\user\Module::PERMISSION_CAN_VIEW_EDIT_FORM]); ?>

  <div class="col-xs-12">
    <div class="panel panel-default">
      <div>
        <div class="panel-heading" role="tab" id="headingFour">
          <h4 class="panel-title">
            <?= Html::icon('user'); ?> <?= Yii::_t('users.forms.user_info') ?>
          </h4>
        </div>
        <div class="panel-body">

          <?php $form = ActiveKartikForm::begin([
            'id' => 'create-user-form',
            'options' => ['class' => 'col-xs-12', 'autocomplete' => 'off'],
          ]); ?>

          <div class="row">
            <div class="col-md-6">
              <?= $form->field($model, 'email') ?>

              <div class="row">
                <div class="form-group col-md-6">
                  <?= Html::activeTextInput($model, 'password', ['autocomplete' => 'off', 'style' => 'display:none;', 'id' => 'userform-password-hidden']) ?>
                  <?= $form->field($model, 'password', [
                    'template' => '<label class="control-label" for="userform-password">' . $model->getAttributeLabel('password') . '</label>
                  <div class="input-group">{input}<span class="input-group-btn">' .
                      Html::button(Html::icon('refresh', ['prefix' => 'fa fa-']), ['id' => 'pass-generate', 'class' => 'btn btn-info', 'title' => Yii::_t('forms.password_generate')]) .
                      Html::button(Html::icon('eye', ['prefix' => 'fa fa-']), ['id' => 'pass-show', 'class' => 'btn btn-success', 'title' => Yii::_t('forms.password_show')]) .
                      '</span></div>{error}',
                  ])->passwordInput(['autocomplete' => 'off'])->label(false); ?>
                </div>

                <?= $form->field($model, 'topname', ['inputOptions' => ['autocomplete' => 'off'], 'options' => ['class' => 'form-group col-md-6']]) ?>
              </div>

              <!-- disables autocomplete -->


              <div class="row">

                <?php $this->beginBlockAccessVerifier('roles', ['UsersUsersUpdateUserRoles']) ?>
                <?= $form->field($model, 'roles', ['options' => ['class' => 'form-group col-md-6']])->dropDownList($model::getRolesList()) ?>
                <?php $this->endBlockAccessVerifier() ?>

                <?= $form->field($model, 'status', ['options' => ['class' => 'form-group col-md-6']])->dropDownList(User::filterStatuses()); ?>
              </div>

              <?php if (!$model->user->isNewRecord): ?>
                <div id="status-reason" class="hide" data-active-status="<?= User::STATUS_ACTIVE; ?>"
                     data-current-status="<?= $model->user->status; ?>">
                  <?= $form->field($model, 'moderationReason')->textarea()
                    ->label($model->getAttributeLabel('moderationReason') . ' (' . Yii::_t("commonMsg.lang.{$model->user->language}") . ')'); ?>
                </div>
              <?php endif; ?>





              <div class="row">
                <?= $form->field($model, 'language', ['options' => ['class' => 'form-group col-md-6']])
                  ->dropDownList(['ru' => Yii::_t('forms.russian'), 'en' => Yii::_t('forms.english')]); ?>
                <?= $form->field($model, 'phone', ['options' => ['class' => 'form-group col-md-6']]) ?>
                <?php if (!$userContacts): ?>
                  <?= $form->field($model, 'skype', ['options' => ['class' => 'form-group col-md-6']]) ?>
                <?php endif ?>
              </div>

              <?= $form->field($model, 'comment')->textarea(); ?>


            </div>
            <div class="col-md-6">

              <?php if ($model->user->hasRole('partner')) : ?>
                <?php if (Yii::$app->user->can(Module::PERMISSION_CAN_CHANGE_MANAGER_ALL_USERS)) : ?>
                  <?= $form->field($model, 'manager_id')->widget(UserSelect2::class, [
                    'initValueUserId' => $model->manager_id,
                    'ignoreIds' => [$model->user->id],
                    'roles' => Yii::$app->getModule('users')->getManagerRoles(),
                    'options' => [
                      'placeholder' => Yii::_t('users.forms.enter_login_or_email') . ':',
                    ]
                  ]); ?>
                <?php elseif (Yii::$app->user->can(Module::PERMISSION_CAN_CHANGE_MANAGER_TO_OWNSELF_USERS_WITHOUT_MANAGER, ['user' => $model->user])) : ?>
                  <?= $form->field($model, 'manager_id')->checkbox(['label' => Yii::_t('users.forms.set_manager_oneself'),
                    'value' => Yii::$app->user->id]) ?>
                <?php endif ?>
              <?php endif ?>

              <?= $form->field($model, 'show_promo_modal')->checkbox() ?>

              <?php if ($model->canResellerHidePromo()): ?>
                <?= $form->field($model, 'partner_hide_promo')->checkbox() ?>
              <?php endif; ?>

              <?php if ($labelStatisticEnableApi->getIsEnabledGlobally()): ?>
                <?= $form->field($model, 'is_label_stat_enabled')->checkbox() ?>
              <?php endif; ?>

              <?php if ($isPartner): ?>
                <?= $form->field($model, 'is_allowed_source_redirect')->checkbox() ?>
                <?= $form->field($model, 'is_disable_buyout')->checkbox() ?>
              <?php endif; ?>

              <?php if ($userPromoSettingsApi->getIsUserCanEditFakeFlag()): ?>
                <?= $form->field($model, 'is_fake_revshare_enabled')->checkbox([
                  'label' => Html::tag('span', $model->getAttributeLabel('is_fake_revshare_enabled')) .
                    (
                    $promoModule->settingsIsFakeGloballyEnabled()
                      ? Html::tag(
                      'p',
                      Yii::_t('forms.is_fake_revshare_enabled_globally'),
                      ['class' => 'note']
                    )
                      : ''
                    )
                  ,
                ]) ?>
              <?php endif; ?>

              <?php if ($isPartner): ?>
                <?= $form->field($model, 'postback_url')->textInput() ?>
                <?= $form->field($model, 'complains_postback_url')->textInput() ?>
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


              <div class="panel-group form-group smart-accordion-default" id="accordion-notifications" role="tablist"
                   aria-multiselectable="true">
                <div class="panel panel-default">
                  <div class="panel-heading" role="tab" id="notificationsHeadingFour">
                    <h4 class="panel-title">
                      <a role="button" data-toggle="collapse" data-parent="#accordion-notifications"
                         href="#collapseNotifications"
                         aria-expanded="false" class="collapsed">
                        <i class="fa fa-lg fa-angle-down pull-right"></i>
                        <i class="fa fa-lg fa-angle-up pull-right"></i>
                        <i class="glyphicon glyphicon-bullhorn"></i> <?= Yii::_t('forms.notification_settings') ?>
                      </a>
                    </h4>
                  </div>
                  <div id="collapseNotifications" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-6">
                          <h4><?= Yii::_t('forms.notification_type-browser') ?></h4>
                          <hr>
                          <div class="col-md-12">
                            <?php /*echo $form->field($model, 'notify_browser_system')->checkbox()->hint(false)*/ ?>
                            <?= $form->field($model, 'notify_browser_news')->checkbox() ?>
                            <?= $form->field($model, 'notify_browser_categories')
                              ->dropDownList($notificationModules, ['multiple' => true, 'size' => count($notificationModules)]); ?>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <h4><?= Yii::_t('forms.notification_type-email') ?></h4>
                          <hr>
                          <div class="col-md-12">
                            <?php /*echo  $form->field($model, 'notify_email_system')->checkbox()*/ ?>
                            <?= $form->field($model, 'notify_email_news')->checkbox() ?>
                            <?= $form->field($model, 'notify_email_categories')
                              ->dropDownList($notificationModules, ['multiple' => true, 'size' => count($notificationModules)]); ?>
                            <?= $form->field($model, 'notify_email'); ?>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <h4><?= Yii::_t('forms.notification_type-telegram') ?></h4>
                          <hr>
                          <div class="col-md-12">
                            <?php /*TRICKY: закомментировано в задаче MCMS-1467
                              echo  $form->field($model, 'notify_telegram_system')->checkbox()->hint(false)*/ ?>
                            <?= $form->field($model, 'notify_telegram_news')->checkbox() ?>
                            <?= $form->field($model, 'notify_telegram_categories')
                              ->dropDownList($notificationModules, ['multiple' => true, 'size' => count($notificationModules)]); ?>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <h4><?= Yii::_t('forms.notification_type-push') ?></h4>
                          <hr>
                          <div class="col-md-12">
                            <?php /*TRICKY: закомментировано в задаче MCMS-1467
                              echo  $form->field($model, 'notify_push_system')->checkbox()*/ ?>
                            <?= $form->field($model, 'notify_push_news')->checkbox() ?>
                            <?= $form->field($model, 'notify_push_categories')
                              ->dropDownList($notificationModules, ['multiple' => true, 'size' => count($notificationModules)]); ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>


          <hr>
          <div class="form-group clearfix">
            <input type="submit" value="<?= Yii::_t('app.common.Save') ?>" class="btn btn-primary pull-right"/>
          </div>

          <?php ActiveKartikForm::end(); ?>

          <?= $this->render('_login_log', ['model' => $model])?>

        </div>
      </div>
    </div>
  </div>

  <?php $this->endBlockAccessVerifier(); ?>

</div>

<div class="row margin-bottom-10">
  <div class="col-xs-12">
    <div class="panel-group smart-accordion-default" id="accordion" role="tablist" aria-multiselectable="true">
      <?php if ($userContacts): ?>
        <?php $this->beginBlockAccessVerifier('UsersUserContactsIndex', [
          mcms\promo\Module::PERMISSION_CAN_VIEW_PERSONAL_PROFITS_WIDGET,
        ]); ?>

        <div class="panel panel-default">
          <div class="panel-heading" role="tab" id="headingUserContacts">
            <h4 class="panel-title">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseUserContacts"
                 aria-expanded="false" aria-controls="collapseUserContacts" class="collapsed">
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
                <?= Html::icon('list-alt'); ?> <?= Yii::_t('users.forms.user_contacts_title') ?>
              </a>
            </h4>
          </div>
          <div id="collapseUserContacts" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingUserContacts">
            <div class="panel-body">
              <?= $userContacts ?>
            </div>
          </div>
        </div>
        <?php $this->endBlockAccessVerifier(); ?>
      <?php endif ?>

      <?php if ($promoPersonalProfit): ?>

        <?php $this->beginBlockAccessVerifier('personalProfitWidgetInUserForm', [
          mcms\promo\Module::PERMISSION_CAN_VIEW_PERSONAL_PROFITS_WIDGET,
//        mcms\promo\Module::PERMISSION_CAN_VIEW_OWN_PERSONAL_PROFITS_WIDGET => $model->user->id, // Не работает иначе показ виджета вообще
        ]); ?>

        <div class="panel panel-default">
          <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne"
                 aria-expanded="false" aria-controls="collapseOne" class="collapsed">
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
                <?= Html::icon('usd'); ?> <?= Yii::_t('users.forms.personal_profits_title') ?>
              </a>
            </h4>
          </div>
          <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
              <?= $promoPersonalProfit ?>
            </div>
          </div>
        </div>
        <?php $this->endBlockAccessVerifier(); ?>

      <?php endif; ?>

      <?php if ($promoTrafficBlock): ?>

        <div class="panel panel-default">
          <div class="panel-heading" role="tab" id="headingTB">
            <h4 class="panel-title">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTB"
                 aria-expanded="false" aria-controls="collapseTB" class="collapsed">
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
                <?= Html::icon('globe'); ?> <?= Yii::_t('users.forms.traffic_block') ?>
              </a>
            </h4>
          </div>
          <div id="collapseTB" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTB">
            <div class="panel-body">
              <?= $promoTrafficBlock ?>
            </div>
          </div>
        </div>

      <?php endif; ?>

      <?php if (TrafficFiltersOffWidget::canView($model->getUser()->id)): ?>
        <div class="panel panel-default">
          <div class="panel-heading" role="tab" id="headingTFO">
            <h4 class="panel-title">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTFO"
                 aria-expanded="false" aria-controls="collapseTFO" class="collapsed">
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
                <?= Html::icon('filter'); ?> <?= Yii::_t('users.forms.traffic_filters_off') ?>
              </a>
            </h4>
          </div>
          <div id="collapseTFO" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTFO">
            <div class="panel-body">
              <?= TrafficFiltersOffWidget::widget([
                'userId' => $model->getUser()->id
              ]) ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($paymentSettings): ?>
        <div class="panel panel-default">
          <div class="panel-heading" role="tab" id="headingTwo">
            <h4 class="panel-title">
              <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"
                 aria-expanded="false" aria-controls="collapseTwo" class="collapsed">
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
                <?= Html::icon('rub'); ?> <?= Yii::_t('users.forms.payment_settings') ?>
              </a>
            </h4>
          </div>
          <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="panel-body">
              <?= $paymentSettings ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($isPartner && $fakeSettingsWidget): ?>
        <div class="panel panel-default">
          <div class="panel-heading" role="tab" id="headingSix">
            <h4 class="panel-title">
              <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion"
                 href="#collapseSix"
                 aria-expanded="false" aria-controls="collapseSix" class="collapsed">
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
                <?= Html::icon('warning-sign'); ?> <?= Yii::_t('promo.settings.fake_provider_tab') ?>
              </a>
            </h4>
          </div>
          <div id="collapseSix" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingSix">
            <div class="panel-body">
              <?= $fakeSettingsWidget ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($promoRebillCorrect): ?>
        <div class="panel panel-default">
          <div class="panel-heading" role="tab" id="headingFive">
            <h4 class="panel-title">
              <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFive"
                 aria-expanded="false" aria-controls="collapseFive" class="collapsed">
                <i class="fa fa-lg fa-angle-down pull-right"></i>
                <i class="fa fa-lg fa-angle-up pull-right"></i>
                <?= Html::icon('wrench'); ?> <?= Yii::_t('users.forms.rebill_correct_title') ?>
              </a>
            </h4>
          </div>
          <div id="collapseFive" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingFive">
            <div class="panel-body">
              <?= $promoRebillCorrect ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>