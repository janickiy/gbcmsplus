<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 8/27/15
 * Time: 4:45 PM
 */

namespace mcms\notifications\models;


use kartik\builder\Form;
use mcms\modmanager\models\Module;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;

class NotificationForm extends Model
{

  private $_model;
  private $_isModelNew;
  private $_events;
  private $_moduleId;
  public $id;
  public $event;
  public $roles;
  public $emails;
  public $from;
  public $header;
  public $template;
  public $notification_type;
  public $use_owner;
  public $is_important;

  protected $formAttributes;

  public function __construct(Notification $model, array $events, $moduleId, $config = [])
  {
    parent::__construct();
    $this->_model = $model;
    $this->_isModelNew = $model->id === null;
    $this->_model->scenario = $this->isModelNew() ? 'create' : 'edit';
    $this->_model->module_id = $model->id;
    $this->_events = $events;
    $this->_moduleId = $moduleId;
    $this->_model->formAttributes['event']['items'] = $this->_events;
    $this->is_important = $model->is_important;
    $this->id = $model->id;
    $this->event = $model->event;
    $this->emails = $model->emails;
    $this->roles = $model->roles;
    $this->use_owner = $model->use_owner;
    $this->from = $model->from;
  }

  public function load($data, $formName = null)
  {
    $data[$this->_model->formName()] = ArrayHelper::getValue($data, $this->formName());
    return $this->_model->load($data) && parent::load($data, $formName);
  }

  public function rules()
  {
    return array_merge(parent::rules(), [
      ['event', 'required'],
      ['event', function($attribute, $params) {
        return !array_key_exists($this->$attribute, $this->_events);
      }],
      ['roles', function($attribute, $params) {
        return !in_array($this->$attribute, $this->_getRoles());
      }],
      ['roles', 'required', 'when' => function($model) {
        return strlen($model->emails) == 0;
      }],
      ['emails', 'required', 'when' => function($model) {
        return count($model->roles) == 0;
      }, 'whenClient' => "function(attribute, value) {
        return count($('#' + attribute.id).val()) == 0;
      }"]
    ]);
  }

  private function isModelNew()
  {
    return $this->_isModelNew;
  }


  public function getFormAttributes()
  {
    $formAttributes = $this->_model->getFormAttributes();
    $formAttributes['is_important'] = [
      'type' => Form::INPUT_CHECKBOX,
      'label' => Yii::_t('labels.notification_creation_isImportant'),
    ];

    return $formAttributes;
  }

  public function beforeValidate()
  {

    $this->emails = array_map(function($email) {
      return trim($email);
    }, explode(',', $this->emails));

    $emailValidator = new EmailValidator();
    $this->emails = array_filter($this->emails, function($email) use ($emailValidator) {
      return $emailValidator->validate($email);
    });

    $this->emails = implode(',', $this->emails);

    return parent::beforeValidate();
  }


  public function saveFormData()
  {
    $this->_model->module_id = Module::findOne(['module_id' => $this->_moduleId])->id;
    $this->_model->emails = $this->emails;
    return $this->_model->save();
  }

  /**
   * @return Notification
   */
  public function getModel()
  {
    return $this->_model;
  }

  private function _getRoles()
  {
    return Yii::$app->getModule('users')
      ->api('roles', ['withOwner' => true, 'removeGuest'])
      ->getResult()
      ;
  }

  public function getMultilangAttributes()
  {
    return $this->_model->getMultilangAttributes();
  }

  public function setAttribute($name, $value)
  {
    return $this->_model->setAttribute($name, $value);
  }

}