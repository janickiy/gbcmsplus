<?php

use mcms\common\helpers\Html;

/** @var \mcms\promo\models\TrafficType[] $trafficTypes  */
?>
<div class="modal-dialog" role="document">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-cancel_4"></i></button>
      <h4 class="modal-title" id="myModalLabel">
        <?= $landing->id ?>. <?= $landing->name ?>
      </h4>
    </div>
    <div class="modal-body">
      <div class="traff-type form-group">

        <div class="filter form-group field-traffic_type">
          <div class="filter-header">
            <span><?= Yii::_t('links.traffic_type') ?><i></i></span>
            <div class="caret_wrap">
              <i class="caret"></i>
            </div>

          </div>
          <div class="filter-body filter-body_left">
            <div class="filter-body_selected">
              <div class="hidden_text"><?= Yii::_t('links.payment_types_selected_none') ?></div>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('links.payment_types_selected_all') ?></div>
              <div class="form-group">
                <?php foreach($trafficTypes as $trafficType): ?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" class="styled" id="requestType<?= $trafficType->id ?>" name="landingRequestType" value="<?= $trafficType->id ?>">
                    <label for="requestType<?= $trafficType->id ?>"><?= $trafficType->name ?></label>
                  </div>
                <?php endforeach ?>
              </div>

            </div>
          </div>
          <div class="help-block"></div>
        </div>
        <div class="form-group">
          <?= Html::radioList('profitType', key($profitTypes), $profitTypes, [
            'item' => function ($index, $label, $name, $checked, $value) {
              return
                '<div class="radio radio-primary radio-inline">' .
                Html::radio($name, $checked, [
                  'value' => $value,
                  'id' => 'profitType_radio_' . $value
                ]) . '<label for="profitType_radio_' . $value . '">' . $label . '</label></div>';
            },
          ]); ?>
        </div>
        <div class="form-group field-description">
          <label for="landingRequestDesc" class="control-label"><?= Yii::_t('links.traffic_type_description') ?></label>
          <textarea class="form-control" name="" id="landingRequestDesc" cols="30" rows="6"></textarea>
          <div class="help-block"></div>
        </div>
        <p><?= Yii::_t('links.request_hint') ?></p>
      </div>
      <?= Html::hiddenInput('landingId', $landing->id, ['id' => 'landingRequestId']) ?>
    </div>
    <div class="modal-footer">
      <div class="row">
        <div class="col-xs-6 text-left">
          <span class="go_back"><i class="icon-double_arrow"></i><?= Yii::_t('main.prev') ?></span>
        </div>
        <div class="col-xs-6">
          <a class="btn btn-success" data-landing-id="<?= $landing->id ?>" data-operator-id="<?= $operatorId ?>" id="landingRequestSubmitBt"><?= Yii::_t('links.send_request') ?></a>
        </div>
      </div>


    </div>
  </div>
</div>