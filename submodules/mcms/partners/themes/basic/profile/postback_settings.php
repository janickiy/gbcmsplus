<?php

use kartik\form\ActiveForm;
use mcms\partners\assets\ClipboardAsset;
use mcms\partners\assets\PromoAsset;
use mcms\partners\components\widgets\TagsTableWidget;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;

PromoAsset::register($this);
ClipboardAsset::register($this);

$this->registerJs(<<<JS
  var settingPostbackUrl = $('#userpromosetting-postback_url'),
      linkFormPostbackUrl = $('#testpostbackurlform-postbackurl'),
      postbackTestForm = $('#testPostbackUrl');

  postbackTestForm.off('submit.updateCheckboxes');
  linkFormPostbackUrl.val(settingPostbackUrl.val());
  
  $('.testButton').click(function(){
    if ($(this).data('type') === 'complainsPostback') {
      $('#testpostbackurlform-postbackurl').val($('#userpromosetting-complains_postback_url').val());
    } else {
      $('#testpostbackurlform-postbackurl').val($('#userpromosetting-postback_url').val());
    }
  });
  
  postbackTestForm.on('submit', function(e) {
    e.preventDefault();
  
    $('#postbackTestResult').html('');
    var data = postbackTestForm.serializeArray();
  
    $.ajax({
      url: postbackTestForm.attr('action'),
      type: 'post',
      data: data
    }).done(function(res) {
      if(res.success) {
        postbackTestForm
          .find('.form-group')
          .removeClass('has-error')
          .find('.help-block').empty();
        $('#postbackTestResult').html(res.data);
      } else {
        var errors = res.error;
        if(errors != null) {
          $.each( errors, function( field, error ){
            postbackTestForm
              .find('.field-' + field)
              .addClass('has-error')
              .find('.help-block').html(error);
          });
        }
      }
    });
  });
JS
)
?>
<?php /** @var \mcms\partners\Module $partnersModule */?>
<?php $partnersModule = Yii::$app->getModule('partners'); ?>

<?php $form = ActiveForm::begin(['id' => 'profile-form']); ?>
<div class="container-fluid postback-settings">
    <div class="row">
      <div class="col-xs-12 col-md-6">
        <div class="bgf profile">
          <div class="content__position">
            <h3><?= Yii::_t('links.global_postback') ?></h3>

            <div class="test__postback-url">
              <?= $form->field($model, 'postback_url')->textInput()->label(false); ?>
              <?= $form->field($linkForm, 'postback_url')->hiddenInput()->label(false); ?>
              <a data-toggle="modal" data-type="postback" class="testButton" data-target="#postbackTestModal"><i class="icon-icon_test"></i></a>
            </div>

            <div class="postback-formate">
              <span id="postback_tags" class="collapsed" data-toggle="collapse" href="#table" aria-expanded="false" aria-controls="collapseExample"><span><?= Yii::_t('links.format_transmitted_to_postback_url_request') ?></span> <i class="caret"></i></span>
              <div id="table" class="postback-formate_hidden collapse">
                <p><?= Yii::_t('links.parament_postback_url_description') ?></p>
                <?= TagsTableWidget::widget([
                  'targetId' => 'userpromosetting-postback_url',
                  'data' => [
                    '{cid}' => Yii::_t('links.cid'),
                    '{label1}' => Yii::_t('links.tag') . ' 1',
                    '{label2}' => Yii::_t('links.tag') . ' 2',
                    '{subid1}' => Yii::_t('links.subid') . ' 1',
                    '{subid2}' => Yii::_t('links.subid') . ' 2',
                    '{subscription_id}' => Yii::_t('partners.links.subscription_id'),
                    '{rebill_id}' => Yii::_t('partners.links.rebill_id'),
                    '{stream_id}' => Yii::_t('links.digital_id_stream'),
                    '{link_id}' => Yii::_t('links.link_id_stream'),
                    '{operator_id}' => Yii::_t('links.digital_operator_id'),
                    '{landing_id}' => Yii::_t('links.digital_landing_id'),
                    '{link_name}' => Yii::_t('links.symbolic_link_name'),
                    '{link_hash}' => Yii::_t('links.link_hash'),
                    '{action_time}' => Yii::_t('links.unix_time'),
                    '{action_date}' => Yii::_t('links.date_of_the_operation_in_format') . ' Y-m-d H:i:s',
                    '{notice_time}' => Yii::_t('links.unix_runtime_post_back_request'),
                    '{notice_date}' => Yii::_t('links.date_post_back_request_format') . ' Y-m-d H:i:s',
                    '{sum_rub}' => Yii::_t('links.amount_of_transactions') . ' RUB',
                    '{sum_eur}' => Yii::_t('links.amount_of_transactions') . ' EUR',
                    '{sum_usd}' => Yii::_t('links.amount_of_transactions') . ' USD',
                    '{type}' => Yii::_t('links.status_postback_request') . ': on, rebill, sell, off'
                  ],
                ]);?>
              </div>
            </div>

            <h3><?= Yii::_t('links.global_complains_postback') ?></h3>

            <div class="test__postback-url">
              <?= $form->field($model, 'complains_postback_url')->textInput()->label(false); ?>
              <?= $form->field($linkForm, 'complains_postback_url')->hiddenInput()->label(false); ?>
              <a data-toggle="modal" data-type="complainsPostback" class="testButton" data-target="#postbackTestModal"><i class="icon-icon_test"></i></a>
            </div>

            <div class="postback-formate">
              <span id="complains_postback_tags" class="collapsed" data-toggle="collapse" href="#complains_table" aria-expanded="false" aria-controls="collapseExample"><span><?= Yii::_t('links.format_transmitted_to_postback_url_request') ?></span> <i class="caret"></i></span>
              <div id="complains_table" class="postback-formate_hidden collapse">
                <p><?= Yii::_t('links.parament_postback_url_description') ?></p>
                <?= TagsTableWidget::widget([
                  'targetId' => 'userpromosetting-complains_postback_url',
                  'data' => [
                    '{cid}' => Yii::_t('links.cid'),
                    '{label1}' => Yii::_t('links.tag') . ' 1',
                    '{label2}' => Yii::_t('links.tag') . ' 2',
                    '{subid1}' => Yii::_t('links.subid') . ' 1',
                    '{subid2}' => Yii::_t('links.subid') . ' 2',
                    '{subscription_id}' => Yii::_t('partners.links.subscription_id'),
                    '{stream_id}' => Yii::_t('links.digital_id_stream'),
                    '{link_id}' => Yii::_t('links.link_id_stream'),
                    '{operator_id}' => Yii::_t('links.digital_operator_id'),
                    '{landing_id}' => Yii::_t('links.digital_landing_id'),
                    '{link_name}' => Yii::_t('links.symbolic_link_name'),
                    '{link_hash}' => Yii::_t('links.link_hash'),
                    '{action_time}' => Yii::_t('links.unix_time'),
                    '{action_date}' => Yii::_t('links.date_of_the_operation_in_format') . ' Y-m-d H:i:s',
                    '{notice_time}' => Yii::_t('links.unix_runtime_post_back_request'),
                    '{notice_date}' => Yii::_t('links.date_post_back_request_format') . ' Y-m-d H:i:s',
                    '{type}' => Yii::_t('links.status_postback_request') . ': on, rebill, sell, off'
                  ],
                ]);?>
              </div>
            </div>

            <div class="form-buttons text-right">
              <input type="submit" value="<?= Yii::_t('app.common.Save') ?>" class="btn btn-success" />
            </div>
          </div>
        </div>
      </div>
      <div class="col-xs-12 col-md-6">
        <div class="bgf profile">
          <div class="content__position">
            <div class="row">
              <div class="col-xs-12 col-sm-6">
                <?= Yii::_t('partners.settings.postback_url_spread') ?>
              </div>
              <div class="col-xs-12 col-sm-6 form-buttons text-right">
                <?= Html::a(Yii::_t('partners.main.apply'), ['/partners/profile/postback-url-spread'], [
                  'onclick' => 'return confirm("' . Yii::_t('partners.main.global_postback_replace') . '")',
                  'class' => 'btn btn-default',
                ]) ?>
              </div>
            </div>
            <div class="row">
              <div class="col-xs-12 col-sm-6">
                <?= Yii::_t('partners.settings.complains_postback_url_spread') ?>
              </div>
              <div class="col-xs-12 col-sm-6 form-buttons text-right">
                <?= Html::a(Yii::_t('partners.main.apply'), ['/partners/profile/complains-postback-url-spread'], [
                  'onclick' => 'return confirm("' . Yii::_t('partners.main.global_postback_replace') . '")',
                  'class' => 'btn btn-default',
                ]) ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php Modal::begin([
  'header' => '<h4 class="modal-title">' . Yii::_t('links.test_postback_url') . '</h4>',
  'closeButton' => ['label' => '<i class="icon-cancel_4"></i>'],
  'options' => [
    'id' => 'postbackTestModal',
    'class' => 'modal fade modal__postback-test-url',
  ],
]); ?>

<?php
/* @var mcms\partners\models\TestPostbackUrlForm $testPostbackUrlForm*/
$form = ActiveForm::begin([
  'id' => 'testPostbackUrl',
  'action' => ['/partners/links/test-postback-url'],
]); ?>

<div class="row">
  <div class="col-xs-12 col-sm-8">
    <?= $form->field($testPostbackUrlForm, 'postbackTestLink')->textInput(['placeholder' => Yii::_t('links.enter_the_url')])->label(false) ?>
    <?= $form->field($testPostbackUrlForm, 'postbackUrl')->hiddenInput()->label(false) ?>
    <?= $form->field($testPostbackUrlForm, 'linkId')->hiddenInput()->label(false) ?>
    <?= $form->field($testPostbackUrlForm, 'on')->hiddenInput()->label(false) ?>
    <?= $form->field($testPostbackUrlForm, 'off')->hiddenInput()->label(false) ?>
    <?= $form->field($testPostbackUrlForm, 'rebill')->hiddenInput()->label(false) ?>
    <?= $form->field($testPostbackUrlForm, 'cpa')->hiddenInput()->label(false) ?>
  </div>
  <div class="col-xs-12 col-sm-4">
    <button id="testPostbackSubmitBt" class="btn btn-primary"><?= Yii::_t('links.postback_send') ?></button>
  </div>
</div>
<div class="form-group">
  <label for="postbackTestResult"><?= Yii::_t('links.postback_query_result') ?></label>
  <textarea class="form-control" id="postbackTestResult" readonly></textarea>
</div>

<?php ActiveForm::end(); ?>

<?php Modal::end(); ?>
