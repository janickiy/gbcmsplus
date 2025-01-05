<?php

use mcms\common\widget\modal\Modal;
use mcms\common\form\AjaxActiveForm;
use mcms\payments\models\paysystems\PaySystemApi;
use yii\bootstrap\Html;
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\multilang\widgets\input\TextareaWidget;

/** @var \mcms\payments\models\wallet\Wallet $model */
/** @var bool $canEditAllFields */

$resellerPercentHint = Yii::_t('payments.user-payments.reseller_percent_hint');
$this->registerJs(<<<JS
  $('#reseller-percent-hint').popover({
    trigger: 'hover', 
    content: '$resellerPercentHint'
  });
JS
);
?>

<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#walletsPjaxGrid'),
]); ?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $model->name ?></h4>
</div>

<div class="modal-body">
  <?= $form->field($model, 'is_active')->checkbox(); ?>
  <?php if ($canEditAllFields) { ?>
    <?= $form->field($model, 'name')->widget(InputWidget::class, [
      'class' => 'form-control',
      'form' => $form
    ]) ?>
    <?= $form->field($model, 'info')->widget(TextareaWidget::class, [
      'class' => 'form-control',
      'form' => $form
    ]) ?>
  <?php } ?>
  <?php $rgkPercent = $model->getDefaultProfitPercent() ?>
    <?php // TRICKY Если поставить тип number, браузер даст ввести в поле число +123.5, но при этом не даст считать его из поля,
    // поэтому установлен тип text и сделана автоудаление левых символов ?>
  <?= $form->field($model, 'profit_percent')
    ->hint(
      Yii::_t('payments.user-payments.paysystem_commission') . ": <span id='rgk-percent'></span><br>
" . Yii::_t('payments.user-payments.reseller_commission') . ": <span id='reseller-percent'></span> 
<span id='reseller-percent-hint' class='glyphicon glyphicon-question-sign'></span><br>
" . Yii::_t('payments.user-payments.partner_cost') . ": <span id='partner-percent'></span>
") ?>

  <?php $this->registerJs(<<<JS
var \$profitPercent = $('#wallet-profit_percent');
function formatProfitSum(sum) {
  var sumFormatted = parseFloat(sum).toFixed(2) + '%';
  if (sum > 0) sumFormatted = '<span class="text-success">+' + sumFormatted + '</span>';  
  if (sum < 0) sumFormatted = '<span class="text-danger">' + sumFormatted + '</span>';  
  return sumFormatted;
}

function updateProfitHint() {
  var value = \$profitPercent.val().toString().replace('+', '').replace(',', '.');
  value = parseFloat(value);
  if (!value) value = $rgkPercent;
  var resellerPercent = value;

  $('#rgk-percent').html(formatProfitSum($rgkPercent));
  $('#reseller-percent').html(formatProfitSum(resellerPercent));
  $('#partner-percent').html(formatProfitSum(resellerPercent));
}

updateProfitHint();
// Запрет ввода других символов
\$profitPercent.on('keyup', function() {
  var value = \$profitPercent.val();
  var regular = new RegExp(/[^+\-0-9,.]/g);
  if (value.search(regular) > -1) {
    \$profitPercent.val(value.replace(regular, ''));
  }
});

// Под полем процента отображение профита в зависимости от введенных данных
\$profitPercent.on('change keyup', updateProfitHint);

// Чекбокс Квитнация обязательна не может быть выбран, если чекбокс Отображать поле для квитанции не активно
var inputIsCheckFileShow = $('#wallet-is_check_file_show');
var inputIsCheckFileRequired = $('#wallet-is_check_file_required');
inputIsCheckFileShow.change(function() {
    if (!this.checked) {
        inputIsCheckFileRequired.prop( "checked", false );
    }
});
inputIsCheckFileRequired.change(function() {
    if (this.checked && !inputIsCheckFileShow.prop('checked')) {
        $(this).prop( "checked", false );
    }
});

// Чекбокс Акт обязателен не может быть выбран, если чекбокс Отображать поле для акта не активен
var inputIsInvoiceFileShow = $('#wallet-is_invoice_file_show');
var inputIsInvoiceFileRequired = $('#wallet-is_invoice_file_required');
inputIsInvoiceFileShow.change(function() {
    if (!this.checked) {
        inputIsInvoiceFileRequired.prop( "checked", false );
    }
});
inputIsInvoiceFileRequired.change(function() {
    if (this.checked && !inputIsInvoiceFileShow.prop('checked')) {
        $(this).prop( "checked", false );
    }
});
JS
) ?>
  <?= $form->field($model, 'is_invoice_file_show')->checkbox(); ?>
  <?= $form->field($model, 'is_invoice_file_required', ['hintOptions' => ['class' => 'note']])->checkbox(); ?>
  <?= $form->field($model, 'is_check_file_show')->checkbox(); ?>
  <?= $form->field($model, 'is_check_file_required', ['hintOptions' => ['class' => 'note']])->checkbox(); ?>
<?php $currencies = $model->getCurrencies(false); ?>
<?php foreach (['min_payout_sum', 'max_payout_sum', 'payout_limit_daily', 'payout_limit_monthly'] as $fieldName) { ?>
    <div class="row">
      <label class="col-sm-12 control-label"><?= $model->getAttributeLabel($fieldName) ?></label>
      <?php foreach (['rub', 'usd', 'eur'] as $currency) { ?>
        <?= (in_array($currency, $currencies))
          ? $form
            ->field($model, $currency . '_' . $fieldName, [
                'options' => [
                  'class' => 'col-sm-4',
                ],
                'inputOptions' => [
                  'disabled' => !$canEditAllFields,
                  'class' => 'form-control'
                ],
                'template' => "
  <div class='input-group'>
    <div class='input-group-addon'>" . mb_strtoupper($currency, 'UTF-8') . "</div>
      {input}
  </div>\n{hint}\n{error}"
              ])
            ->label(false)
          : null; ?>
      <?php } ?>
    </div>
  <?php } ?>
  <?php if ($canEditAllFields) { ?>
    <p>* <?= Yii::_t('payments.wallets.limits-hint') ?></p>
    <?php foreach (['rub', 'usd', 'eur'] as $currency) { ?>
      <?php if (!in_array($currency, $currencies)) continue; ?>
      <?php $recipientItems = PaySystemApi::getAvailableApiByRecipientAsItems($model->code, $currency) ?>
      <?= $form->field($model, $currency . '_sender_api_id')->dropDownList(
        $recipientItems,
        ['prompt' => $recipientItems
          ? Yii::_t('app.common.not_selected')
          : Yii::_t('payments.payment-systems-api.paysystems-api-not-available-for-currency')
        ]
      ) ?>
    <?php } ?>
  <?php } ?>
  <?php if (in_array('rub', $model->getCurrencies(false))): ?>
    <?= $form->field($model, 'is_rub')->checkbox(); ?>
  <?php endif; ?>
  <?php if (in_array('usd', $model->getCurrencies(false))): ?>
    <?= $form->field($model, 'is_usd')->checkbox(); ?>
  <?php endif; ?>
  <?php if (in_array('eur', $model->getCurrencies(false))): ?>
    <?= $form->field($model, 'is_eur')->checkbox(); ?>
  <?php endif; ?>
</div>

<div class="modal-footer">
  <div class="row">
    <div class="col-md-12">
      <?= Html::submitButton(
        '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Save'),
        ['class' => 'btn btn-primary']
      ) ?>
    </div>
  </div>
</div>
<?php AjaxActiveForm::end(); ?>


