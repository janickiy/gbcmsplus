<?php

namespace mcms\payments\components\rbac;


use Yii;
use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

class CanUseMultipleCurrenciesBalance extends Rule
{
  public $name = 'PaymentsCanUseMultipleCurrenciesBalance';
  public $description = 'Can have or view multiple currencies';

  /**
   * @inheritDoc
   */
  public function execute($user, $item, $params)
  {
    $userId = ArrayHelper::getValue($params, ['model', 'user_id']);

    if (array_key_exists('reseller', Yii::$app->authManager->getRolesByUser($userId))) {
      return true;
    }
    return false;
  }

}