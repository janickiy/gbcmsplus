<?php
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\partners\components\helpers\DomainClassAttributeHelper;
use mcms\partners\components\widgets\CheckDomainWidget;
use yii\widgets\ActiveForm;
use mcms\partners\assets\PromoLinksAddStep1Asset;

PromoLinksAddStep1Asset::register($this);
?>

<?php $form = ActiveForm::begin([
  'id' => 'linkStep1Form',
  'action' => ['form-handle'],
  'enableAjaxValidation' => true,
  'validateOnBlur' => false,
  'validateOnChange' => false,
]); ?>
<?= Html::hiddenInput('stepNumber', 1) ?>
<?= $form->field($linkStep1Form, 'id', ['options' => ['class' => 'hidden']])->hiddenInput(['id' => 'linkId'])->label(false) ?>

<div class="step_position">
  <div class="row">
    <div class="col-xs-5">
      <div class="form-group">
        <?= $form->field($linkStep1Form, 'name')->textInput(['placeholder' => Yii::_t('links.link_name')])->label(false) ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-5">
      <div class="form-group">
        <?= $form->field($linkStep1Form, 'stream_id', ['options' => ['data-stream-id' => true]])
          ->dropDownList($streams, [
            'class' => 'selectpicker',
            'data-none-selected-text' => Yii::_t('main.nothing_selected'),
            'data-width' => '100%'
          ])->label(false)?>
        <?= $form->field($linkStep1Form, 'isNewStream')->hiddenInput(['value' => 0])->label(false) ?>
        <?= $form->field($linkStep1Form, 'streamName')->hiddenInput(['value' => ''])->label(false) ?>
        </div>
    </div>
    <div class="col-xs-7">
      <button data-toggle="modal" data-target="#streamModal" type="button" class="btn btn-primary"><?= Yii::_t('links.create_stream') ?></button>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-5">
      <div class="form-group">
        <?= $form->field($linkStep1Form, 'domain_id')->dropDownList($domainsItems, [
          'class' => 'selectpicker',
          'data-none-selected-text' => Yii::_t('main.nothing_selected'),
          'data-width' => '100%',
          'options' => ArrayHelper::map($domains, 'id', function($domain) {
            return  [
              'data-content' => '<i class=\'icon ' . DomainClassAttributeHelper::getDomainClass($domain->isActive()) . ' icon-shield\'></i>' . $domain->url,
              'disabled' => $domain->isBanned(),
            ];
          }),
          'groups' => [
            $isSystemKeySystem => ['label' => Yii::_t('domains.domain_group_system'), 'class' => 'system-domains-group'],
            $isSystemKeyParked => ['label' => Yii::_t('domains.domain_group_parked'), 'class' => 'parked-domains-group'],
          ],
        ])->label(false) ?>
      </div>
    </div>
    <div class="col-xs-7">
      <button data-toggle="modal" data-target="#parkingModal" type="button" class="btn btn-primary"> <?= Yii::_t('links.park') ?></button>
      <?= CheckDomainWidget::widget(['domainSelector' => '$(\'#linkstep1form-domain_id option:selected\').html()']) ?>
    </div>
  </div>
</div>
<?php ActiveForm::end(); ?>