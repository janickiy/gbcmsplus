<?php

namespace mcms\partners\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class TicketForm extends Model
{
  public $support_category_id;
  public $name;
  public $text;
  public $files;
  public $images;


  public function rules()
  {
    return [
      [['name', 'text', 'support_category_id'], 'required'],
      ['images', 'string'],
      ['files', 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'name' => Yii::_t('partners.support.support-ticket_name'),
      'text' => Yii::_t('partners.support.support-ticket_question'),
      'support_category_id' => Yii::_t('partners.support.support-ticket_category'),
      'images' => Yii::_t('partners.support.support-ticket_images')
    ];
  }

}