<?php

namespace mcms\partners\models;

use yii\base\Model;

class DomainForm extends Model
{
  public $type;
  public $url;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['type', 'url'], 'required'],
      ['type', 'integer'],
      [['url'], 'string', 'max' => 255],
    ];
  }
}