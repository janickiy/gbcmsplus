<?php

use yii\helpers\Html;
use mcms\common\form\AjaxActiveForm;
use mcms\partners\components\widgets\PriceWidget;

/**
 * @var \mcms\partners\models\DomainForm $model
 * @var string $aDomainIp
 */
?>
<div class="modal fade parking" id="parkingModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="icon-cancel_4"></i></button>
        <h4 class="modal-title"><?= Yii::_t('domains.add_domain') ?></h4>
      </div>
      <div class="modal-body">

        <!-- Tab panes -->
        <div class="tab-content">
          <div role="tabpanel" class="tab-pane active" id="left">
            <?php $formParked = AjaxActiveForm::begin([
              'id' => 'parkingFormParked',
              'method' => 'post',
              'action' => ['domains/add'],
              'enableAjaxValidation' => true,
              'ajaxSuccess' => 'function(response){$(document).trigger("mcms.domains.added", response)}',
            ]); ?>

              <h5><?= Yii::_t('domains.domains_add_form_parking_1') ?></h5>
              <ul>
                <li><?= Yii::_t('domains.domains_add_form_parking_2') ?></li>
                <li><?= Yii::_t('domains.domains_add_form_parking_3', ['ip' => $aDomainIp]); ?></li>
              </ul>

              <?= $formParked->field($model, 'url')->textInput()->label(false) ?>

              <div class="modal-footer">
                <button type="submit" class="btn btn-success" id="bt1"><?= Yii::_t('domains.domains_add_form_parking_submit') ?></button>
              </div>
            <?php AjaxActiveForm::end(); ?>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>
