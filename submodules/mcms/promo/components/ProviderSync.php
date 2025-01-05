<?php

namespace mcms\promo\components;

use mcms\common\traits\LogTrait;
use mcms\currency\models\Currency;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Provider;
use yii\base\Object;
use mcms\common\helpers\curl\Curl;
use mcms\common\helpers\ArrayHelper;
use yii\console\Exception;
use yii\db\Query;
use Yii;

/**
 * Class ProviderSync
 * @package components
 */
class ProviderSync extends Object
{

  use LogTrait;

  /**
   * @var Provider
   */
  protected $providerModel;

  private $rootUserId;

  const DEFAULT_LANDING_RATING = 0;
  const DEFAULT_CURL_TIMEOUT = 10;


  const LANDINGS_CACHE_KEY = 'LandingsDataGroupByOperator';
  const LANDINGS_COUNT_CACHE_KEY = 'LandingsCountGroupByOperator';
  const OPERATORS_CACHE_KEY = 'OperatorsData';
  const OPERATORS_IPS_CACHE_KEY = 'OperatorsIps';

  public $clearCacheKeys = [];

  public $activeLandingList = [];

  /* @var array массив id => код валюты*/
  public $currencyConvert = [];

  protected $_avrNumberRebillsPerSub;

  /**
   * отключение курл-запросов для тестирования
   * @var bool
   */
  public $ignoreCurl = false;


  /**
   * 1 => Yii::t('sites', 'All Mobile'),
   * 2 => Yii::t('sites', 'Android Phone'),
   * 3 => Yii::t('sites', 'Android Tablet'),
   * 4 => Yii::t('sites', 'iPhone'),
   * 5 => Yii::t('sites', 'iPad'),
   * 6 => Yii::t('sites', 'Windows Mobile/Phone'),
   * 7 => Yii::t('sites', 'Symbian'),
   * 8 => Yii::t('sites', 'BlackBerry'),
   * 9 => Yii::t('sites', 'Java'),
   * 10 => Yii::t('sites', 'PC/Web Traffic')
   * 11 => Yii::t('sites', '3G Modem')
   */
  private $platformsConvert = [
    1 => [101, 102, 103, 104, 105, 106, 107, 108, 109], // если пришло All mobiles, то чекаем все платформы мобильников
    2 => 101,
    3 => 102,
    4 => 105,
    5 => 104,
    6 => 109,
    7 => 108,
    8 => 103,
    9 => 106,
    10 => 110,
    11 => 111
  ];

  /**
   * 1 => Yii::t('sites', 'Контекстная реклама'),
   * 2 => Yii::t('sites', 'Баннерная реклама'),
   * 3 => Yii::t('sites', 'RichMedia'),
   * 4 => Yii::t('sites', 'Email'),
   * 5 => Yii::t('sites', 'Социальные сети'),
   * 6 => Yii::t('sites', 'Teasers (Content)'),
   * 7 => Yii::t('sites', 'Clickunder/Popunder'),
   * 8 => Yii::t('sites', 'Дорвеи'),
   * 9 => Yii::t('sites', 'Контентные сайты'),
   * 10 => Yii::t('sites', 'SMS'),
   * 11 => Yii::t('sites', 'Push Ads')
   *
   * @var array
   */
  private $trafficTypesConvert = [
    1 => 1,
    2 => 2,
    3 => 3,
    4 => 4,
    5 => 5,
    6 => 6,
    7 => 7,
    8 => 8,
    9 => 9,
    10 => 10,
    11 => 11,
  ];

  /**
   * 1 => Yii::t('sites', 'МТ'),
   * 2 => Yii::t('sites', 'MO'),
   * 3 => Yii::t('sites', 'WAPClick'),
   *
   * @var array
   */
  private $payTypesConvert = [
    1 => 'mt',
    2 => 'mo',
    3 => '3g'
  ];

  /**
   * @param Provider $provider
   * @param array $config
   */
  public function __construct(Provider $provider, array $config = [])
  {
    $this->providerModel = $provider;
    foreach (Currency::find()->all() as $element) {
      $key = ArrayHelper::getValue($element, 'id');
      $value = ArrayHelper::getValue($element, 'code');
      $this->currencyConvert[$key] = strtolower($value);
    }
    parent::__construct($config);
  }


  /**
   * @param $url
   * @param array $postParams
   * @return mixed
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function sendPost($url, $postParams = [])
  {
    $curl = new Curl([
      'isPost' => true,
      'postFields' => $postParams,
      'url' => $url,
      'timeout' => ArrayHelper::getValue(Yii::$app->params, 'sync_curl_timeout', self::DEFAULT_CURL_TIMEOUT),
    ]);
    $curl->notUseProxy();

    $response = $curl->getResult();
    $info = $curl->getCurlInfo();

    $httpCode = ArrayHelper::getValue($info, 'http_code');

    if ($httpCode != 200) {
      Yii::error('Api ' . $this->providerModel->code . ' failed: http_code=' . $httpCode . '; response=' . $response, __METHOD__);
      return null;
    }

    if (empty($response)) {
      Yii::error('Api ' . $this->providerModel->code . ' failed: response is empty', __METHOD__);
      return null;
    }

    return $response;
  }

  /**
   * @return int
   */
  public function getRootUserId()
  {
    if ($this->rootUserId) return $this->rootUserId;

    $rootUsers = UsersHelper::getUsersByRoles(['root']);
    $user = reset($rootUsers);

    $this->rootUserId = $user['id'];

    return $this->rootUserId;
  }

  /**
   *
   * Преобразуем сериализованную строку платформ в массив айдишников.
   * В массиве $this->platformsConvert лежат переводы id из моблидерса в наши.
   *
   * @param $landingObj
   * @return array
   */
  public function convertPlatforms($landingObj)
  {
    $platforms = ArrayHelper::getValue($landingObj, 'allowed_platforms');

    if (!$platformsArr = @unserialize($platforms)) return [];

    $result = [];


    foreach ($platformsArr as $platform) {
      if (!$converted = ArrayHelper::getValue($this->platformsConvert, $platform)) continue;

      $converted = is_array($converted) ? $converted : [$converted];

      $result = array_merge($converted, $result);
    }

    return array_unique($result);
  }

  /**
   * @param $landingObj
   * @return array
   */
  public function convertForbiddenTrafficTypes($landingObj)
  {
    $types = ArrayHelper::getValue($landingObj, 'denied_traffic');

    if (!$typesArr = @unserialize($types)) return [];

    $res = array_map(function ($type) {
      if (!$converted = ArrayHelper::getValue($this->trafficTypesConvert, $type)) return null;
      return $converted;
    }, $typesArr);

    return array_filter($res);
  }

  /**
   * @param $operator
   * @return mixed
   */
  public function convertSubType($operator)
  {
    $code = ArrayHelper::getValue($operator, 'subscr_type') == 1 ? 'onetime' : 'sub';

    $codes = Yii::$app->db->cache(function () {
      return (new Query())
        ->select('id, code')
        ->from('landing_subscription_types')
        ->indexBy('code')
        ->all();
    }, 10);

    return $codes[$code]['id'];
  }

  /**
   * @param $operator
   * @return array
   */
  public function convertPayTypes($operator)
  {
    $types = ArrayHelper::getValue($operator, 'pay_types');

    if (!$typesArr = @unserialize($types)) return [];

    $codes = Yii::$app->db->cache(function () {
      return (new Query())
        ->select('id, code')
        ->from('landing_pay_types')
        ->indexBy('code')
        ->all();
    }, 10);

    $res = array_map(function ($type) use ($codes) {
      if (!$code = ArrayHelper::getValue($this->payTypesConvert, $type)) return null;
      if (!$payType = ArrayHelper::getValue($codes, $code)) return null;
      return $payType['id'];
    }, $typesArr);

    return array_filter($res);
  }

  /**
   * Получить наш ID ленда по ID ленда от провайдера
   * @param int $providerLandingId ID ленда от провайдера
   * @return int|null
   */
  protected function convertToLandingId($providerLandingId)
  {
    $id = (new Query())->select('id')->from(Landing::tableName())->andWhere([
      'provider_id' => $this->providerModel->id,
      'send_id' => $providerLandingId
    ])->scalar();

    if ($id) {
      return (int)$id;
    }

    return null;
  }

  /**
   * Задано ли в параметрах системы жесткое кол-во дней в холде для партнеров?
   * @return bool
   */
  protected function isForcePartnerHold()
  {
    return isset(Yii::$app->params['forcePartnerHold']);
  }

  /**
   * В параметрах системы жесткое кол-во дней в холде для партнеров
   * @return int
   */
  protected function getForcedPartnerHoldDays()
  {
    return (int)ArrayHelper::getValue(Yii::$app->params, 'forcePartnerHold');
  }

  /**
   * Среднее количество ребиллов на подписку для рассчета цены выкупа
   * @return int
   */
  protected function getAvrNumberRebillsPerSub()
  {
    if (!$this->_avrNumberRebillsPerSub) {
      $this->_avrNumberRebillsPerSub = (int)Yii::$app->getModule('promo')->getAvrNumberRebillsPerSub();
    }
    return $this->_avrNumberRebillsPerSub;
  }

  /**
   * @param float $rebillPrice
   * @return float
   */
  protected function getBuyoutPrice($rebillPrice)
  {
    return $rebillPrice * $this->getAvrNumberRebillsPerSub();
  }

  /**
   * Обновление топа лендингов
   *
   * @param $landingsRatingByCategory array массив ленд => рейтинг по категории и оператору
   * @param $landingsLastSubByCategory array массив ленд => дата последней подписки по категории и оператору
   * @throws \yii\db\Exception
   */
  protected function updateTopLandings($landingsRatingByCategory, $landingsLastSubByCategory)
  {
    $topLandings = [];
    foreach ($landingsRatingByCategory as $category => $operators) {
      foreach ($operators as $operatorId => $landings) {
        //Находим лендинги с максимальным рейтингом
        $rating = max($landings);
        $landingsWithMaxRating = array_keys($landings, $rating);
        //Берем рандомный лендинг из них
        $landingId = $landingsWithMaxRating[array_rand($landingsWithMaxRating, 1)];
        $topLandings[$category][$operatorId] = ['rating' => $rating, 'landing_id' => $landingId];

        if ($rating > 0) {
          continue;
        }

        //если рейтинг равен нулю то берем лендинг с последней датой подписки. Если подписок не было возьмем рандомный
        $lastSubDate = max($landingsLastSubByCategory[$category][$operatorId]);
        $landingsWithLastSubDate = array_keys($landingsLastSubByCategory[$category][$operatorId], $lastSubDate);
        //Берем рандомный лендинг из них
        $landingId = $landingsWithLastSubDate[array_rand($landingsWithLastSubDate, 1)];
        $topLandings[$category][$operatorId] = ['rating' => 0, 'landing_id' => $landingId];
      }
    }

    foreach ($topLandings as $categoryId => $operators) {
      foreach ($operators as $operatorId => $topLanding) {
        $topOperatorLandings = [];

        $topOperatorLandings[] = [$operatorId, $categoryId, $topLanding['landing_id'], $topLanding['rating']];

        $db = Yii::$app->db;
        $sql = $db->queryBuilder->batchInsert('operator_top_landings', ['operator_id', 'category_id', 'landing_id', 'rating'], $topOperatorLandings);

        $db->createCommand($sql . ' ON DUPLICATE KEY UPDATE landing_id=VALUES(landing_id), rating=VALUES(rating)')->execute();
      }
    }
  }
}
