<?php
use admin\modules\credits\models\Credit;
use admin\modules\credits\models\CreditTransaction;
use rgk\utils\widgets\form\AjaxActiveForm;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;
use yii\web\View;

/** @var \admin\modules\credits\models\form\CreditPaymentForm $model */
/** @var Credit $credit */
/** @var number $balance */
/** @var View $this */

$this->title = $model->isNewRecord ? CreditTransaction::t('create_payment') : CreditTransaction::t('payment') . " #{$model->id}";

$paymentSaveConfirm = CreditTransaction::t('payment_save_confirm');
$this->registerJs(<<<JS
var form = $('#transaction-form');
// не очень хороший момент, но по-другому с аякс-формой не вызвать confirm сообщение
    form.on('beforeFormBlock', function () {
      if (form.hasClass('confirm-yes')) {
        return true;
      }

      yii.confirm('$paymentSaveConfirm', function () {
        $('#transaction-form').addClass('confirm-yes').submit();
      });

      return false;
    });
JS
);

$form = AjaxActiveForm::begin([
  'id' => 'transaction-form',
  'ajaxSuccess' => Modal::ajaxSuccess(['#credits-pjax', '#credit-transactions-pjax']),
]); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?= $this->title ?></h4>
    </div>

    <div class="modal-body">
        <div class="well well-sm" style="margin-bottom: 10px;">
            <b><?= Credit::t('attribute-amount') ?>:</b> <?= Yii::$app->formatter->asCurrency($credit->amount, $credit->currency) ?><br>
            <b><?= Credit::t('attribute-debtSum') ?>:</b> <?= Yii::$app->formatter->asCurrency($credit->getDebt(), $credit->currency) ?><br>
            <b><?= CreditTransaction::t('balance') ?>:</b> <?= Yii::$app->formatter->asCurrency($balance, $credit->currency) ?>
        </div>
        <div class="row">
          <?php if ($model->isNewRecord) { ?>
            <?= $form->field($model, 'credit_id')->hiddenInput(['value' => $credit->id])->label(false) ?>
          <?php } ?>
            <div class="col-sm-12">
              <?= $form->field($model, 'amount') ?>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <div class="row">
            <div class="col-md-12">
              <?= Html::submitButton(
                  '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
                  ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
              ) ?>
            </div>
        </div>
    </div>
<?php AjaxActiveForm::end(); ?>