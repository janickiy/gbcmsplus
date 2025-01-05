<?php

use mcms\common\form\ActiveKartikForm;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Source;
use mcms\promo\assets\ArbitrarySourcesUpdateAsset;
use mcms\promo\models\Stream;
use mcms\promo\components\widgets\OperatorsDropdown;
use yii\bootstrap\Html;
use yii\helpers\StringHelper;

/**
 * @var Source $model
 * @var $currency
 */

ArbitrarySourcesUpdateAsset::register($this);
$this->title = Yii::_t('promo.arbitrary_sources.update') . ' | ' . $model->name;
/** @var \mcms\promo\Module $module */
$module = Yii::$app->getModule('promo');
/** @var \mcms\common\module\Module $statModule */
$statModule = Yii::$app->getModule('statistic');
/** @var \mcms\statistic\components\api\ModuleSettings $statSettingsApi */
$statSettingsApi = $statModule->api('moduleSettings');
?>

<?php $form = ActiveKartikForm::begin([
    'id' => 'source-form',
    'enableAjaxValidation' => true,
    'options' => ['class' => 'well'],
  ]
);?>

  <div class="row">
    <div class="col-md-8">
      <div class="col-md-12">

        <div class="alert alert-success">
          <p><strong><?= Html::icon('user') . ' ' . $model->userLink ?></strong></p>
          <p><?= $model->link ?></p>
        </div>

        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

        <?= $form->field($model, 'name') ?>

        <?= $form->field($model, 'stream_id')->dropDownList(ArrayHelper::map(Stream::getStreamsByUserId($model->user_id, false), 'id', 'name')) ?>

        <?= $form->field($model, 'domain_id')->dropDownList($domainDropDownItems) ?>

        <?= $form->field($model, 'trafficback_type')->radioList($model->getTrafficbackTypes(), ['inline' => true]) ?>

        <?php /* ?>
        <?php if (Yii::$app->getModule('promo')->settings->getValueByKey(\mcms\promo\Module::SETTINGS_ENABLE_TB_SELL)): ?>
          <?= $form->field($model, 'is_trafficback_sell')->checkbox() ?>
        <?php endif; ?>
        <?php */ ?>

        <?= $form->field($model, 'trafficback_url') ?>

        <?= $this->render('_dynamic_form', ['model' => $model, 'form' => $form])?>

        <?= $this->render('_prelands_form', ['model' => $model, 'form' => $form]) ?>

      </div>

    </div>
    <div class="col-md-4">

      <div class="col-md-12">

        <?= $form->field($model, 'status')->dropDownList($model->statuses, ['prompt' => Yii::_t('app.common.not_selected')]) ?>

        <div id="arbitrary-source-reject-reason" class="<?= $model->isDeclined() ? '' : 'hide'?>"
             data-status-declined="<?= Source::STATUS_DECLINED; ?>">

          <?= $this->render('_user_info', ['model' => $model, 'currency' => $currency]); ?>

          <?= $form->field($model, 'reject_reason')->textarea(); ?>
        </div>

        <hr>

        <?= $form->field($model, 'postback_url')->textInput([
          'disabled' => (bool)$model->use_global_postback_url,
        ]) ?>

        <?=
        $form->field($model, 'use_global_postback_url')->checkbox([
          'disabled' => !$globalPostbackUrl,
          'id' => 'gl_pb',
          'class' => 'checkbox checkbox_with_label',
          'label' =>  '<label for="gl_pb">' . ($globalPostbackUrl ? Yii::_t('partners.links.use_global_pb') . ' (<a href="javascript://" title="' . $globalPostbackUrl . '">' . StringHelper::truncate($globalPostbackUrl, 20) . '</a>)' : Yii::_t('partners.links.global_pb_not_defined')) . '</label>',
        ])
        ?>

        <?=
        $form->field($model, 'use_complains_global_postback_url')->checkbox([
          'disabled' => !$globalComplainsPostbackUrl,
          'id' => 'gl_cpb',
          'class' => 'checkbox checkbox_with_label',
          'label' =>  '<label for="gl_pb">' . ($globalComplainsPostbackUrl ? Yii::_t('partners.links.use_global_сpb') . ' (<a href="javascript://" title="' . $globalComplainsPostbackUrl . '">' . StringHelper::truncate($globalComplainsPostbackUrl, 20) . '</a>)' : Yii::_t('partners.links.global_cpb_not_defined')) . '</label>',
        ])
        ?>

        <?= $form->field($model, 'is_notify_subscribe')->checkbox() ?>

        <?= $form->field($model, 'is_notify_rebill')->checkbox() ?>

        <?= $form->field($model, 'is_notify_unsubscribe')->checkbox() ?>

        <?= $form->field($model, 'is_notify_cpa')->checkbox() ?>

        <hr>

        <?= $form->field($model, 'subid1') ?>

        <?= $form->field($model, 'subid2') ?>

        <hr>

        <?= $form->field($model, 'blockedOperatorIds')->widget(
          OperatorsDropdown::class, [
          'options' =>  [
            'multiple' => true,
            'data-none-selected-text' => Yii::_t('app.common.not_selected'),
          ],
        ]) ?>

        <?= $form->field($model, 'operator_blocked_reason')->textarea() ?>

        <?php if($model->canManageForceOperatorOption()): ?>
          <hr>

          <?= $form->field($model, 'is_allow_force_operator')->checkbox()->hint(
            ($module->isGlobalAllowForceOperator()
              ? Html::tag('p', Yii::_t('promo.arbitrary_sources.global_allow_force_operator-enabled'))
              : '') .
            Html::tag('p', Yii::_t('promo.settings.global_allow_force_operator-hint'))
          ) ?>
        <?php endif ?>

        <?php if($model->canManageTrafficFiltersOff()): ?>
          <?= $form->field($model, 'is_traffic_filters_off')->checkbox() ?>
        <?php endif ?>

      </div>
    </div>
    <div class="col-xs-12">
      <hr>
      <div class="form-group clearfix">
        <?=Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn pull-right ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')])?>
      </div>
    </div>
  </div>

<?php ActiveKartikForm::end();?>