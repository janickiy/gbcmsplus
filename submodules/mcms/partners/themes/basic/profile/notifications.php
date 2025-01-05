<?php

use mcms\common\form\AjaxActiveForm;
use mcms\common\helpers\Html;
use mcms\partners\assets\basic\ProfileAsset;

ProfileAsset::register($this);

/**
 * @var \yii\web\View $this
 */
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-lg-7">
      <div class="bgf profile">

        <?php $form = AjaxActiveForm::begin([
          'method' => 'post',
          'action' => ['profile/save-notifications-settings'],
          'enableAjaxValidation' => false,
        ]) ?>
          <div class="wallet_settings active" data-show="notify">
            <div class="title">
              <h2><?= Yii::_t('profile.browser_notifications_settings') ?></h2>
            </div>
            <div class="content__position danger_message">
              <i class="icon-danger"></i>
              <p><?= Yii::_t('profile.browser_notifications_hint', Html::a(Yii::_t('profile.browser_notifications_link'), ['notification/index/'])) ?></p>
            </div>
            <div class="content__position subscribe_cb">
                <?php /* TRICKY: закомментировано в задаче MCMS-1467
                   echo  $form->field($userParams, 'notify_browser_system', [
                  'options' => ['class' => 'checkbox checkbox-subscr'],
                  'template' => '{input} {label}',
                ])->checkbox([], false)->label(Yii::_t('profile.show_system_notifications'))*/ ?>
                <?= $form->field($userParams, 'notify_browser_news', [
                  'options' => ['class' => 'checkbox checkbox-subscr'],
                  'template' => '{input} {label}',
                ])->checkbox([], false)->label(Yii::_t('profile.show_news_notifications')) ?>
            </div>
            <?= $form->field($userParams, 'notify_browser_categories', [
              'options' => ['class' => 'content__position set_category'],
              'template' => '<p>' . Yii::_t('profile.notification_categories') . '</p> {input}',
            ])->checkboxList($submodules, [
              'encode' => false,
              'class' => 'btn-group',
              'data-toggle' => 'buttons',
              'itemOptions' => ['labelOptions' => ['class' => 'btn']],
              'item' => function ($index, $label, $name, $checked, $value) {
                return Html::label(
                  Html::input('checkbox', $name, $value, ['checked' => $checked]) . $label,
                  $name,
                  ['class' => 'btn '. ($checked ? 'active' : '')]
                );
              }
            ]); ?>

          </div>

          <div class="wallet_settings" data-show="email">
            <div class="title">
              <h2><?= Yii::_t('profile.email_notifications_settings') ?></h2>
            </div>
            <div class="content__position">
              <?= $form->field($userParams, 'notify_email', [
                'template' => '{label} {input} {error}',
              ])->textInput(['disabled' => true])->label(Yii::_t('profile.email_notifications_email')) ?>
            </div>
            <div class="content__position subscribe_cb">
                <?php /* TRICKY: закомментировано в задаче MCMS-1467
                    echo  $form->field($userParams, 'notify_email_system', [
                  'options' => ['class' => 'checkbox checkbox-subscr'],
                  'template' => '{input} {label}',
                ])->checkbox([], false)->label(Yii::_t('profile.show_system_notifications'))*/ ?>
                <?= $form->field($userParams, 'notify_email_news', [
                  'options' => ['class' => 'checkbox checkbox-subscr'],
                  'template' => '{input} {label}',
                ])->checkbox([], false)->label(Yii::_t('profile.show_news_notifications')) ?>
            </div>
            <?= $form->field($userParams, 'notify_email_categories', [
              'options' => ['class' => 'content__position set_category'],
              'template' => '<p>' . Yii::_t('profile.notification_categories') . '</p> {input}',
            ])->checkboxList($submodules, [
              'encode' => false,
              'class' => 'btn-group',
              'data-toggle' => 'buttons',
              'itemOptions' => ['labelOptions' => ['class' => 'btn']],
              'item' => function ($index, $label, $name, $checked, $value) {
                return Html::label(
                  Html::input('checkbox', $name, $value, ['checked' => $checked]) . $label,
                  $name,
                  ['class' => 'btn '. ($checked ? 'active' : '')]
                );
              }
            ]); ?>

          </div>
        <div class="content__position">
          <div class="form-buttons">
            <div class="form-group">
              <button type="submit" class="btn btn-success"><?= Yii::_t('app.common.Save') ?></button>
            </div>
          </div>
        </div>
        <?php AjaxActiveForm::end(); ?>

      </div>
    </div>
    <div class="col-lg-5 mw600">
      <div class="bgf profile">
        <div class="title">
          <h2><?= Yii::_t('profile.notification_types') ?></h2>
        </div>
        <div class="content__position">
          <ul class="row partners-merchant radio__filter">
            <li class="col-xs-6 visible" data-show="notify">
              <label class="active">
                <i class="merchant-icon icon-news"></i>
                <input type="radio" name="merchant" value="notify" checked=""><?= Yii::_t('profile.browser_notifications') ?>
              </label>
            </li>
            <li class="col-xs-6 visible" data-show="email">
              <label>
                <i class="merchant-icon icon-mail"></i>
                <input type="radio" name="merchant" value="email"><?= Yii::_t('profile.email_notifications') ?>
              </label>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>