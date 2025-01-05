<?php

namespace mcms\promo\models;

use mcms\common\validators\LocalhostUrlValidator;
use mcms\promo\components\PrelandDefaultsSync;
use mcms\user\models\User;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * Class SmartLink
 * @package mcms\promo\models
 */
class SmartLink extends Source
{

  const DEFAULT_NAME = 'Smart link';
  const HASH_LENGHT = 9;
  const SCENARIO_ADMIN_UPDATE_SMART_LINK = 'admin_update_smart_link';

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['url', 'postback_url', 'trafficback_url'], 'filter', 'filter' => function ($value) {
        return str_replace(['"', "'"], '', $value);
      }],
      ['postback_url', 'required', 'when' => function ($attribute, $params) {
        if ($this->use_global_postback_url) return false;
        return !empty($this->is_notify_subscribe) ||
          !empty($this->is_notify_unsubscribe) ||
          !empty($this->is_notify_rebill) ||
          !empty($this->is_notify_cpa);
      }, 'enableClientValidation' => false], //TRICKY При условной валидации надо отключать клиентскую, иначе на клиенте поле всегда будет обязательным
      ['postback_url', 'chooseSubject', 'on' => self::SCENARIO_PARTNER_TEST_POSTBACK_URL],
      ['postback_url', LocalhostUrlValidator::class],
      ['postback_url', 'required', 'on' => self::SCENARIO_PARTNER_TEST_POSTBACK_URL],
      [['addPrelandOperatorIds', 'offPrelandOperatorIds', 'blockedOperatorIds'], 'each', 'rule' => ['integer']],
      [['addPrelandOperatorIds', 'offPrelandOperatorIds', 'blockedOperatorIds', 'operator_blocked_reason'], 'safe'],
      [['domain_id', 'status'], 'required'],
      [['stream_id'], 'checkStream', 'skipOnEmpty' => false, 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE],
      [['streamName', 'isNewStream', 'is_trafficback_sell'], 'safe', 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE],
      ['streamName', 'unique', 'targetClass' => Stream::class, 'targetAttribute' => [
        'streamName' => 'name',
        'user_id' => 'user_id',
      ], 'when' => function (Source $model) {
        return $model->isNewStream;
      }, 'message' => Yii::_t('promo.streams.unique'), 'on' => self::SCENARIO_PARTNER_CREATE_ARBITRARY_SOURCE
      ],
      ['name', 'required'],
      ['user_id', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['hash', 'user_id'], 'required'],
      [['user_id', 'default_profit_type', 'ads_type', 'status', 'source_type', 'stream_id', 'domain_id', 'is_notify_subscribe', 'is_notify_rebill', 'is_notify_unsubscribe', 'is_notify_cpa', 'trafficback_type', 'is_trafficback_sell', 'allow_all_url'], 'integer'],
      [['url', 'name', 'trafficback_url', 'label1', 'label2', 'subid1', 'subid2', 'cid', 'cid_value'], 'string', 'max' => 255],
      [['label1', 'label2', 'subid1', 'subid2', 'cid', 'cid_value'], 'trim'],
      [['postback_url', 'trafficback_url'], 'url'],
      [['hash', 'url'], 'unique'],
      ['hash', 'string', 'max' => 9],
      ['url', 'url'],
      [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::class, 'targetAttribute' => ['domain_id' => 'id'],
        'filter' => ['and',
          ['=', 'status', Domain::STATUS_ACTIVE],
          ['or',
            ['=', 'is_system', true],
            ['=', 'user_id', $this->user_id],
          ]
        ],
        'message' => Yii::_t('promo.domains.select_active')
      ],
      [['stream_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stream::class, 'targetAttribute' => ['stream_id' => 'id'],
        'filter' => ['and',
          ['=', 'status', Stream::STATUS_ACTIVE],
          ['=', 'user_id', $this->user_id],
        ],
        'when' => function (Source $model) {
          return !($model->isNewStream);
        }
      ],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      [['is_traffic_filters_off'], 'default', 'value' => 0],
      ['is_traffic_filters_off', 'canManageTrafficFiltersOff', 'on' => [self::SCENARIO_ADMIN_UPDATE_ARBITRARY_SOURCE]],
      ['use_global_postback_url', 'isGlobalPostbackUrlExist'],
      ['use_complains_global_postback_url', 'isGlobalComplainsPostbackUrlExist'],
    ];
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_ADMIN_UPDATE_SMART_LINK => [
        'name',
        'status',
        'stream_id',
        'domain_id',
        'postback_url',
        'trafficback_url',
        'label1',
        'label2',
        'subid1',
        'subid2',
        'cid',
        'cid_value',
        'is_notify_subscribe',
        'is_notify_rebill',
        'is_notify_unsubscribe',
        'is_notify_cpa',
        'trafficback_type',
        'is_trafficback_sell',
        'addPrelandOperatorIds',
        'offPrelandOperatorIds',
        'blockedOperatorIds',
        'operator_blocked_reason',
        'reject_reason',
        'is_allow_force_operator',
        'addPrelandOperatorIds',
        'offPrelandOperatorIds',
        'blockedOperatorIds',
        'is_traffic_filters_off',
        'use_global_postback_url',
        'use_complains_global_postback_url',
      ],
    ]);
  }

  /**
   * @param $insert
   * @throws Exception
   */
  public function beforeSave($insert)
  {

    $this->createStreamIfNew();

    if ($this->trafficback_type == self::TRAFFICBACK_TYPE_DYNAMIC) {
      $this->trafficback_url = '';
    }

    if (is_array($this->filter_operators)) {
      $this->filter_operators = json_encode($this->filter_operators);
    }

    /**
     * Подставляем значения прелендов по-умолчанию
     * @var PrelandDefaults $defaultPrelands
     */
    if ($insert) {
      $defaultAddPrelands = $this->findDefaultPrelandOperators(PrelandDefaults::TYPE_ADD);
      $operators = [];
      foreach ($defaultAddPrelands->each() as $defaultPrelandAdd) {
        $operators = array_merge($operators, $defaultPrelandAdd->operators);
      }

      if ($defaultAddPrelands) {
        $this->addPrelandOperatorIds = $operators;
      }

      $defaultOffPrelands = $this->findDefaultPrelandOperators(PrelandDefaults::TYPE_ADD);
      $operators = [];
      foreach ($defaultOffPrelands->each() as $defaultPrelandAdd) {
        $operators = array_merge($operators, $defaultPrelandAdd->operators);
      }

      if ($defaultOffPrelands) {
        $this->offPrelandOperatorIds = $operators;
      }
    }

    return ActiveRecord::beforeSave($insert);
  }


  /**
   * @param bool $insert
   * @param array $changedAttributes
   * @throws Exception
   */
  public function afterSave($insert, $changedAttributes)
  {
    if ($insert) {
      $this->saveLinkAddPrelandOperators();
    }
    $this->saveLinkOffPrelandOperators($insert);
    $this->saveLinkBlockedOperators($insert);

    (new PrelandDefaultsSync([
      'type' => [PrelandDefaults::TYPE_ADD, PrelandDefaults::TYPE_OFF],
      'sourceId' => $this->id,
      'userId' => $this->user_id,
      'streamId' => $this->stream_id,
    ]))->run();

    $this->invalidateCache();

    return ActiveRecord::afterSave($insert, $changedAttributes);
  }

  private function createStreamIfNew()
  {
    $stream = Stream::findOne([
      'user_id' => $this->user_id,
      'status' => Stream::STATUS_ACTIVE
    ]);

    if ($stream) {
      $this->stream_id = $stream->id;
      return;
    }

    $stream = new Stream([
      'user_id' => $this->user_id,
      'name' => Stream::DEFAULT_NAME,
      'status' => Stream::STATUS_ACTIVE
    ]);

    if (!$stream->save()) {
      throw new Exception('Stream save error');
    }

    $this->stream_id = $stream->id;
  }

  /**
   * @param $userId
   * @param bool $save true - данные будут схранены в БД, false - вернется объект без сохранения (для отображения в ПП)
   * @return SmartLink|bool
   * @throws Exception
   */
  public static function createForUser($userId, $save = true)
  {
    $smartLink = self::findOne([
      'user_id' => $userId,
      'source_type' => self::SOURCE_TYPE_SMART_LINK
    ]);


    if (!$smartLink) {
      $domain = (new Domain())->getActiveSystem();

      if (!$domain) {
        Yii::error('Смарт ссылка для партнера' . $userId. ' не создана. Нет активных доменов');
        return false;
      }

      $smartLink = new self([
        'user_id' => $userId,
        'name' => self::DEFAULT_NAME,
        'status' => self::STATUS_APPROVED,
        'source_type' => self::SOURCE_TYPE_SMART_LINK,
        'domain_id' => $domain->id,
        'trafficback_type' => self::TRAFFICBACK_TYPE_STATIC,
      ]);

      $smartLink->initHash(self::HASH_LENGHT);
      if ($save && !$smartLink->save()) {
        Yii::error('Смарт ссылка для партнера' . $userId . ' не создана' . print_r($smartLink->getErrors(), true), __METHOD__);
      }
    };

    return $smartLink;

  }

}

