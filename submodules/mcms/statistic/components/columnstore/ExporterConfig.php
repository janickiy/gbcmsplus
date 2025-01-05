<?php

namespace mcms\statistic\components\columnstore;

use yii\helpers\ArrayHelper;

/**
 * Конфигурация экспортера, чтобы все параметры не передавать каждый раз, можно передавать этот класс.
 * Помимо конструктора конфиг никак не изменить. Никаких сеттеров в этом классе!
 *
 * TODO похожий класс есть
 * @see \mcms\statistic\components\traffic_generator\GeneratorConfig
 * Может имеет смысл сделать общий компонент если есть необходимость
 *
 * @property string $with
 * @property string $dir
 * @property int $hitsFrom
 * @property int $hitsTo
 * @property int $subsFrom;
 * @property int $subsTo;
 * @property int $rebillsFrom;
 * @property int $rebillsTo;
 * @property int $offsFrom;
 * @property int $offsTo;
 * @property int $onetimesFrom;
 * @property int $onetimesTo;
 * @property int $soldsFrom;
 * @property int $soldsTo;
 * @property string $dateFrom;
 * @property string $dateTo;
 * @property int $refundsFrom;
 * @property int $refundsTo;
 * @property int $complaintsFrom;
 * @property int $complaintsTo;
 */
class ExporterConfig
{

  const WITH_ALL = 'all';
  const LOCK_FILE_NAME = 'export_finished.lock';

  protected $with;
  protected $dir;
  protected $hitsFrom;
  protected $hitsTo;
  protected $subsFrom;
  protected $subsTo;
  protected $rebillsFrom;
  protected $rebillsTo;
  protected $offsFrom;
  protected $offsTo;
  protected $onetimesFrom;
  protected $onetimesTo;
  protected $soldsFrom;
  protected $soldsTo;
  protected $dateFrom;
  protected $dateTo;
  protected $refundsFrom;
  protected $refundsTo;
  protected $complaintsFrom;
  protected $complaintsTo;

  /**
   * @var array Список экспортируемых данных
   */
  private static $_queries = [
    'hits',
    'subs',
    'rebills',
    'offs',
    'onetimes',
    'solds',
    'refunds',
    'complaints',
  ];

  /**
   * Помимо конструктора конфиг никак не изменить. Никаких сеттеров!
   * @param array $config
   * @throws \yii\base\InvalidParamException
   */
  public function __construct(array $config = [])
  {
    $this->setProp($config, 'with', 'all');
    $this->setProp($config, 'dir');
    $this->setProp($config, 'hitsFrom');
    $this->setProp($config, 'hitsTo');
    $this->setProp($config, 'subsFrom');
    $this->setProp($config, 'subsTo');
    $this->setProp($config, 'rebillsFrom');
    $this->setProp($config, 'rebillsTo');
    $this->setProp($config, 'offsFrom');
    $this->setProp($config, 'offsTo');
    $this->setProp($config, 'onetimesFrom');
    $this->setProp($config, 'onetimesTo');
    $this->setProp($config, 'soldsFrom');
    $this->setProp($config, 'soldsTo');
    $this->setProp($config, 'dateFrom');
    $this->setProp($config, 'dateTo');
    $this->setProp($config, 'refundsFrom');
    $this->setProp($config, 'refundsTo');
    $this->setProp($config, 'complaintsFrom');
    $this->setProp($config, 'complaintsTo');
  }

  /**
   * @param $name
   * @return mixed
   */
  public function __get($name)
  {
    return $this->{$name};
  }

  /**
   * @param $config
   * @param $param
   * @param null $default
   */
  private function setProp($config, $param, $default = null)
  {
    $value = ArrayHelper::getValue($config, $param);

    if ($value === null) {
      $value = $default;
    }

    $this->{$param} = $value;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return get_object_vars($this);
  }

  /**
   * Получить [[with]] в виде массива строк
   * @return string[]
   */
  public function getWithList()
  {
    $exploded = explode(',', $this->with);

    $queries = array_filter(self::$_queries, function ($query) use ($exploded) {
      if ($this->with === self::WITH_ALL) {
        return true;
      }
      return in_array($query, $exploded, true);
    });

    return $queries;
  }

  /**
   * Сформировать путь до lock-файла
   * @return string
   */
  public function getLockFilePath()
  {
    return $this->getDir() . '/' . self::LOCK_FILE_NAME;
  }

  /**
   * @return string
   */
  public function getDir()
  {
    return rtrim($this->dir, '/');
  }
}
