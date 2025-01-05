<?php

use mcms\common\widget\AdminGridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

?>

<div class="modal-header">
  <?= Html::button('<span aria-hidden="true">&times;</span>', ['class' => 'close', 'data-dismiss' => 'modal']); ?>
  <h4 class="modal-title"><?= $model->id ?></h4>
</div>

<div class="modal-body">
  <?= DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      'handler_code',
      [
        'attribute' => 'data',
        'format' => 'raw',
        'value' => function ($model) {
          return Html::textarea(
            'data',
            json_encode(json_decode($model->data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ['cols' => 65, 'rows' => 20, 'disabled' => 'disabled']
          );
        }
      ],
      'time:datetime',
    ]
  ]); ?>
</div>
<div class="modal-footer">
  <?= Html::button(Yii::_t('app.common.Close'), ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
</div>
