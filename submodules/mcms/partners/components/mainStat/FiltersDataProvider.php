<?php

namespace mcms\partners\components\mainStat;

use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Получение данных для фильтров статы. Например операторы, страны и т.д.
 */
class FiltersDataProvider
{

  private static $_instance;
  /** @var \mcms\promo\Module */
  private $promoModule;
  private $_platforms;
  private $_operatorsByCountry;
  private $_landingsByCountry;
  private $_webmasterSources;
  private $_arbitraryLinksByStreams;
  private $_streams;
  private $_countries;

  private function __construct()
  {
    $this->promoModule = Yii::$app->getModule('promo');
  }

  /**
   * синглтончик
   * @return FiltersDataProvider
   */
  public static function getInstance()
  {
    if (self::$_instance) {
      return self::$_instance;
    }

    self::$_instance = new self();
    return self::$_instance;
  }

  /**
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function getOperatorsGroupedByCountry()
  {
    if ($this->_operatorsByCountry) {
      return $this->_operatorsByCountry;
    }

    return $this->_operatorsByCountry = $this->promoModule
      ->api('operators', [
        'conditions' => [
          'onlyActiveCountries' => true,
          'id' => [],
        ],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
        'pagination' => false,
        'orderByCountry',
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name', 'country.name'])
      ->getResult();
  }

  /**
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function getPlatforms()
  {
    if ($this->_platforms) {
      return $this->_platforms;
    }

    return $this->_platforms = $this->promoModule
      ->api('platforms', [
        'conditions' => [
          'id' => [],
        ],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
        'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }

  /**
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function getLandingsByCountry()
  {
    if ($this->_landingsByCountry) {
      return $this->_landingsByCountry;
    }

    return $this->_landingsByCountry = $this->promoModule
      ->api('landingOperators', [
        'conditions' => [
          'onlyActiveCountries' => true,
          'landing_id' => [],
        ],
        'pagination' => false,
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
        'orderByCountry',
      ])
      ->setResultTypeMap()
      ->setMapParams([ 'landing.id', 'landing.name', 'operator.country.name'])
      ->getResult();
  }

  /**
   * @param array $pagination
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function getWebmasterSources($pagination = ['pageSize' => 1000])
  {
    if ($this->_webmasterSources) {
      return $this->_webmasterSources;
    }

    return $this->_webmasterSources = $this->promoModule
      ->api('sources', [
        'conditions' => [
          'source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE,
          'id' => [],
        ],
        'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
        'pagination' => $pagination,
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }

  /**
   * Ссылки сгруппированные по потокам.
   * P.S.: не стал ничего рефакторить тут, скопипастил из старого кода
   * @param array $pagination
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function getArbitraryLinksByStreams($pagination = ['pageSize' => 1000])
  {
    if ($this->_arbitraryLinksByStreams) {
      return $this->_arbitraryLinksByStreams;
    }

    $linksByStream = $this->promoModule
      ->api('sources', [
        'conditions' => [
          'source_type' => [Source::SOURCE_TYPE_LINK, Source::SOURCE_TYPE_SMART_LINK],
          'id' => []
        ],
        'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        'pagination' => $pagination,
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
        'orderByStreamName',
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name', 'stream.id'])->getResult();

    $streams = Stream::find()->select(['id', 'name'])->andWhere(['id' => array_keys($linksByStream)])->asArray()->all();
    $streamsMapped = ArrayHelper::map($streams, 'id', 'name');

    $result = [];
    foreach ($linksByStream as $streamId => $links) {
      $result[] = [
        'id' => $streamId,
        'name' => $streamsMapped[$streamId],
        'links' => $linksByStream[$streamId],
      ];
    }

    return $this->_arbitraryLinksByStreams = $result;
  }

  /**
   * @return array
   */
  public function getArbitraryLinks()
  {
    if (isset($this->_arbitraryLinks)) {
      return $this->_arbitraryLinks;
    }

    $this->_arbitraryLinks = Yii::$app->getModule('promo')
      ->api('sources', ['conditions' => ['source_type' => [Source::SOURCE_TYPE_LINK, Source::SOURCE_TYPE_SMART_LINK]]])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_arbitraryLinks;
  }

  /**
   * @return array
   */
  public function getStreams()
  {
    if ($this->_streams) {
      return $this->_streams;
    }

    $this->_streams = Yii::$app->getModule('promo')
      ->api('streams', [
        'conditions' => [
          'id' => [],
        ],
        'pagination' => ['pageSize' => 0],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_streams;
  }

  /**
   * @return array
   */
  public function getCountries()
  {
    if ($this->_countries) {
      return $this->_countries;
    }

    $this->_countries = Yii::$app->getModule('promo')
      ->api('countries', [
        'conditions' => [
          'id' => []
        ],
        'pagination' => ['pageSize' => 0],
        'statFiltersUser' => Yii::$app->user->id,
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return $this->_countries;
  }
}
