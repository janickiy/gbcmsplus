<?php

use mcms\common\form\AjaxActiveForm;
use mcms\currency\models\Currency;
use mcms\promo\components\api\MainCurrencies;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var Currency $model */
/** @var View $this */

$this->title = Yii::_t('app.common.Update') . ' ' . strtoupper($model->code);
$validateCourseUrl = Url::to(['/currency/default/validate-custom-course', 'id' => $model->id]);
$this->registerJs(<<<JS
    var form = $('#currency-form');
    var errors = $('.errors');
    form.find('input').change(function() {
      $.post('$validateCourseUrl', form.serializeArray(), function (response) {
        errors.empty();
        $.each(response.error, function( index, value ) {
         errors.append(value + '<br>');
        });
      });
    });
JS
);
?>


<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#currenciesPjaxGrid'),
  'id' => 'currency-form',
]) ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?php foreach ([MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR] as $currencyCode) { ?>
      <?php if ($currencyCode !== $model->code) { ?>
        <div class="row">
          <div class="col-md-6">
            <?= $form->field($model, 'partner_percent_' . $currencyCode); ?>
          </div>
          <div class="col-md-6">
            <?= $form->field($model, 'custom_to_' . $currencyCode); ?>
          </div>
        </div>
      <?php } ?>
    <?php } ?>
    <div class="errors" style="color: red;"></div>
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