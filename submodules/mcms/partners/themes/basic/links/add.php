<?php

use yii\helpers\Url;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use mcms\partners\assets\PromoLinksAddAsset;
use mcms\common\helpers\Html;
use mcms\common\helpers\Link;
use mcms\partners\assets\ClipboardAsset;
use mcms\partners\components\widgets\DomainCreateFormWidget;
use mcms\partners\components\helpers\DomainClassAttributeHelper;

PromoLinksAddAsset::register($this);
ClipboardAsset::register($this);
/* @var mcms\partners\models\LinkForm $linkForm */
/* @var mcms\common\web\View $this */
?>
<?php $this->beginBlock('viewport'); ?><meta name="viewport" content="width=1250"><?php $this->endBlock() ?>
<div class="container-fluid no-max-width">
  <div class="row">
    <div class="col-xs-12">
      <div class="bgf">
        <div class="steps_wrap addLinks-container"
             data-next="<?= Yii::_t('main.next') ?>"
             data-done="<?= Yii::_t('main.done') ?>"
             data-step1-action="<?= Url::to(['add-step1']) ?>"
             data-step2-action="<?= Url::to(['add-step2']) ?>"
             data-step3-action="<?= Url::to(['add-step3']) ?>"
             data-list-action="<?= Url::to(['landing-list']) ?>"
             data-request-action="<?= Url::to(['landing-request']) ?>"
             data-modal-action="<?= Url::to(['landing-modal']) ?>"
             data-request-modal-action="<?= Url::to(['request-modal']) ?>"
             data-change-view-action="<?= Url::to(['change-landings-view']) ?>"
             data-country-paytypes="<?= Html::encode(Json::encode($countryPayTypes)) ?>"
             data-operator-paytypes="<?= Html::encode(Json::encode($operatorPayTypes)) ?>"
             data-country-offers="<?= Html::encode(Json::encode($countryOfferCategories)) ?>"
             data-operator-offers="<?= Html::encode(Json::encode($operatorOfferCategories)) ?>"
             data-showtype="<?= $showType ?>"
             data-domain-class-active="<?= DomainClassAttributeHelper::DOMAIN_CLASS_ACTIVE ?>"
             data-domain-class-banned="<?= DomainClassAttributeHelper::DOMAIN_CLASS_BANNED ?>"
        >

          <div class="title">
            <h2><?= $link ? $link->name : Yii::_t('links.new_link') ?></h2>
            <?= Link::get('index', [], ['class' => 'title__link'], '<i class="icon-double_arrow"></i>'. Yii::_t('links.to_link_list')) ?>
          </div>
          <div class="row change__step">
            <div data-step="1" class="col-xs-4 steps_progress active travel">
              <span><?= Yii::_t('links.create_link') ?></span>
            </div>
            <div data-step="2" class="col-xs-4 steps_progress">
              <span><?= Yii::_t('links.select_landings') ?></span>
            </div>
            <div data-step="3" class="col-xs-4 steps_progress">
              <span><?= Yii::_t('links.settings') ?></span>
            </div>
          </div>

          <div class="steps content__position step_land">
            <div class="step__1 step-empty"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>
            <div class="step__2 step-empty"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>
            <div class="step__3 step-empty"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>
          </div>
          <div class="steps__buttons">
            <span class="btn btn-default pull-left hidden" id="prev__step"><?= Yii::_t('main.prev') ?></span>
            <span class="btn btn-default" id="next__step"><?= Yii::_t('main.next') ?></span>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div class="modal fade parking" id="streamModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
    <?php $form = ActiveForm::begin([
      'enableAjaxValidation' => true,
      'id' => 'newStreamForm',
    ]); ?>
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-cancel_4"></i></button>
          <h4 class="modal-title" id="myModalLabel"><?= Yii::_t('links.new_stream') ?></h4>
        </div>
        <div class="modal-body" style="padding: 30px 40px 15px 40px;">

          <div class="form-group">
            <?= $form->field($streamModel, 'name')->label(false) ?>
          </div>

        </div>
        <div class="modal-footer">
          <input type="submit" class="btn btn-primary" id="newStreamBtn" value="<?= Yii::_t('main.create') ?>">
        </div>
      </div>
    </div>
    <?php ActiveForm::end(); ?>
  </div>

  <?= DomainCreateFormWidget::widget() ?>

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
    'action' => ['test-postback-url'],
  ]); ?>

  <div class="row">
    <div class="col-xs-8">
      <?= $form->field($testPostbackUrlForm, 'postbackTestLink')->textInput(['placeholder' => Yii::_t('links.enter_the_url')])->label(false) ?>
      <?= $form->field($testPostbackUrlForm, 'postbackUrl')->hiddenInput()->label(false) ?>
      <?= $form->field($testPostbackUrlForm, 'linkId')->hiddenInput()->label(false) ?>
      <?= $form->field($testPostbackUrlForm, 'on')->hiddenInput()->label(false) ?>
      <?= $form->field($testPostbackUrlForm, 'off')->hiddenInput()->label(false) ?>
      <?= $form->field($testPostbackUrlForm, 'rebill')->hiddenInput()->label(false) ?>
      <?= $form->field($testPostbackUrlForm, 'cpa')->hiddenInput()->label(false) ?>
    </div>
    <div class="col-xs-4">
      <button id="testPostbackSubmitBt" class="btn btn-primary"><?= Yii::_t('links.postback_send') ?></button>
    </div>
  </div>
  <div class="form-group">
    <label for="postbackTestResult"><?= Yii::_t('links.postback_query_result') ?></label>
    <textarea class="form-control" id="postbackTestResult" readonly></textarea>
  </div>

  <?php ActiveForm::end(); ?>

  <?php Modal::end(); ?>

  <div class="pre-overlay">
    <div class="pre-overlay_content">
      <img src="/img/loading_clock.svg" alt="">
      <span><?= Yii::_t('main.loading') ?>...</span>
    </div>
  </div>

</div>