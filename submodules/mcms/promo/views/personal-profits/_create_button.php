<?php
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;

/** @var integer $userId */
?>

<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' '.Yii::_t('promo.personal-profits.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/promo/personal-profits/create-modal', 'userId' => $userId]),
]) ?>