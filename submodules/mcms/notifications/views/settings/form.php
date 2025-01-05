<?php

use kartik\form\ActiveForm;
use mcms\common\grid\ContentViewPanel;
use yii\helpers\Html;
use mcms\common\multilang\widgets\multilangform\MultiLangForm;
use mcms\notifications\models;
use mcms\notifications\models\Notification;
use mcms\notifications\components\assets\AdminAsset;

AdminAsset::register($this);

/** @var \yii\web\View $this */
/** @var \yii\data\ArrayDataProvider $replacementsDataProvider */

?>

<?php ContentViewPanel::begin([
]) ?>
<?php
$form = ActiveForm::begin(['id' => 'module-event', 'enableAjaxValidation' => true]); ?>

<?php if (in_array($model->scenario, [Notification::SCENARIO_EDIT, Notification::SCENARIO_ADMIN_EDIT])): ?>
  <?= MultiLangForm::widget([
    'model' => $model,
    'form' => $form,
    'attributes' => $model->getFormAttributes()
  ]); ?>
<?php endif ?>

<?= $this->render('../partial/replacements_modal', [
  'replacementsDataProvider' => $replacementsDataProvider
]); ?>

<?= $form->field($model, 'event')->dropDownList($events, ['prompt' => Yii::_t('app.common.choose')]) ?>

<?= Yii::$app->user->can('NotificationsSettingsEdit') ? $form->field($model, 'roles')->checkboxList($model->getAllRoles(), [
  'id' => 'roles_checkbox_list',
  'item' => function ($index, $label, $name, $checked, $value) {
    return Html::tag('div', Html::checkbox($name, $checked, ['label' => Html::tag('span', $label), 'value' => $value, 'class' => 'checkbox']));
  },
]) : '' ?>

<?= $form->field($model, 'emails')->textarea(['rows' => 5])->hint(Yii::_t('labels.emails_hint')) ?>

<?= $form->field($model, 'emails_language')->dropDownList(\mcms\common\SystemLanguage::getLanguangesDropDownArray()); ?>

<?= $form->field($model, 'notification_type')->dropDownList($model->notificationTypes) ?>

<?= $form->field($model, 'is_important')->checkbox() ?>

<?= $form->field($model, 'is_news')->checkbox() ?>

<?= $form->field($model, 'is_disabled')->checkbox() ?>

<?= $form->field($model, 'is_system')->checkbox() ?>

<?= Html::hiddenInput('use_owner', $model->use_owner); ?>

<hr>
<div class="form-group clearfix">
  <input type="submit" value="<?= Yii::_t('app.common.Save') ?>" class="btn btn-primary pull-right"/>
</div>

<?php ActiveForm::end(); ?>

<?php ContentViewPanel::end() ?>