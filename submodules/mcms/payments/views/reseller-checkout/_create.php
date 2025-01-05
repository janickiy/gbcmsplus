<?php
/**
 * @var $model mcms\payments\models\UserPaymentForm
 * @var $resellerPayPeriod int|null
 * @var $resellerPayPeriodEndDate int|null
 * @var $isInvoiceFileShow array разрешено ли добавление файла инвойса для всех платежных систем
 */

use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\modal\Modal;
use rgk\utils\widgets\form\AjaxActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Json;
use yii\helpers\Url;
?>
<script>
  var walletLimits = <?= Json::encode($walletLimits) ?>;
  var isInvoiceFileShow = <?= Json::encode($isInvoiceFileShow) ?>;
</script>
<?php $form = AjaxActiveForm::begin([
  'action' => Url::to(['create']),
  'isFilesAjaxUpload' => true,
  'ajaxSuccess' => Modal::ajaxSuccess('#reseller-payments'),
]) ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">×</button>
  <h4 class="modal-title"><?= Yii::_t('payments.reseller-profit-log.create') ?></h4>
</div>

<div class="modal-body">
  <div class="row">
    <div class="col-sm-8">
      <?= $form->field($model, 'user_wallet_id')->dropDownList(ArrayHelper::map($model->getWallets(true), 'id', 'name'), [
        'prompt' => Yii::_t('app.common.not_selected'),
      ]); ?>
      <script>
        <?php ob_start() ?>
        (function () {
          $('#userpaymentresellerform-user_wallet_id')
            .on('change', function (event, attribute) {
              if (this.value.length) {
                var limitsHtml = '';
                $.each(walletLimits[this.value], function () {
                  if (!this.length) return;
                  if (limitsHtml.length) limitsHtml = limitsHtml + '<br/>';
                  limitsHtml = limitsHtml + this;
                });

                if (isInvoiceFileShow[this.value]) {
                  $('#invoice_file_block').show();
                } else {
                  $('#invoice_file_block').hide();
                }

                if (limitsHtml.length) {
                  $('#js-wallet-limits').html(limitsHtml).show();
                } else {
                  $('#js-wallet-limits').hide();
                }
              } else {
                $('#js-wallet-limits').hide();
                $('#invoice_file_block').hide();
              }
            });
        })();
        <?php $this->registerJs(ob_get_clean()) ?>
      </script>
      <div id="js-wallet-limits" class="note" style="display: none"></div>
      <?php if ($resellerPayPeriod) { ?>
        <div class="note">
          <?= Yii::_t('payments.reseller.pay_period_end', [
            'date' => Yii::$app->formatter->asDate($resellerPayPeriodEndDate, 'medium'),
            'days' => $resellerPayPeriod,
          ]) ?>
        </div>
      <?php } ?>
      <br/>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'invoice_amount') ?>
    </div>
  </div>
  <div id="invoice_file_block" style="display: none;">
    <?= $form->field($model, 'invoice_file')->fileInput() ?>
  </div>
  <?= $form->field($model, 'description')->textarea() ?>
</div>
<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        Html::icon('save', ['prefix' => 'fa fa-']) . ' ' . Yii::_t('payments.users.payments-order-payment'),
        ['class' => 'pull-right btn btn-success']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveForm::end() ?>
