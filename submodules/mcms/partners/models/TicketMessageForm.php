<?php

namespace mcms\partners\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class TicketMessageForm extends Model
{
  private $model;
  public $text;
  public $ticketId;
  public $images;
  public $files;


  public function rules()
  {
    return [
      [['text', 'ticketId'], 'required'],
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
      'text' => Yii::_t('partners.support.ticket_message_text'),
      'images' => Yii::_t('partners.support.ticket_images')
    ];
  }

}