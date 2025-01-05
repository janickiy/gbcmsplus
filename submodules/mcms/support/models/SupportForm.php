<?php

namespace mcms\support\models;

use Yii;
use yii\base\Model;
use mcms\support\components\events\EventAdminCreated;

class SupportForm extends Model
{

  public $support_category_id;
  public $delegated_to;
  public $question;
  public $created_by;
  public $name;

  protected $model;

  public function __construct(Support $support, $config = [])
  {
    $this->model = $support;

    $this->model->scenario = $support->id === null
      ? $support::SCENARIO_CREATE
      : $support::SCENARIO_EDIT
      ;

    $this->support_category_id = $support->getAttribute('support_category_id');

    parent::__construct($config);
  }

  public function rules()
  {
    return [
      [['name', 'question', 'support_category_id', 'created_by'], 'required'],
    ];
  }

  public function saveSupport()
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      $supportCategory = SupportCategory::find()
        ->where(['id' => $this->support_category_id])
        ->one();

      $this->model->name = $this->name;
      $this->model->support_category_id = $supportCategory->id;
      $this->model->created_by = $this->created_by;
      $this->model->has_unread_messages = 1;
      $this->model->is_opened = 1;
      $this->model->save();

      $supportText = new SupportText();
      $supportText->text = $this->question;
      $supportText->from_user_id = Yii::$app->user->id;
      $supportText->support_id = $this->model->id;
      $saveResult = $supportText->save();
      $transaction->commit();
      (new EventAdminCreated($this->model))->trigger();
      return $saveResult;
    } catch (\Exception $e) {
      $transaction->rollBack();
      throw $e;
    }
  }

  public function getModel()
  {
    return $this->model;
  }

  public function attributeLabels()
  {
    return array_merge(parent::attributeLabels(), [
      'created_by' => Yii::_t('labels.support_from_user_id'),
      'name' =>  Yii::_t('labels.support_name'),
      'question' => Yii::_t('labels.support_question'),
      'support_category_id' => Yii::_t('labels.support_support_category_id'),
    ]);
  }
}