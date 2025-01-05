<?php

namespace mcms\partners\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\user\components\api\User;
use mcms\user\Module;
use Yii;

/**
 * Управление доступом к просмотру и редактированию детальной информации о кошельке в партнерке.
 * Сделано что бы люди не знающие пароль не могли изменить реквизиты кошельков, а так же для скрытия реквизитов, например
 * если партнер забыл разлогинится с чужого ПК
 */
class UserWalletsManageAccess extends ApiResult
{
  /** @const string Ключ для хранения флага обозначающего наличие доступа */
  const STORAGE_USER_WALLETS_ACCESS = 'user_wallets_manager_access';

  /** @var \mcms\user\models\User $user Юзверь */
  private $user;

  /**
   * @inheritdoc
   */
  public function init($params = [])
  {
    $this->user = Yii::$app->user->identity;
  }

  /**
   * Проверка наличия доступа
   * @return bool
   */
  public function hasAccess()
  {
    return (bool)Yii::$app->session->get(self::STORAGE_USER_WALLETS_ACCESS, false);
  }

  /**
   * Проверить и установить доступ по паролю
   * @param string $password
   * @return bool
   */
  public function provideAccess($password)
  {
    return $password && $this->user->validatePassword($password) && $this->setAccessTrue();
  }

  /**
   * Отменить доступ
   */
  public function denyAccess()
  {
    Yii::$app->session->set(self::STORAGE_USER_WALLETS_ACCESS, false);
  }

  /**
   * Установить доступ
   * @return bool
   */
  private function setAccessTrue()
  {
    Yii::$app->session->set(self::STORAGE_USER_WALLETS_ACCESS, true);

    return true;
  }
}