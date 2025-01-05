<?php
namespace mcms\partners\models;

use yii\base\Model;

class LinkStep2Form extends Model
{
  public $id;
  public $linkOperatorLandings;

  public function rules()
  {
    return [
      ['linkOperatorLandings', 'safe']
    ];
  }
}