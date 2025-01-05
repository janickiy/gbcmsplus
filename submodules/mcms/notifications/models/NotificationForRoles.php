<?php

namespace mcms\notifications\models;


use mcms\common\event\Event;
use yii\behaviors\TimestampBehavior;
use mcms\common\multilang\MultiLangModel;

/**
 * Class NotificationForRoles
 * @package mcms\notifications\models
 *
 * @property int $id
 * @property int $module_id
 * @property int $user_id
 * @property string|string[] $roles Роли через запятую или массив ролей. Преобразуется в beforeSave и afterFind
 * @property string $from
 * @property string $header
 * @property string $template
 * @property string $notification_type
 * @property string $is_important
 * @property string $is_system
 * @property string $is_news
 * @property string $event
 * @property string $emails
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $emails_language
 * @property integer $is_replace
 * @property integer $event_instance
 * @property integer $is_send
 */
class NotificationForRoles extends MultiLangModel
{
  const ROLES_GLUE = ',';

  public function getMultilangAttributes()
  {
    return ['from', 'header', 'template'];
  }

  public static function tableName()
  {
    return '{{%notifications_for_roles}}';
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  public function rules()
  {
    return array_merge(
      parent::rules(), [
        [['is_replace', 'is_send'], 'boolean'],
        [['module_id', 'user_id'], 'integer'],
        ['emails_language', 'string'],
        [['roles', 'header', 'from', 'template', 'event', 'notification_type'], 'required'],
        [['from', 'header', 'template'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
        [['from', 'header', 'template'], 'default', 'value' => ''],
        [['is_important', 'is_system', 'is_news', 'is_replace'], 'boolean'],
      ]
    );
  }

  /**
   * @return Event
   */
  public function getEventObjectInstance()
  {
    return @unserialize($this->event_instance);
  }

  /**
   * @param bool $insert
   * @return bool
   */
  public function beforeSave($insert)
  {
    $this->roles = implode(self::ROLES_GLUE, $this->roles);

    return parent::beforeSave($insert);
  }


  public function afterFind()
  {
    $this->roles = explode(self::ROLES_GLUE, $this->roles);

    parent::afterFind();
  }

}