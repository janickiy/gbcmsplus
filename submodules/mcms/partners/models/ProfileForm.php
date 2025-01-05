<?php

namespace mcms\partners\models;

use mcms\common\SystemLanguage;
use mcms\user\models\User;
use mcms\user\models\UserContact;
use Yii;
use yii\base\Model;
use vova07\fileapi\behaviors\UploadBehavior;

class ProfileForm extends Model
{
  /**
   * @var User
   */
  public $_model;
  public $email;
  public $language;
  public $topname;
  public $color;
  public $oldPassword;
  public $newPassword;
  public $contacts;

  /**
   * @var UserContact[]
   */
  public $contactModels = false;

  public function rules()
  {
    return [
      ['oldPassword', 'required', 'when' => function () {
        return $this->newPassword != "";
      }, 'whenClient' => "function (attribute, value) {
        return $('#profileform-newpassword').val() != '';
      }"],
      [['topname'], 'string', 'max' => 255],
      ['color', 'string', 'max' => 10],
      ['language', 'in', 'range' => array_keys(SystemLanguage::getLanguangesDropDownArray())],
      [['oldPassword', 'newPassword'], 'string', 'min' => 6],
      ['contacts', 'validateContacts'],
    ];
  }

  public function __construct($model, $config = [])
  {
    $this->_model = $model;

    $userAttributes = $model->activeAttributes();
    $this->setAttributes($this->_model->getAttributes(), false);

    foreach ($userAttributes as $fieldName) {
      if ($this->hasProperty($fieldName)) {
        $config[$fieldName] = $this->_model->getAttribute($fieldName);
      }
    }

    parent::__construct($config);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'email' => Yii::_t('profile.user_email'),
      'language' => Yii::_t('profile.user_language'),
      'color' => Yii::_t('profile.color_theme'),
      'topname' => Yii::_t('profile.topname'),
      'oldPassword' => Yii::_t('profile.old_password'),
      'newPassword' => Yii::_t('profile.new_password'),
    ];
  }

  /**
   * @return UserContact[]
   */
  public function getContactModels()
  {
    if ($this->contactModels === false) {
      $this->contactModels = $this->_model->getActiveContacts()->indexBy('id')->all();
    }

    return $this->contactModels;
  }

  /**
   *
   */
  public function validateContacts()
  {
    // удаляем модели контактов, чтобы сбилдить заново (вместе с новыми)
    $this->contactModels = [];

    foreach ($this->contacts as $id => $contact) {
      $model = $this->fetchContact($id);
      if (!$model) {
        continue;
      }
      if ($model->id) {
        $this->contactModels[$model->id] = $model;
      } else {
        $this->contactModels[] = $model; // новый контакт
      }

      $model->setAttributes($contact);
      if (!$model->validate()) {
        foreach ($model->getErrors() as $field => $error) {
          $this->addError("contacts[{$model->id}][$field]value", reset($error));
        }
      }
    }
  }

  /**
   * @param $id
   * @return UserContact
   */
  protected function fetchContact($id)
  {
    $model = is_numeric($id)
      ? UserContact::findOne(['id' => $id, 'user_id' => $this->_model->id])
      : new UserContact(['user_id' => $this->_model->id]);

    $model->setScenario(UserContact::SCENARIO_PARTNER);

    return $model;
  }
}