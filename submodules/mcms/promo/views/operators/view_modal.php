<?php
/** @var \yii\web\View $this */
/** @var $model \mcms\promo\models\Operator */
/** @var $statisticModule \mcms\statistic\Module */

?>
<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>

<div class="modal-body">
  <?= $this->render('_view', ['model' => $model, 'statisticModule' => $statisticModule]) ?>
</div>
