<?php
use mcms\promo\models\UserPromoSetting;
use mcms\partners\components\widgets\TagsTableWidget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\helpers\StringHelper;
use yii\widgets\ActiveForm;
use mcms\partners\assets\PromoLinksAddStep3Asset;

PromoLinksAddStep3Asset::register($this);
?>

<?php $form = ActiveForm::begin([
  'id' => 'linkStep3Form',
  'action' => ['form-handle'],
  'enableAjaxValidation' => true,
  'validateOnBlur' => false,
  'validateOnChange' => false,
]); ?>
<?= Html::hiddenInput('stepNumber', 3) ?>
<?= $form->field($linkStep3Form, 'id', ['options' => ['class' => 'hidden']])->hiddenInput(['id' => 'linkId'])->label(false) ?>

<div class="result_url">
  <div class="result_url-box">
    <div class="result_url-label"><?= Yii::_t('links.your_link') ?>:</div>
    <div class="result_url-input selected__text clipboard">
      <span id="resultLink"><?= $link ? $link->getLink() : 'http://stub.stub' ?></span>
      <div class="result_url-copy">
        <span class="copy-button" data-clipboard-target="#resultLink"><i class="icon-blank"></i></span>
      </div>
    </div>
  </div>
</div>
<?= $this->render('_landing_statuses', ['link' => $link])?>
<div class="result_url-settings">
    <div class="row">
    <div class="col-xs-12">
      <h3><?= Yii::_t('links.additional_tags') ?></h3>
      <div class="row">
        <div class="col-md-3">
          <span class="header_min"><?= Yii::_t('links.click_id') ?> (<?= Yii::_t('links.click_id_description') ?>)</span>
        </div>
        <div class="col-md-2">
            <?= $form->field($linkStep3Form, 'cid', [
              'options' => ['class' => 'form-group before-equal-block all_input_mark'],
            ])->textInput(['placeholder' => 'cid'])->label(false) ?>
          <div class="equal-block">=</div>
        </div>
        <div class="col-md-7">
            <?= $form->field($linkStep3Form, 'cid_value', [
              'options' => ['class' => 'form-group all_input_mark'],
            ])->textInput(['placeholder' => Yii::_t('links.click_id_placeholder')])->label(false) ?>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3">
          <span class="header_min"><?= Yii::_t('links.tag1_description') ?></span>
        </div>
        <div class="col-md-2">
          <div class="before-equal-block">
            <input type="text" value="subid1" disabled class="form-control" />
          </div>
          <div class="equal-block">=</div>
        </div>
        <div class="col-md-7">
          <?= $form->field($linkStep3Form, 'subid1', [
            'options' => ['class' => 'form-group input_mark all_input_mark'],
          ])->textInput(['placeholder' => Yii::_t('links.subid1_placeholder')])->label(false) ?>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <span class="header_min"><?= Yii::_t('links.tag2_description') ?></span>
        </div>
        <div class="col-md-2">
          <div class="before-equal-block">
            <input type="text" value="subid2" disabled class="form-control" />
          </div>
          <div class="equal-block">=</div>
        </div>
        <div class="col-md-7">
          <?= $form->field($linkStep3Form, 'subid2', [
            'options' => ['class' => 'form-group input_mark all_input_mark'],
          ])->textInput(['placeholder' => Yii::_t('links.subid2_placeholder')])->label(false) ?>
        </div>
      </div>
<!-- more -->
      <div class="row">
        <div class="col-md-3">
          <span class="header_min"><?= Yii::_t('links.erid_description') ?></span>
        </div>
        <div class="col-md-2">
          <div class="before-equal-block">
            <input type="text" value="erid" disabled class="form-control" />
          </div>
          <div class="equal-block">=</div>
        </div>
        <div class="col-md-7">
          <?= $form->field($linkStep3Form, 'erid', [
            'options' => ['class' => 'form-group input_mark all_input_mark'],
          ])->textInput(['placeholder' => Yii::_t('links.erid_placeholder')])->label(false) ?>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <span class="header_min"><?= Yii::_t('links.adv_network_description') ?></span>
        </div>
        <div class="col-md-2">
          <div class="before-equal-block">
            <input type="text" value="adv_network" disabled class="form-control" />
          </div>
          <div class="equal-block">=</div>
        </div>
        <div class="col-md-7">
          <?= $form->field($linkStep3Form, 'adv_network', [
            'options' => ['class' => 'form-group input_mark all_input_mark'],
          ])->textInput(['placeholder' => Yii::_t('links.adv_network_placeholder')])->label(false) ?>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <span class="header_min"><?= Yii::_t('links.adv_site_id_description') ?></span>
        </div>
        <div class="col-md-2">
          <div class="before-equal-block">
            <input type="text" value="adv_site_id" disabled class="form-control" />
          </div>
          <div class="equal-block">=</div>
        </div>
        <div class="col-md-7">
          <?= $form->field($linkStep3Form, 'adv_site_id', [
            'options' => ['class' => 'form-group input_mark all_input_mark'],
          ])->textInput(['placeholder' => Yii::_t('links.adv_site_id_placeholder')])->label(false) ?>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <span class="header_min"><?= Yii::_t('links.adv_site_domain_description') ?></span>
        </div>
        <div class="col-md-2">
          <div class="before-equal-block">
            <input type="text" value="adv_site_domain" disabled class="form-control" />
          </div>
          <div class="equal-block">=</div>
        </div>
        <div class="col-md-7">
          <?= $form->field($linkStep3Form, 'adv_site_domain', [
            'options' => ['class' => 'form-group input_mark all_input_mark'],
          ])->textInput(['placeholder' => Yii::_t('links.adv_site_domain_placeholder')])->label(false) ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <h3><?= Yii::_t('links.trafficback_url') ?></h3>
      <?= $form->field($linkStep3Form, 'trafficback_type', ['options' => ['class' => 'trafficback']])->radioList($trafficbackTypes,
        [
          'item' => function ($index, $label, $name, $checked, $value) {
            return
              '<div class="radio radio-primary radio-inline">'.
              Html::radio($name, $checked, [
                'value' => $value,
                'data-tab' => '#trafficback_tab_' . $value,
                'id' => 'trafficback_radio_' . $value
              ]) . '<label for="trafficback_radio_' . $value . '">' . $label . '</label></div>';
          },
        ])->label(false); ?>
      <div class="tab-content trafficback-tab">

        <div class="tab-pane <?= $linkStep3Form->trafficback_type == $trafficbackTypeStaticValue ? 'active' : ''; ?>" role="tabpanel" id="trafficback_tab_<?= $trafficbackTypeStaticValue ?>">
          <?= $form->field($linkStep3Form, 'trafficback_url')->textInput(['placeholder' => Yii::_t('links.enter_the_url')])->label(false)?>
        </div>
        <div class="tab-pane <?= $linkStep3Form->trafficback_type == $trafficbackTypeDynamicValue ? 'active' : ''; ?>" role="tabpanel" id="trafficback_tab_<?= $trafficbackTypeDynamicValue ?>">
          <div class="form-group">
            <p><?= Yii::_t('links.dynamic_trafficback_text_1') ?> <i>?back_url=yoursite.com </i><?= Yii::_t('links.dynamic_trafficback_text_2') ?></p>
          </div>
        </div>
      </div>

      <h3><?= Yii::_t('links.report_postback_url') ?></h3>

      <div id="landings-has-revshare" class="form-group form-group-postback <?= $landingsHasRevshare ? '' : 'hidden' ?>">
        <label class="control-label"><?=Yii::_t('statistic.revshare')?>:</label>

        <?= $form->field($linkStep3Form, 'is_notify_subscribe', [
          'template' => '{input}{label}{hint}',
          'options' => ['class' => 'checkbox checkbox-primary checkbox-inline']
        ])->checkbox([
          'id' => 'notifySubscribeCheck',
          'class' => 'styled',
          'label' => '<label for="notifySubscribeCheck">' . Yii::_t('links.subscriptions') . '</label>',
        ], false) ?>
        <?= $form->field($linkStep3Form, 'is_notify_unsubscribe', [
          'template' => '{input}{label}{hint}',
          'options' => ['class' => 'checkbox checkbox-primary checkbox-inline']
        ])->checkbox([
          'id' => 'notifyUnsubscribeCheck',
          'class' => 'styled',
          'label' => '<label for="notifyUnsubscribeCheck">' . Yii::_t('links.unsubscriptions') . '</label>',
        ], false) ?>
        <?= $form->field($linkStep3Form, 'is_notify_rebill', [
          'template' => '{input}{label}{hint}',
          'options' => ['class' => 'checkbox checkbox-primary checkbox-inline']
        ])->checkbox([
          'id' => 'notifyRebillCheck',
          'class' => 'styled',
          'label' => '<label for="notifyRebillCheck">' . Yii::_t('links.debiting') . '</label>',
        ], false) ?>
      </div>


      <div id="landings-has-cpa" class="form-group form-group-postback <?= $landingsHasCPA ? '' : 'hidden'?>">
        <label class="control-label"><?= Yii::_t('main.cpa') ?></label>

        <?= $form->field($linkStep3Form, 'is_notify_cpa', [
          'template' => '{input}{label}{hint}',
          'options' => ['class' => 'checkbox checkbox-primary checkbox-inline']
        ])->checkbox([
          'id' => 'notifySellCheck',
          'class' => 'styled',
          'label' => '<label for="notifySellCheck">' . Yii::_t('links.sells') . '</label>',
        ], false) ?>
      </div>

      <div class="test__postback-url">
        <?= $form->field($linkStep3Form, 'postback_url')->textInput(['placeholder' => Yii::_t('links.enter_the_url')])->label(false)?>
        <a id="postbackTest" data-toggle="modal" data-target="#postbackTestModal"><i class="icon-icon_test"></i></a>
      </div>

      <div class="form-group">
        <?= $form->field($linkStep3Form, 'use_global_postback_url', [
          'template' => '{input}{label}{hint}',
          'options' => ['class' => 'checkbox checkbox-primary checkbox-inline']
        ])->checkbox([
          'disabled' => !$globalPostbackUrl,
          'id' => 'gl_pb',
          'class' => 'styled',
          'label' => '<label for="gl_pb">' . Yii::_t('partners.links.use_global_pb') . ($globalPostbackUrl ? ' (<a href="javascript://" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . $globalPostbackUrl . '">' . StringHelper::truncate($globalPostbackUrl, 20) . '</a>)' : '') . '</label>',
        ], false) ?>
      </div>

      <div class="form-group">
        <?= $form->field($linkStep3Form, 'use_complains_global_postback_url', [
          'template' => '{input}{label}{hint}',
          'options' => ['class' => 'checkbox checkbox-primary checkbox-inline']
        ])->checkbox([
          'disabled' => !$globalComplainsPostbackUrl,
          'id' => 'gl_сpb',
          'class' => 'styled',
          'label' => '<label for="gl_сpb">' . Yii::_t('partners.links.use_global_сpb') . ($globalComplainsPostbackUrl ? ' (<a href="javascript://" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . $globalComplainsPostbackUrl . '">' . StringHelper::truncate($globalComplainsPostbackUrl, 20) . '</a>)' : '') . '</label>',
        ], false) ?>
      </div>

      <div class="postback-formate">
        <span id="postback_tags" class="collapsed" data-toggle="collapse" href="#table" aria-expanded="false" aria-controls="collapseExample"><span><?= Yii::_t('links.format_transmitted_to_postback_url_request') ?></span> <i class="caret"></i></span>
        <div id="table" class="postback-formate_hidden collapse">
          <p><?= Yii::_t('links.parament_postback_url_description') ?></p>
          <?= TagsTableWidget::widget([
            'targetId' => 'linkstep3form-postback_url',
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
              '{type}' => Yii::_t('links.status_postback_request') . ': on, rebill, sell, off',
              '' => $form->field($linkStep3Form, 'send_all_get_params_to_pb', [
                'template' => '{input}{label}{hint}',
                'options' => ['class' => 'checkbox checkbox-primary checkbox-inline']
              ])->checkbox([
                'id' => 'sendAllGetParamsToPb',
                'class' => 'styled',
                'label' => '<label for="sendAllGetParamsToPb">' . Yii::_t('links.send_all_get_params_to_pb') . '</label>',
              ], false)
            ],
          ]);?>
        </div>


      </div>
      <h3><?= Yii::_t('links.ip_ranges_operators') ?></h3>
      <div class="form-group operator_ip">
        <label class="control-label"><?= Yii::_t('sources.sources_format') ?>:</label>
        <?= Html::radioList('ip_ranges_operators', $ipListFormat, [
          $ipFormatRange => '1.1.1.X - 1.1.1.Y',
          $ipFormatCidr => '1.1.1.1/XX',
        ], [
          'tag' => 'span',
          'item' => function ($index, $label, $name, $checked, $value) {
            return Html::tag('div', Html::radio($name, $checked, [
                'value' => $value,
                'class' => 'ip_ranges',
                'id' => 'ip_ranges_operators_' . $value
              ])
              . Html::label($label, 'ip_ranges_operators_' . $value), ['class' => 'radio radio-primary radio-inline']);
          }
        ]); ?>
        <div class="checkbox checkbox-primary checkbox-inline">
          <?= Html::checkbox('divide_by_operators', $ipListGroup == 1, ['class' => 'ip_ranges', 'id' => 'divide_by_operators']) ?>
          <label for="divide_by_operators"><?= Yii::_t('links.divide_by_operators') ?></label>
        </div>
      </div>
      <div class="ip">
        <pre class="pre__list" id="ipList" style="margin-top: 5px;"><?= $link ? $link->getIPs($ipListFormat, $ipListGroup) : ''?></pre>
        <span class="copy" data-clipboard-target="#ipList"><i class="icon-blank"></i></span>
      </div>
    </div>
  </div>
</div>
<?php ActiveForm::end(); ?>