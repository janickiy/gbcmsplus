<?php

namespace mcms\payments\commands;


use mcms\payments\components\UserBalance;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Баланс пользователей
 * Class BalanceController
 * @package mcms\payments\commands
 */
class BalanceController extends Controller
{
  private $moduleUser = null;
  public $onlyNegative = false;

  /**
   * @inheritDoc
   */
  public function init()
  {
    parent::init();
    $this->moduleUser = Yii::$app->getModule('users');
  }

  /**
   * [userId, --onlyNegative]
   *
   * @param null $id
   * @return int
   */
  public function actionIndex($id = null)
  {
    if ($id && !$this->moduleUser->api('getOneUser', ['user_id' => $id])->getResult()) {
      $this->stdout('User not found' . PHP_EOL, Console::FG_RED);
      return Controller::EXIT_CODE_ERROR;
    }

    $userIdLis = $id ? [$id] : $this->getUserIdList();
    foreach ($userIdLis as $id) {
      $balanceComponent = new UserBalance(['userId' => $id, 'showLog' => false]);
      $main = $balanceComponent->getMain(false);
      $hold = $balanceComponent->getHold(false);
      if ($this->onlyNegative && floatval($main) >= 0 && floatval($hold) >= 0) {
        continue;
      }
      $this->stdout('user: ' . $id . ' (' .  $balanceComponent->getCurrency() . ') ' . PHP_EOL);
      $this->printBalance(['main' => $main, 'hold' => $hold]);
    }

    return Controller::EXIT_CODE_NORMAL;
  }

  /**
   * @return mixed
   */
  private function getUserIdList()
  {
    $moduleUser = $this->moduleUser;
    return $moduleUser
      ->api('usersByRoles', ['pagination' => false, $moduleUser::PARTNER_ROLE])
      ->setResultTypeMap()
      ->setMapParams(['id', 'id'])
      ->getResult()
    ;
  }

  private function printBalance($data)
  {
    foreach ($data as $type => $amount) {
      $amount = floatval($amount);
      $this->stdout(' ' . $type . ' ');
      $this->stdout($amount . PHP_EOL,
        $amount == 0 ? Console::FG_YELLOW : (
          $amount > 0 ? Console::FG_GREEN : Console::FG_RED
        )
      );
    }
  }

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return array_merge(parent::options($actionID), ['reset', 'onlyNegative']);
  }

}