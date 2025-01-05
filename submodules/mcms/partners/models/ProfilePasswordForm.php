<?php

namespace mcms\partners\models;

use Yii;
use yii\base\Model;
use kartik\builder\Form;

class ProfilePasswordForm extends Model
{

  public $password;
  public $password_repeat;

  public function getFormAttributes()
  {
    return [
      'password' => [
        'type' => Form::INPUT_PASSWORD,
        'label' => Yii::_t('partners.profile.change_password_password')
      ],
      'password_repeat' => [
        'type' => Form::INPUT_PASSWORD,
        'label' => Yii::_t('partners.profile.change_password_password_confirmation'),
      ],
    ];
  }

  public function rules()
  {
    $validationRules = [
      [['password', 'password_repeat'], 'required'],
      [['password', 'password_repeat'], 'string', 'min' => 6],
      ['password_repeat', 'compare', 'compareAttribute' => 'password'],
    ];

    return $validationRules;
  }

    /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'password' => Yii::_t('partners.profile.change_password_password'),
      'password_repeat' => Yii::_t('partners.profile.change_password_password_confirmation'),

    ];
  }

}