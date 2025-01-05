<?php
use mcms\common\widget\modal\Modal;
use mcms\promo\Module;
use yii\bootstrap\Html;
use yii\helpers\Url;
/** @var integer $partnerId */
?>

<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . Yii::_t('promo.rebill-conditions.create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['/' . Module::getInstance()->id . '/rebill-conditions/create-modal', 'partnerId' => $partnerId],
]) ?>
