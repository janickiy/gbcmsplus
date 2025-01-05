<?php

namespace mcms\loyalty\components;

use mcms\loyalty\models\LoyaltyBonus;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Компонент для синхронизации бонусов реселлера
 */
class LoyaltyBonusImport extends Object
{
  /** @var array */
  public $mgmpBonus;

  private $_errors = [];

  /**
   * Синхронизация данных
   * @return bool
   * @throws \yii\base\InvalidParamException
   */
  public function execute()
  {
    if (empty($this->mgmpBonus['id'])) {
      throw new InvalidParamException('Неверный формат параметра mgmpBonus');
    }

    $model = LoyaltyBonus::findOne(['external_id' => (int)$this->mgmpBonus['id']]);
    if (!$model) $model = new LoyaltyBonus;

    $model->external_id = ArrayHelper::getValue($this->mgmpBonus, 'id');
    $model->external_invoice_id = ArrayHelper::getValue($this->mgmpBonus, 'invoice_id');
    $model->amount_usd = ArrayHelper::getValue($this->mgmpBonus, 'amount_usd');
    $model->comment = ArrayHelper::getValue($this->mgmpBonus, 'comment');
    $model->type = ArrayHelper::getValue($this->mgmpBonus, 'type');
    $model->details_json = ArrayHelper::getValue($this->mgmpBonus, 'details_json');
    $model->decline_reason = ArrayHelper::getValue($this->mgmpBonus, 'decline_reason');
    $model->status = ArrayHelper::getValue($this->mgmpBonus, 'status');
    $model->created_at = ArrayHelper::getValue($this->mgmpBonus, 'created_at');
    $model->updated_at = ArrayHelper::getValue($this->mgmpBonus, 'updated_at');

    $result = $model->save();
    if (!$result) {
      Yii::error('Cannot save loyalty bonus! Data: ' . Json::encode($model->toArray())
        . '. Errors: ' . Json::encode($model->getErrors()), __METHOD__);
      $this->_errors = $model->getErrors();
    }

    return $result;
  }

  /**
   * Ошибки валидации
   * @return array
   */
  public function getErrors()
  {
    return $this->_errors;
  }
}