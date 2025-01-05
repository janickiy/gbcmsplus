<?php

namespace mcms\payments\components\api;

use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserBalanceInvoice;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class UserInvoices extends ApiResult
{

  private $userId;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }

    $invoices = UserBalanceInvoice::find()->where(['user_id' => $this->userId])->orderBy(['created_at' => SORT_DESC]);

    $this->setDataProvider(new ActiveDataProvider([
      'query' => $invoices,
      'sort' => false
    ]));

    $this->setResultTypeDataProvider();
  }
}