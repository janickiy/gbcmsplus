<?php
use mcms\common\form\AjaxActiveForm;
use mcms\user\assets\ProfileAsset;
use mcms\payments\models\ProfileForm;
use yii\bootstrap\Html;
use mcms\common\widget\modal\Modal;
use yii\widgets\Pjax;

/**
 * @var mcms\user\models\User $user
 * @var mcms\user\models\ProfileForm $model
 * @var array $languagesArray
 * @var $promoPersonalProfit
 * @var \yii\web\View $this
 */
ProfileAsset::register($this);
$this->title = Yii::_t('profile.profile');
?>
<?php $form = AjaxActiveForm::begin([
  'id' => 'profile-form',
  'action' => Yii::$app->getModule('users')->api('userLink')->buildProfileEditLink(),
  'ajaxSuccess' => Modal::ajaxSuccess('#profile-summary'),
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">
      <?= $this->title ?>
      <span class="pull-right"><?= $user->email ?> (ID <?= $user->id ?>)</span>
    </h4>
  </div>
  <div class="modal-body">
    <?php $crop = true; ?>
    <?php if ($crop) : ?>
      <div id="modal-crop" class="collapse form-group">
        <div id="modal-preview" class="form-group"></div>
        <div>
          <button type="button" class="btn btn-primary crop"><?= Yii::_t('profile.upload') ?></button>
        </div>
      </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <!-- Данные пользователя -->
          <?= Html::label($model->getAttributeLabel('email'), '#profileform-email'); ?>

          <?php if (Yii::$app->getModule('users')->canChangeEmail()): ?>
            <?= $form->field($model, 'email')->label(false); ?>
          <?php else: ?>
            <?= $form->field($model, 'email', [
              'options' => [
                'class' => 'input-group form-group profile-user__email'
              ],
              'template' => "{input}\n<span id='lock_span_icon' class='input-group-addon' data-toggle='tooltip' data-placement='top' title='" .
                Yii::_t('profile.contact_support_for_change') . "'><i class='glyphicon glyphicon-lock'></i></span>{hint}\n{error}"
            ])->textInput(['disabled' => true]) ?>
          <?php endif ?>
        </div>
        <div class="col-md-6">
          <?= $form->field($model, 'topname'); ?>
        </div>
    </div>
    <?= $form->field($model, 'status')->textInput(['value' => $user->getNamedStatus(), 'disabled' => true]); ?>
    <div class="row">
      <div class="col-xs-6">
        <?= $form->field($model, 'oldPassword')->passwordInput(['placeholder' => Yii::_t('profile.password')]) ?>
      </div>
      <div class="col-xs-6">
        <?= $form->field($model, 'newPassword')->passwordInput(['placeholder' => Yii::_t('profile.password')]) ?>
      </div>
    </div>

    <div class="row">
      <?= $form->field($model, 'language', ['options' => ['class' => 'form-group col-md-4']])->dropDownList($languagesArray); ?>
      <?= $form->field($model, 'phone', ['options' => ['class' => 'form-group col-md-4']]) ?>
      <?= $form->field($model, 'skype', ['options' => ['class' => 'form-group col-md-4']])
            ->textInput(['disabled' => true])
      ?>
      <?= $form->field($model, 'grid_page_size', ['options' => ['class' => 'form-group col-xs-12']]) ?>
    </div>
    <?php Pjax::begin(['id' => 'contactsPjax', 'enablePushState' => false]); ?>
      <?= $this->render('_contact_fields', ['user' => $user, 'form' => $form]) ?>
    <?php Pjax::end(); ?>
</div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">
      <i class="fa fa-save"></i>
      <?= Yii::_t('app.common.Save') ?>
    </button>
  </div>
<?php AjaxActiveForm::end(); ?>