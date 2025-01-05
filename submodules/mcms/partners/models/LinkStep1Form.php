<?php

namespace mcms\partners\models;

use yii\base\Model;

class LinkStep1Form extends Model
{
  public $id;
  public $name;
  public $stream_id;
  public $streamName;
  public $isNewStream = false;
  public $domain_id;

  public function rules()
  {
    return [
      [['name', 'domain_id', 'stream_id'], 'required'],
      [['streamName'], 'string'],
    ];
  }
}