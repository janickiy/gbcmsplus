<?php

use admin\modules\credits\assets\CreditFormAsset;
use admin\modules\credits\models\Credit;
use admin\modules\credits\models\form\CreditForm;
use mcms\common\form\AjaxActiveForm;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Json;
use yii\web\View;

/** @var CreditForm $model */
/** @var View $this */

CreditFormAsset::register($this);

$settings = Credit::getSettings();
$settingsJson = Json::encode($settings);
$this->title = Yii::_t('credits.credit.create');


$formId = 'credits-form';
$this->registerJs(/** @lang JavaScript */"
CreditForm.formId = '$formId';
CreditForm.creditSettings = $settingsJson;
CreditForm.init();
");
?>


<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#credits-pjax'),
  'id' => $formId
]); ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
  </div>

  <div class="modal-body">
    <?php if (is_null($settings)) { ?>
      <div class="alert alert-danger"><?= Yii::_t('credits.credit.unavailable') ?></div>
    <?php } else { ?>
      <div class="row">
        <div class="col-md-6">
          <?= $form->field($model, 'currency')
            ->dropDownList(Credit::getCurrencyList(), ['prompt' => '']) ?>
        </div>
        <div class="col-md-6">
          <?= $form->field($model, 'amount')
            ->hint(
                '<p class="first-fee note hidden">' . Yii::_t('credits.credit.first_fee') . ': <span></span></p>'
                . '<p class="receive-amount note hidden">' . Yii::_t('credits.credit.receive-amount') . ': <span></span></p>'
            ); ?>
        </div>
      </div>

      <div class="calc-settings-warning alert alert-warning hidden">
        <p><?= Yii::_t('credits.credit.select-currency-warning') ?></p>
      </div>

      <div class="calc-settings-block alert alert-info hidden">
        <p class="credit-limit"><?= Yii::_t('credits.credit.limit') ?>: <span></span></p>
        <p class="credit-percent"><?= Yii::_t('credits.credit.percent') ?>: <span></span></p>
      </div>

  <?php } ?>

  </div>

  <div class="modal-footer">
    <?php if (!is_null($settings)) { ?>
      <div class="row">
        <div class="col-md-12">
          <?= Html::submitButton(
              '<i class="fa fa-save"></i> ' . Yii::_t('credits.credit.create'),
              [
                'class' => 'btn btn-success',
                'data-confirm' => Yii::_t('credits.credit.confirm-msg'),
              ]
          ) ?>
        </div>
      </div>
    <?php } ?>
  </div>
<?php AjaxActiveForm::end(); ?>