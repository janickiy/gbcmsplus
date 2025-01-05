<?php

use yii\helpers\Html;

?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= Yii::$app->formatter->asText($model->url); ?></h4>
</div>

<div class="modal-body">

  <?= $this->render('_view', ['model' => $model]);?>

</div>
<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>

