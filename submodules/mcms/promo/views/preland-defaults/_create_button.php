<?php
use mcms\common\widget\modal\Modal;
use mcms\promo\Module;
use yii\bootstrap\Html;
use yii\helpers\Url;
use mcms\promo\controllers\PrelandDefaultsController;

/** @var integer $userId */
?>

<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . PrelandDefaultsController::translate('create'),
    'class' => 'btn btn-xs btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/' . Module::getInstance()->id . '/preland-defaults/form-modal']),
]) ?>
