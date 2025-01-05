<?php

namespace mcms\payments\components;

use mcms\payments\models\wallet\Wallet;
use rgk\utils\traits\ModelPerActionTrait;
use yii\base\Model;

/**
 * Class WalletEnable
 * @package mcms\payments\components
 *
 * @property Wallet $model
 */
class WalletEnable extends Model
{

  use ModelPerActionTrait;

  /**
   * @inheritdoc
   */
  protected function executeInternal()
  {
    $this->model->is_active = 1;
    return $this->model->save();
  }

  /**
   * @inheritdoc
   */
  protected function getModelClass()
  {
    return Wallet::class;
  }
}