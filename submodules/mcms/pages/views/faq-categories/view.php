<?php
use mcms\common\helpers\Link;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model mcms\pages\models\FaqCategory */
?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>
<div class="modal-body">
  <?= DetailView::widget([
    'model' => $model,
    'attributes' => [
      'id',
      'name',
      'sort',
      [
        'attribute' => 'visible',
        'format' => 'boolean',
      ],
    ],
  ]);
  ?>
</div>