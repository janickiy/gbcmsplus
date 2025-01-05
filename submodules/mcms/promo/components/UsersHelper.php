<?php


namespace mcms\promo\components;

use Yii;
use mcms\common\helpers\ArrayHelper;

/**
 * Class UsersHelper
 * @package mcms\promo\components
 */
class UsersHelper
{
   const SELECT2_LIMIT = 10;
  /**
   * По массиву пользователя из API возвращает отформатированную строку вида:
   * "#1 - username: user@test.email"
   *
   * @param $item
   * @return string
   */
  static public function userRowFormat($item)
  {
    return sprintf(
      '#%s - %s: %s',
      ArrayHelper::getValue($item, 'id'),
      ArrayHelper::getValue($item, 'username'),
      ArrayHelper::getValue($item, 'email')
    );
  }

  /**
   * @param $q
   * @return array
   */
  static public function select2Users($q)
  {

    $dataProvider = Yii::$app->getModule('users')->api('user')->search([
      ['like', 'username', $q],
      ['like', 'email', $q]
    ], false, self::SELECT2_LIMIT, true);
    $dataProvider->getPagination()->setPageSize(self::SELECT2_LIMIT);

    $items = array_map(function ($item) {
      return [
        'text' => UsersHelper::userRowFormat($item),
        'id' => ArrayHelper::getValue($item, 'id')
      ];
    }, $dataProvider->getModels());

    return ['results' => $items];
  }

  /**
   * Получение строки пользователя по id
   * Форматирование строки методом [[userRowFormat]].
   *
   * @param $id
   * @return null|string
   */
  static public function getUserString($id)
  {
    $users = Yii::$app->getModule('users')->api('user')->search([
      ['=', 'id', $id]
    ]);
    $user = ArrayHelper::getValue($users, 0, false);

    return $user ? self::userRowFormat($user) : null;
  }

  /**
   * @param $userId
   * @return mixed
   */
  static public function getRolesByUserId($userId)
  {
    return Yii::$app->getModule('users')
      ->api('rolesByUserId', ['userId' => $userId])
      ->setResultTypeMap()
      ->setMapParams(['name', 'name'])
      ->getResult();
  }

  static public function getUsersByRoles(array $roles)
  {
    return Yii::$app->getModule('users')
    ->api('usersByRoles', $roles)
    ->getResult();
  }


  static public function getCurrentUserNotAvailableUsers()
  {
    return Yii::$app->getModule('users')
      ->api('notAvailableUserIds', [
        'userId' => Yii::$app->user->id,
        'skipCurrentUser' => true
      ])->getResult();
  }

}