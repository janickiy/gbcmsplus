<?php

namespace mcms\partners\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class SourceForm extends Model
{
  public $id;
  public $url;
  public $domain_id;
  public $default_profit_type;
  public $filter_operators;
  public $ads_type;
  public $stepNumber;

  const PROFIT_TYPE_BUYOUT = 2;

  public function init()
  {
    $this->default_profit_type = self::PROFIT_TYPE_BUYOUT;
  }


  public function rules()
  {
    return [
      ['url', 'required'],
      ['domain_id', 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'url' => Yii::_t('partners.sources.url'),
    ];
  }

  public function getStepAttributes()
  {
    $attributes = [
      1 => ['url', 'default_profit_type'],
      2 => [],
      3 => ['ads_type', 'filter_operators']
    ];

    return ArrayHelper::getValue($attributes, $this->stepNumber, []);
  }


}