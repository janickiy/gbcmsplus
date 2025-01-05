<?php

namespace mcms\partners\models;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\Model;

class LinkForm extends Model
{
  public $operator_id;
  public $stream_id;
  public $domain_id;
  public $ads_network_id;
  public $name;
  public $id;

  public $trafficback_type;
  public $trafficback_url;
  public $is_trafficback_sell;
  public $is_notify_subscribe;
  public $is_notify_rebill;
  public $is_notify_unsubscribe;
  public $is_notify_cpa;
  public $postback_url;
  public $complains_postback_url;
  public $use_global_postback_url;
  public $use_complains_global_postback_url;
  public $landing = [];

  public $stepNumber;
  public $streamName;
  public $isNewStream = false;
  public $linkOperatorLandings;

  const TRAFFICBACK_TYPE_STATIC = 1;
  const STEP_ONE = 1;
  const STEP_TWO = 2;
  const STEP_THREE = 3;
  const STEP_FOUR = 4;

  public function init()
  {
    $this->trafficback_type = self::TRAFFICBACK_TYPE_STATIC;
  }

  public function rules()
  {
    return [
      [['name', 'domain_id', 'stream_id'], 'required'],
      [['streamName'], 'string'],
      ['trafficback_url', 'url', 'when' => function ($model) {
        return $model->trafficback_type == self::TRAFFICBACK_TYPE_STATIC;
      }, 'whenClient' => "function (attribute, value) {
        return $('.trafficback:checked').val() == '" . self::TRAFFICBACK_TYPE_STATIC . "';
      }"],
      [['postback_url', 'complains_postback_url'], 'url'],
      [['ads_network_id'], 'integer'],
      [['linkOperatorLandings', 'use_global_postback_url', 'use_complains_global_postback_url'], 'safe']
    ];
  }

  public function getStepAttributes()
  {
    $attributes = [
      self::STEP_ONE => ['name', 'stream_id', 'domain_id', 'streamName', 'isNewStream'],
      self::STEP_TWO => ['linkOperatorLandings'],
      self::STEP_THREE => [
        'postback_url',
        'trafficback_url',
        'is_notify_subscribe',
        'is_notify_rebill',
        'is_notify_unsubscribe',
        'is_notify_cpa',
        'use_global_postback_url',
        'use_complains_global_postback_url',
        'trafficback_type',
        'ads_network_id'
      ],
      self::STEP_FOUR => [
        'postback_url',
      ]
    ];

    return ArrayHelper::getValue($attributes, $this->stepNumber, []);
  }
}