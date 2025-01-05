<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\CIDR;
use mcms\common\helpers\Html;
use mcms\common\helpers\Link;
use mcms\promo\components\api\OperatorIpList;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\events\OperatorCreated;
use mcms\promo\components\events\OperatorUpdated;
use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use mcms\promo\models\search\LandingSearch;
use mcms\user\models\User;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\Url;
use mcms\promo\components\TrafficBlockChecker;

/**
 * This is the model class for table "{{%operators}}".
 *
 * @property integer $id
 * @property integer $country_id
 * @property string $name
 * @property integer $status
 * @property integer $is_3g
 * @property integer $show_service_url
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $sync_updated_at
 * @property integer $is_trial
 * @property boolean $is_disallow_replace_landing
 * @property boolean $is_geo_default
 *
 * @property LandingOperator[] $landingOperator
 * @property Landing[] $landings
 * @property Landing[] $activeLandings
 * @property OperatorIp[] $operatorIp
 * @property Country $country
 * @property User $createdBy
 * @property PersonalProfit[] $personalProfit
 * @property SourceOperatorLanding[] $sourcesOperatorLanding
 */
class Operator extends \yii\db\ActiveRecord
{

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  private $_landingsCount;
  private $_activeLandingsCount;

  public $ipModels;
  public $ipTextarea;

  const DROP_DOWN_OPERATORS_CACHE_KEY = 'operators_dropdown';
  const DROP_DOWN_OPERATORS_TAGS = ['operator', 'country'];

  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%operators}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    // Если есть право, добавляем к интежер-полям show_service_url
    $integerFields = ['country_id', 'status', 'created_by', 'is_3g'];
    if (Yii::$app->getModule('promo')->canChangeOperatorShowServiceUrl()) {
      $integerFields[] = 'show_service_url';
    }

    return [
      ['created_by', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['country_id', 'name', 'created_by', 'status'], 'required'],
      [$integerFields, 'integer'],
      [['name'], 'string', 'max' => 50],
      [['ipTextarea'], 'validateIps'],
      [['ipTextarea'], 'safe'],
      [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::class, 'targetAttribute' => ['country_id' => 'id']],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
      [['is_disallow_replace_landing','is_geo_default'], 'boolean'],
      [['sync_updated_at', 'is_trial'], 'safe'],
      ['is_geo_default', 'unique', 'targetAttribute' => ['country_id'],
        'filter' => function($query){$query->andWhere(['is_geo_default'=>1]);},
        'when' => function($model){ return $model->is_geo_default == "1";},
        'message' => Yii::_t('promo.operators.error-main_operator_for_the_selected_geo')],
    ];
  }

  public function validateIps()
  {
    /** @var OperatorIp $ip */
    foreach((array)$this->ipModels as $ip) {
      if (!$ip->validate(array_keys($ip->getAttributes(null, ['operator_id', 'to_ip'])))) {
        $ipMessage = $ip->mask ? implode('/', [$ip->from_ip, $ip->mask]) : $ip->from_ip;
        $this->addError('ipTextarea', '"' . $ipMessage . '" - ' . Yii::_t('promo.operators.ip_save_error'));
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'country_id' => Yii::_t('promo.operators.attribute-country_id'),
      'name' => Yii::_t('promo.operators.attribute-name'),
      'is_3g' => Yii::_t('promo.operators.attribute-is_3g'),
      'show_service_url' => Yii::_t('promo.operators.attribute-show_service_url'),
      'status' => Yii::_t('promo.operators.attribute-status'),
      'created_by' => Yii::_t('promo.operators.attribute-created_by'),
      'created_at' => Yii::_t('promo.operators.attribute-created_at'),
      'updated_at' => Yii::_t('promo.operators.attribute-updated_at'),
      'ipTextarea' => Yii::_t('promo.operators.attribute-ipTextarea'),
      'is_trial' => Yii::_t('promo.operators.attribute-is_trial'),
      'is_disallow_replace_landing' => Yii::_t('promo.operators.attribute-is_disallow_replace_landing'),
      'is_geo_default' => Yii::_t('promo.operators.attribute-is_geo_default'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingOperator()
  {
    return $this->hasMany(LandingOperator::class, ['operator_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasMany(Landing::class, ['id' => 'landing_id'])->viaTable('{{%landing_operators}}', ['operator_id' => 'id']);
  }

  /**
   * @return integer
   */
  public function getLandingsCount()
  {
    return $this->_landingsCount = ($this->_landingsCount !== null)
      ? $this->_landingsCount
      : self::find()->andWhere((['=', self::tableName() . '.id', $this->id]))
        ->innerJoin(LandingOperator::tableName(), self::tableName() . '.id = ' . LandingOperator::tableName() . '.operator_id')
        ->innerJoin(Landing::tableName(), Landing::tableName() . '.id = ' . LandingOperator::tableName() . '.landing_id')
        ->count();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getActiveLandings()
  {
    return $this->hasMany(Landing::class, ['id' => 'landing_id'])->viaTable('{{%landing_operators}}', ['operator_id' => 'id'])
      ->andWhere([Landing::tableName() . '.status' => Landing::STATUS_ACTIVE]);
  }

  /**
   * @param bool $isSelectUnlockedHidden Показывать ли ленды, у которых доступ=скрытый,
   * но у партнера есть доступ по заявке
   * @return integer
   */
  public function getActiveLandingsCount($isSelectUnlockedHidden = false)
  {
    if ($this->_activeLandingsCount !== null) {
      return $this->_activeLandingsCount;
    }

    return $this->getActiveLandingsQuery($isSelectUnlockedHidden)->count();
  }

  /**
   * @param bool $isSelectUnlockedHidden Показывать ли ленды, у которых доступ=скрытый,
   * но у партнера есть доступ по заявке
   * @return Query $qeury
   */
  public function getActiveLandingsQuery($isSelectUnlockedHidden = false)
  {
    $allowedLandings = $isSelectUnlockedHidden ? LandingUnblockRequest::find()->where([
      'user_id' => Yii::$app->user->id,
      'status' => LandingUnblockRequest::STATUS_UNLOCKED
    ])->select('landing_id') : [];

    $query = (new Query())
      ->select(LandingOperator::tableName() . '.*')
      ->from(self::tableName())
      ->innerJoin(LandingOperator::tableName(), self::tableName() . '.id = ' . LandingOperator::tableName() . '.operator_id AND ' . LandingOperator::tableName() . '.is_deleted = 0')
      ->innerJoin(Landing::tableName(), Landing::tableName() . '.id = ' . LandingOperator::tableName() . '.landing_id')
      ->where([
        self::tableName() . '.id' => $this->id,
        Landing::tableName().'.status' => Landing::STATUS_ACTIVE
      ])
    ;

    $query->andWhere([
      'or',
      ['!=', Landing::tableName().'.access_type', Landing::ACCESS_TYPE_HIDDEN],
      $isSelectUnlockedHidden ? ['IN', 'landing_id', $allowedLandings] : '1 = 0'
    ]);
    return $query;
  }


  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperatorIp()
  {
    return $this->hasMany(OperatorIp::class, ['operator_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCountry()
  {
    return $this->hasOne(Country::class, ['id' => 'country_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCreatedBy()
  {
    return $this->hasOne(User::class, ['id' => 'created_by']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPersonalProfits()
  {
    return $this->hasMany(PersonalProfit::class, ['operator_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSourceOperatorLanding()
  {
    return $this->hasMany(SourceOperatorLanding::class, ['operator_id' => 'id']);
  }


  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => Yii::_t('promo.operators.status-inactive'),
      self::STATUS_ACTIVE => Yii::_t('promo.operators.status-active')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }


  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  public function getCountryLink()
  {
    return \mcms\common\helpers\Link::get(
      '/promo/countries/view',
      ['id' => $this->country_id], ['data-pjax' => 0], $this->getCountryName()
    ) ? : $this->getCountryName();
  }

  public function getNameWithCountry()
  {
    return $this->name . ' (' . $this->country->code . ')';
  }

  public function getCountryName()
  {
    return $this->country->name;
  }

  public function loadIps()
  {
    $this->ipModels = [];
    $arr = [];
    foreach(self::ipsListToArray($this->ipTextarea) as $ipArray) {
      $arr['fromip_' . ArrayHelper::getValue($ipArray, 'from_ip') . '_mask_' . ArrayHelper::getValue($ipArray, 'mask')] = $ipArray;
    }
    foreach($arr as $ip) {
      $ipModel = new OperatorIp($ip);
      if ($this->id) {
        $ipModel->operator_id = $this->id;
        if (CIDR::validIP($ipModel->from_ip))
          $ipModel = $ipModel->tryToFind();
      }
      $this->ipModels[] = $ipModel;
    }

    return true;
  }

  public function afterSave($insert, $changedAttributes)
  {
    if ($this->ipModels !== null) {
      $oldIDs = $insert ? [] : ArrayHelper::map($this->operatorIp, 'id', 'id');

      foreach ($this->ipModels as $ipModel) {
        $ipModel->operator_id = $this->id;
        if (!$ipModel->save(false)) throw new Exception('Ip model save error');
      }

      $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($this->ipModels, 'id', 'id')));

      if (!empty($deletedIDs)) OperatorIp::deleteAll(['id' => $deletedIDs]);
    }

    $insert
      ? (new OperatorCreated($this))->trigger()
      : (new OperatorUpdated($this))->trigger()
    ;

    $this->invalidateCache();

    (new LandingSetsLandsUpdater(
      ['landingIds' => ArrayHelper::getColumn($this->getLanding()->all(), 'id')]
    ))->run();

    parent::afterSave($insert, $changedAttributes);
  }


  public function getReplacements()
  {
    /** @var Country $country */
    $country = $this->getCountry()->one();
    /** @var User $createdBy */
    $createdBy = $this->getCreatedBy()->one();
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('promo.replacements.operator_id')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => Yii::_t('promo.replacements.operator_name')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => Yii::_t('promo.replacements.operator_status')
        ]
      ],
      'is_3g' => [
        'value' => $this->isNewRecord ? null : $this->is_3g,
        'help' => [
          'label' => Yii::_t('promo.replacements.operator_is_3g')
        ]
      ],

      'country' => [
        'value' => $this->isNewRecord ? null : $country->getReplacements(),
        'help' => [
          'label' => Yii::_t('promo.replacements.operator_country')
        ]
      ],
      'createdBy' => [
        'value' => $this->isNewRecord ? null : $createdBy->getReplacements(),
        'help' => [
          'label' => Yii::_t('promo.replacements.operator_createdBy')
        ]
      ],
      'operatorIps' => [
        'value' => $this->isNewRecord ? null : $this->getReplacementOperatorIps(),
        'help' => [
          'label' => Yii::_t('promo.replacements.operator_operator_ips')
        ]
      ],
    ];
  }

  public function getReplacementOperatorIps()
  {
    if (!empty ($this->operatorIp)) {
      $replacement = [];
      foreach ($this->operatorIp as $ip) {
        $replacement[] = $ip->from_ip . '-' . $ip->to_ip;
      }
      return implode(', ', $replacement);
    }
  }

  /**
   * Ссылка на просмотр оператора
   * @param string $additionalInfo
   * @param bool $isDisabled
   * @return string
   */
  public function getViewLink($additionalInfo = '', $isDisabled = false)
  {
    return Link::get(
      '/promo/operators/view',
      ['id' => $this->id],
      [
        'data-pjax' => 0,
        'class' => $this->status == self::STATUS_ACTIVE && !$isDisabled ? '' : 'text-danger'
      ], $this->getStringInfo() . $additionalInfo, false
    );
  }

  public static function getViewUrl($id, $asString = false)
  {
    $arr = ['/promo/operators/view', 'id' => $id];
    return $asString ? Url::to($arr) : $arr;
  }


  /**
   * Оператор в виде строки
   * @return string
   */
  public function getStringInfo()
  {
    return sprintf(
      '#%s - %s (%s)',
      ArrayHelper::getValue($this, 'id'),
      ArrayHelper::getValue($this, 'name'),
      ArrayHelper::getValue($this->country, 'code')
    );
  }

  /**
   * Данный кэш создается и используется в апи OperatorIpList.
   * При изменении текущей модели необходимо сбрасывать кэш.
   */
  protected function invalidateCache()
  {
    // Сбрасываем кэш для конкретного оператора, а также для всех кэшей, где оператор не указан в параметрах поиска.
    TagDependency::invalidate(Yii::$app->cache, [
      OperatorIpList::CACHE_KEY_PREFIX . 'operatorid' . $this->id,
      OperatorIpList::CACHE_KEY_PREFIX . 'operatorid',
      'operator',
    ]);

    ApiHandlersHelper::clearCache('OperatorsIps'); // Сбрасываем кэш микросервисов
    ApiHandlersHelper::clearCache('LandingsDataGroupByOperator');
    ApiHandlersHelper::clearCache('LandingsDataByOperator-' . $this->id);
    ApiHandlersHelper::clearCache('OperatorsData');
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return $this->status !== self::STATUS_ACTIVE;
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  /**
   * @return $this
   */
  public function setEnabled()
  {
    $this->status = self::STATUS_ACTIVE;
    return $this;
  }

  /**
   * @return $this
   */
  public function setDisabled()
  {
    $this->status = self::STATUS_INACTIVE;
    return $this;
  }
  static public function ipsListToArray($ips)
  {
    $ips = explode("\n", $ips);
    $model_ips = [];

    foreach ($ips as $cidr) {
      if (empty($cidr)) continue;

      $cidr = trim($cidr);
      if (strpos($cidr, '/') == false) {
        $ip_range = explode('-', $cidr);
        $start = trim($ip_range[0]);

        if ($start === '') continue;

        $end = empty($ip_range[1]) ? $start : trim($ip_range[1]);

        if (!CIDR::validIP($start)) {
          $model_ips[] = ['from_ip' => $start];
          continue;
        }
        if ($start != $end && !CIDR::validIP($end)) {
          $model_ips[] = ['from_ip' => $end];
          continue;
        }

        foreach (CIDR::rangeToCIDRList($start, $end) as $cidrRow) {
          list ($net, $mask) = explode('/', $cidrRow);
          $model_ips[] = ['from_ip' => $net, 'mask' => $mask];
        }
      } else {
        list ($net, $mask) = explode('/', $cidr);
        if (!CIDR::validIP($net)) {
          $model_ips[] = ['from_ip' => $cidr];
          continue;
        }

        list ($start, $end) = CIDR::cidrToRange($cidr);

        $model_ips[] = ['from_ip' => $net, 'mask' => $mask, 'to_ip' => $end];
      }

    }
    return $model_ips;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public static function getActiveOperatorsWithActiveLandings()
  {
    return Operator::find()
      ->joinWith(['landingOperator', 'landingOperator.landing'])
      ->where([Operator::tableName() . '.status' => Operator::STATUS_ACTIVE])
      ->andWhere([Landing::tableName() . '.status' => Landing::STATUS_ACTIVE]);
  }

  public function getLandingsLink()
  {
    return Link::get(
      'landings/index',
      [(new LandingSearch())->formName() . '[operators][]' => $this->id],
      ['data-pjax' => 0],
      Yii::_t('promo.landings.main')) . ' ' . Html::tag('span', $this->activeLandingsCount, ['class' => 'label label-default']);
  }

  public static function getOperatorsDropDown($countriesId = [], $groupByCountry = true, $onlyActiveCountries = false, $operatorsId = [], $onlyActiveLands = true, $operatorId = null)
  {
    $cacheKey = self::DROP_DOWN_OPERATORS_CACHE_KEY;
    if ($onlyActiveCountries) {
      $cacheKey .= '_only_active_countries_';
    }
    if($groupByCountry) {
      $cacheKey .= '_grouped_countries_';
    } else {
      $cacheKey .= '_ungrouped_';
    }
    if ($countriesId) {
      asort($countriesId);
      $cacheKey .= '_countries_' . serialize($countriesId);
    }

    if (!empty($operatorsId)) {
      $cacheKey .= 'operators_' . serialize($operatorsId);
    }

    if (!empty($operatorId)) {
      $cacheKey .= 'selected_op_' . $operatorId;
    }

    $items = Yii::$app->cache->get($cacheKey);
    if ($items === false) {
      $queryWhere = [];

      if ($countriesId) {
        $queryWhere[Country::tableName() . '.id'] = $countriesId;
      }
      if ($onlyActiveCountries) {
        $queryWhere[Country::tableName() . '.status'] = Country::STATUS_ACTIVE;
      }
      if (!empty($operatorsId)) {
        $queryWhere[self::tableName() . '.id'] = $operatorsId;
      }

      $operatorsWhere = ['or', [Operator::tableName() . '.status' => Operator::STATUS_ACTIVE]];
      if ($operatorId) {
        $operatorsWhere[] = [Operator::tableName() . '.id' => $operatorId];
      }

      $where = ['and', $queryWhere, $operatorsWhere];

      $operators = Operator::find()
        ->where($where)
        ->joinWith($onlyActiveLands ? ['country', 'activeLandings'] : ['country'])
        ->orderBy([
          Country::tableName() . '.name' => SORT_ASC,
          Operator::tableName() . '.name' => SORT_ASC
        ])
        ->groupBy([Operator::tableName() . '.id'])
        ->all();

      // Группируем по странам только если $groupByCountry = true и если id стран не указаны, либо больше 1.
      if ($groupByCountry && (!$countriesId || count($countriesId) > 1)) {
        $items = ArrayHelper::map($operators, 'id', 'nameWithCountry', 'country.name');
      } else {
        $items = ArrayHelper::map($operators, 'id', 'nameWithCountry');
      }

      // Кладем в кэш
      Yii::$app->cache->set(
        $cacheKey,
        $items,
        3600,
        new TagDependency(['tags' => self::DROP_DOWN_OPERATORS_TAGS])
      );
    }

    return $items;
  }

  /**
   * @return boolean
   */
  public function isActive()
  {
    return $this->status == self::STATUS_ACTIVE;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public static function getInactiveOperatorQuery()
  {
    return static::find()
      ->where(['<>', 'status', self::STATUS_ACTIVE])
      ;
  }

  /**
   * @return string
   */
  public function getBuyoutMinutesGlobalText()
  {
    /** @var \mcms\statistic\Module $statisticModule */
    $statisticModule = Yii::$app->getModule('statistic');
    /** @var \mcms\statistic\components\api\ModuleSettings $api */
    $api = $statisticModule->api('moduleSettings');
    $globalValue = $api->getBuyoutMinutes();

    return Yii::_t('promo.operators.buyout_minutes_is_global', [
      'minutes' => $globalValue
    ]);
  }

  /**
   * Может ли редактировать едитабл поля в модалке при просмотре оператора
   * @return bool
   */
  public function canUpdateParams()
  {
    return Yii::$app->user->can('PromoOperatorsUpdateOperatorParams');
  }

  /**
   * Проверяет, заблокирован ли трафик по этому оператору
   * @param int|string|null $userId
   * @return bool
   */
  public function isTrafficBlocked($userId = null)
  {
    if ($userId === null) {
      $userId = Yii::$app->user->id;
    }

    return (new TrafficBlockChecker($userId, $this->id))->isTrafficBlocked();
  }
}
