<?php

namespace mcms\notifications\models;


use mcms\user\models\UserInvitation;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class EmailInvitation
 * @package mcms\notifications\models
 *
 * @property int $id [int(10) unsigned]
 * @property string $from [varchar(255)]
 * @property string $header [varchar(255)]
 * @property string $template
 * @property bool $is_complete [tinyint(1) unsigned]
 * @property int $created_at [int(10) unsigned]
 * @property int $updated_at [int(10) unsigned]
 */
class UserInvitationEmail extends ActiveRecord
{
  /**
   * @return string
   */
  public static function tableName()
  {
    return 'users_invitations_emails';
  }

  public static function dropdownList()
  {
    return self::find()
      ->select('header')
      ->indexBy('id')
      ->column();
  }

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['is_complete'], 'integer'],
      [['from', 'header', 'template'], 'string'],
      [['from', 'header'], 'required'],
    ];
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('notifications.invitations.attribute-id'),
      'from' => Yii::_t('notifications.invitations.attribute-from'),
      'header' => Yii::_t('notifications.invitations.attribute-header'),
      'template' => Yii::_t('notifications.invitations.attribute-template'),
      'is_complete' => Yii::_t('notifications.invitations.attribute-is_complete'),
      'created_at' => Yii::_t('notifications.invitations.attribute-created_at'),
      'updated_at' => Yii::_t('notifications.invitations.attribute-updated_at'),
    ];
  }

  /**
   * @return string
   */
  public function getStringInfo()
  {
    return sprintf('%s - %s', $this->id, $this->header);
  }

  /**
   * @return array
   */
  public function getReplacements()
  {
    return [
      '{username}' => 'username',
      '{password}' => 'password',
      '{link}' => 'link',
    ];
  }

  /**
   * @return array
   */
  public function getReplacementsHelp()
  {
    return [
      '{username}' => Yii::_t('notifications.invitations.username'),
      '{password}' => Yii::_t('notifications.invitations.password'),
      '{link}' => Yii::_t('notifications.invitations.signup-link'),
    ];
  }

  /**
   * @param UserInvitation $invitation
   * @return string
   */
  public function replaceHeader($invitation)
  {
    $replacements = $this->getReplacements();

    foreach ($replacements as &$replacement) {
      $replacement = $invitation->$replacement;
    }

    return strtr($this->header, $replacements);
  }

  /**
   * @param UserInvitation $invitation
   * @return string
   */
  public function replaceMessage($invitation)
  {
    $replacements = $this->getReplacements();

    foreach ($replacements as &$replacement) {
      $replacement = $invitation->$replacement;
    }

    return strtr($this->template, $replacements);
  }
}