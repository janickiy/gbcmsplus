<?php
namespace mcms\holds\models;

use Yii;

/**
 * Форма для добавления партнера к правилам холдов
 */
class LinkPartnerForm extends \yii\base\Model
{
  public $userId;

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['userId'], 'required'],
      [['userId'], 'integer'],
    ];
  }

  /**
   * Назначить партнеру программу холдов
   * @param $holdProgramId
   * @return bool
   */
  public function setHoldProgramId($holdProgramId)
  {
    if (!$this->validate()) {
      return false;
    }
    return Yii::$app->getModule('payments')->api('userSettingsData', [
      'userId' => $this->userId
    ])->setHoldProgramId($holdProgramId);
  }

  /**
   * Удалить у партнера программу холдов
   * @return bool
   */
  public function unsetHoldProgramId()
  {
    if (!$this->validate()) {
      return false;
    }
    return Yii::$app->getModule('payments')->api('userSettingsData', [
      'userId' => $this->userId
    ])->unsetHoldProgramId();
  }
}
