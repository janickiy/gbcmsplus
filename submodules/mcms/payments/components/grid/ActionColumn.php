<?php

namespace mcms\payments\components\grid;


use kartik\helpers\Html;
use mcms\common\grid\ActionColumnAsset;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\AjaxButtons;
use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\Wallet;
use Yii;

class ActionColumn extends \mcms\common\grid\ActionColumn
{
  public function init()
  {
    parent::init();
    $this->buttons['auto-payout'] = function ($url, UserPayment $model, $key) {
      if (!$model->isAvailableAutopay()) return null;

      $walletName = (string)$model->walletModel->name;
      return Html::a(Html::icon('credit-card'), $url, array_merge([
        'title' => Yii::_t('payments.payments.payout-on', $walletName),
        'aria-label' => Yii::_t('payments.payments.payout-on', $walletName),
        AjaxButtons::CONFIRM_ATTRIBUTE => Yii::_t('payments.payments.confirm-auto-payout', $walletName),
        AjaxButtons::AJAX_ATTRIBUTE => 1,
        'class' => 'btn btn-xs btn-default',
      ], $this->buttonOptions));
    };

    $this->buttons['process-payout-modal'] = function ($url, UserPayment $model, $key) {
      if (!$model->isPayable()) return null;

      return Modal::widget([
        'toggleButtonOptions' => [
          'title' => Yii::_t('payments.payments.payout'),
          'tag' => 'span',
          'label' => Html::icon('credit-card'),
          'class' => 'btn btn-xs btn-default',
          'data-pjax' => 0,
        ],
        'size' => Modal::SIZE_LG,
        'url' => $url,
      ]);
    };

    $this->buttons['update-payment'] = function ($url, UserPayment $model, $key) {
      if (!$model->canUpdate()) {
        return null;
      }
      return Html::a(Html::icon('pencil'), $url, array_merge([
        'class' => 'btn btn-xs btn-default'
      ], $this->buttonOptions));
    };

    $this->buttons['view-payment'] = $this->buttons['view'];
  }

}