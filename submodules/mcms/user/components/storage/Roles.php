<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 9/1/15
 * Time: 6:01 PM
 */

namespace mcms\user\components\storage;
use mcms\user\Module;
use yii\helpers\ArrayHelper;

/**
 * Class Roles
 * @package mcms\user\components\storage
 *
 * @property string $auth_item_name
 * @property int $notification_id
 * @deprecated
 */
class Roles implements RolesInterface
{
  public function getRoles($includeOwner = true)
  {
    $roles = [];

    return ArrayHelper::merge(
      $roles,
      (new \mdm\admin\models\searchs\AuthItem(['type' => \yii\rbac\Item::TYPE_ROLE]))
      ->search(\Yii::$app->request->getQueryParams())
      ->getModels()
    );
  }

  public function getOwnerRole()
  {
    return Module::OWNER_ROLE;
  }
}