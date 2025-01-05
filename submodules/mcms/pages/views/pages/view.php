<?php

/** @var mcms\common\web\View $this */
use yii\widgets\DetailView;

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
      'seo_title',
      'url',
      'code',
      'seo_description',
      [
        'attribute' => 'text',
        'format' => 'raw',
        'value' => strip_tags($model->text)
      ],
      [
        'attribute' => 'created_at',
        'format' => 'datetime'
      ],
      [
        'attribute' => 'updated_at',
        'format' => 'datetime'
      ]
    ],
  ]);
  ?>

  <h5><?= Yii::_t('main.text') ?></h5>
  <?= $this->render('_text', ['model' => $model]) ?>
</div>
