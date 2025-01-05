<?php

use yii\helpers\Html;

/**
 * @var mcms\payments\components\exchanger\CurrencyCourses $currencies
 * @var array $mainCurrencies
 */
?>
<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $model->name ?></h4>
</div>

<div class="modal-body">

  <?= $this->render('_view', ['model' => $model, 'currencies' => $currencies,'mainCurrencies'=>$mainCurrencies]);?>

</div>

<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>

