<?php
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;

/** @var \yii\web\View $this */
/** @var $model \mcms\promo\models\Operator */
/** @var $statisticModule \mcms\statistic\Module */

$this->blocks['actions'] =
  Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'id' => 'show-shortcut',
      'class' => 'btn btn-success',
      'label' => Html::icon('plus') . ' ' . Yii::_t('promo.operators.update')
    ],
    'url' => ['/promo/operators/update', 'id' => $model->id],
  ]);
?>

<?= $this->render('_view', ['model' => $model, 'statisticModule' => $statisticModule]) ?>