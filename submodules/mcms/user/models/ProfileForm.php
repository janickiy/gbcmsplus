<?php

namespace mcms\user\models;

use mcms\common\SystemLanguage;
use Yii;
use yii\base\Model;

class ProfileForm extends Model
{
    /**
     * @var \mcms\user\models\User
     */
    public $_model;
    public $email;
    public $username;
    public $topname;
    public $status;
    public $skype;
    public $language;
    public $phone;
    public $notify_email;
    public $oldPassword;
    public $newPassword;
    /** @var integer */
    public $grid_page_size;

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

    public function rules()
    {
        return [
            ['oldPassword', 'required', 'when' => function () {
                return $this->newPassword != "";
            }, 'whenClient' => "function (attribute, value) {
        return $('#profileform-newpassword').val() != '';
      }"],
            ['grid_page_size', 'integer'],
            [['topname', 'skype'], 'string', 'max' => 255],
            ['phone', 'string', 'max' => 20],
            ['language', 'in', 'range' => array_keys(SystemLanguage::getLanguangesDropDownArray())],
            [['oldPassword', 'newPassword'], 'string', 'min' => 6],
            [['oldPassword'], function ($attribute, $params) {
                if (!empty($this->$attribute) && !$this->_model->validatePassword($this->$attribute)) {
                    $this->addError($attribute, Yii::_t('users.forms.wrong_password'));
                }
            }],
            ['email', 'email'],
            ['email', function ($attribute, $params) {
                if (!Yii::$app->getModule('users')->canChangeEmail()) {
                    $this->addError($attribute, Yii::_t('users.forms.wrong_email'));
                }
            }, 'when' => function () {
                return $this->email != $this->_model->email;
            }],
            ['notify_email', 'filter', 'filter' => function ($value) {
                return $this->email;
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::_t('profile.user_email'),
            'username' => Yii::_t('profile.username'),
            'topname' => Yii::_t('profile.topname'),
            'skype' => Yii::_t('profile.user_skype'),
            'language' => Yii::_t('profile.user_language'),
            'phone' => Yii::_t('profile.phone'),
            'status' => Yii::_t('profile.status'),
            'oldPassword' => Yii::_t('profile.old_password'),
            'newPassword' => Yii::_t('profile.new_password'),
            'grid_page_size' => Yii::_t('forms.grid_page_size'),
        ];
    }

}