<?php

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\promo\assets\WebmasterSourcesFormAssets;
use mcms\promo\models\LandingSet;
use mcms\promo\models\Source;
use mcms\promo\components\widgets\OperatorsDropdown;
use cakebake\bootstrap\select\BootstrapSelectAsset;
use yii\widgets\ActiveForm;
use mcms\promo\models\AdsType;
use mcms\promo\components\widgets\BannerPicker;

BootstrapSelectAsset::register($this, ['selector' => '.operators-selectpicker']);
WebmasterSourcesFormAssets::register($this);
/**
 * @var yii\web\View $this
 * @var Source $model
 * @var yii\widgets\ActiveForm $form
 * @var $currency
 */

$this->title = Yii::_t('promo.webmaster_sources.update') . ' | ' . $model->name;

/** @var \mcms\promo\Module $module */
$module = Yii::$app->getModule('promo');
/** @var \mcms\common\module\Module $statModule */
$statModule = Yii::$app->getModule('statistic');
/** @var \mcms\statistic\components\api\ModuleSettings $statSettingsApi */
$statSettingsApi = $statModule->api('moduleSettings');

$isGlobalAutoRotationEnabled = $module->getIsLandingsAutoRotationGlobalEnabled();

$adsTypeReplaceLinks = AdsType::findOne(['code' => 'replace_links'])->id;
$this->registerJs(<<<JS
    var adsType = $('#adsType');
    var container = $('.replace-links-params-container');

    function refreshAdsType() {
        if (adsType.val() == $adsTypeReplaceLinks) {
            container.show();
        } else {
            container.hide();
        }
    }
    
    adsType.on('change', refreshAdsType);
    refreshAdsType();
JS
);

?>

<?php $form = ActiveForm::begin([
    'id' => 'source-form',
    'enableAjaxValidation' => true,
    'options' => ['class' => 'well'],
  ]
); ?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

<div class="row">
  <div class="col-lg-8 col-md-8 col-sm-8">

    <div class="alert alert-success">
      <p><strong><?= Html::icon('user') . ' ' . $model->userLink ?></strong></p>
    </div>

    <?= $form->field($model, 'url') ?>
    <?= $form->field($model, 'allow_all_url')->checkbox([
      'label' => Html::tag('span', $model->getAttributeLabel('allow_all_url')) .
        (Html::tag('p', ($module->isCheckDomainDisabled() ?
          Yii::_t('promo.webmaster_sources.global_domain_checking_disabled') :
          Yii::_t('promo.webmaster_sources.global_domain_checking_enabled')
        ), ['class' => 'note'])),
      'class' => 'checkbox',
    ]) ?>

    <?= $form->field($model, 'ads_type')->dropDownList(AdsType::getDropDown(), ['id' => 'adsType']) ?>
    <div class="replace-links-params-container" style="display: none;">
      <?= $form->field($model, 'replace_links_css_class')->textInput() ?>
    </div>

    <?= $form->field($model, 'bannersIds')
      ->widget(BannerPicker::class)
      ->label(false);
    ?>

    <?= $form->field($model, 'banner_show_limit') ?>

    <?php if (!$model->isAutoRotationEnabled()): ?>
      <?= $form->field($model, 'set_id')->dropDownList(
        ArrayHelper::map(LandingSet::getByCategory($model->category_id, $model->set_id), 'id', 'name'),
        ['prompt' => Yii::_t('app.common.choose')]
      ) ?>

      <div class="form-group">
        <?= Html::a(
          '<span class="btn-label"><i class="glyphicon glyphicon-refresh"></i></span>' . Yii::_t('promo.sources.landing-set-sync'),
          ['/promo/webmaster-sources/landing-sets-sync', 'id' => $model->id],
          ['data-method' => 'post', 'class' => 'btn btn-labeled btn-info', 'data-confirm' => Yii::_t('promo.webmaster_sources.are-you-shure')]
        ) ?>
        <?= Html::a(
          '<span class="btn-label"><i class="glyphicon glyphicon-trash"></i></span>' . Yii::_t('promo.sources.remove-landing-set'),
          ['/promo/webmaster-sources/landing-sets-delete', 'id' => $model->id],
          ['data-method' => 'post', 'class' => 'btn btn-labeled btn-danger', 'data-confirm' => Yii::_t('promo.webmaster_sources.are-you-shure')]
        ) ?>
      </div>

      <?= $form->field($model, 'landing_set_autosync')->checkbox([
        'label' => Html::tag('span', $model->getAttributeLabel('landing_set_autosync')),
        'class' => 'checkbox',
      ]) ?>
    <?php endif ?>

    <?php if ($isGlobalAutoRotationEnabled) :?>
      <?= $form->field($model, 'is_auto_rotation_enabled')->checkbox([
        'label' => Html::tag('span', $model->getAttributeLabel('is_auto_rotation_enabled')),
        'class' => 'checkbox',
      ]) ?>
    <?php endif ?>

    <?php if ($model->landing_set_autosync): ?>
      <?= $this->render('_landing_list', ['model' => $model]) ?>
    <?php else: ?>
      <?= $this->render('_dynamic_form', [
        'model' => $model,
        'form' => $form,
      ]) ?>
    <?php endif; ?>

    <?= $this->render('_prelands_form', [
      'model' => $model,
      'form' => $form,
    ]); ?>
  </div>
  <div class="col-lg-4 col-md-4 col-sm-4">

    <?= $form->field($model, 'category_id')->dropDownList($model->categories, ['prompt' => Yii::_t('app.common.choose')]) ?>

    <?= $form->field($model, 'status')->dropDownList($model->statuses) ?>

    <div id="webmaster-source-reject-reason" class="<?= $model->isDeclined() ? '' : 'hide' ?>"
         data-status-declined="<?= Source::STATUS_DECLINED; ?>">

      <?= $this->render('_user_info', ['model' => $model, 'currency' => $currency]); ?>

      <?= $form->field($model, 'reject_reason')->textarea(); ?>
    </div>

    <?= $form->field($model, 'blockedOperatorIds')->widget(
      OperatorsDropdown::class, [
      'options' =>  [
        'multiple' => true,
        'data-none-selected-text' => Yii::_t('app.common.not_selected'),
      ],
    ]) ?>

    <?= $form->field($model, 'operator_blocked_reason')->textarea() ?>

  </div>
  <div class="col-xs-12">
    <hr>
    <div class="form-group clearfix">
      <?=Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn pull-right ' . ($model->isNewRecord ? 'btn-success' : 'btn-primary')])?>
    </div>
  </div>
</div>

<?php ActiveForm::end(); ?>

