<?php

namespace mcms\partners\models;

use mcms\common\validators\UrlValidator;
use yii\base\Model;

class LinkStep3Form extends Model
{
  public $id;
  public $postback_url;
  public $trafficback_url;
  public $trafficback_type;
  public $subid1;
  public $subid2;
  public $cid;
  public $cid_value;
  public $is_notify_subscribe;
  public $is_notify_rebill;
  public $is_notify_unsubscribe;
  public $is_notify_cpa;
  public $use_global_postback_url;
  public $use_complains_global_postback_url;
  public $ads_network_id;
  public $is_trafficback_sell;
  public $send_all_get_params_to_pb;
  public $erid;
  public $adv_network;
  public $adv_site_id;
  public $adv_site_domain;
  const TRAFFICBACK_TYPE_STATIC = 1;

  public function init()
  {
    $this->trafficback_type = self::TRAFFICBACK_TYPE_STATIC;
  }

  public function rules()
  {
    return [
      ['trafficback_url', 'url', 'when' => function ($model) {
        return $model->trafficback_type == self::TRAFFICBACK_TYPE_STATIC;
      }, 'whenClient' => "function (attribute, value) {
        return $('.trafficback:checked').val() == '" . self::TRAFFICBACK_TYPE_STATIC . "';
      }"],
      [['postback_url', 'trafficback_url'], UrlValidator::class, 'enableIDN' => true],
      [['ads_network_id'], 'integer'],
      [['use_global_postback_url', 'use_complains_global_postback_url'], 'safe'],
      [['subid1', 'subid2', 'cid'], 'filter', 'filter' => 'trim'],
      [['subid1', 'subid2', 'cid_value'], 'match', 'pattern' => '/^[\#)(\$A-z0-9-_{}]+$/'],
      [['cid'], 'match', 'pattern' => '/^[A-z0-9-_]+$/'],
    ];
  }
}