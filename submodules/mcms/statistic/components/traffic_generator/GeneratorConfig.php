<?php

namespace mcms\statistic\components\traffic_generator;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Конфигурация генератора, чтобы все параметры не передавать каждый раз, можно передавать этот класс.
 * Помимо конструктора конфиг никак не изменить. Никаких сеттеров в этом классе!
 *
 * @property string $kpSecret @see GeneratorConfig::$kpSecret
 * @property string $pbHandlerUrl @see GeneratorConfig::$pbHandlerUrl
 * @property int $sourceId @see GeneratorConfig::$sourceId
 * @property int $operatorId @see GeneratorConfig::$operatorId
 * @property float $subsPercent @see GeneratorConfig::$subsPercent
 * @property string $hitsDateFrom @see GeneratorConfig::$hitsDateFrom
 * @property int $hitsCount @see GeneratorConfig::$hitsCount
 * @property string $hitHandlerUrl @see GeneratorConfig::$hitHandlerUrl
 * @property string $rebillsPercent @see GeneratorConfig::$rebillsPercent
 * @property string $offsPercent @see GeneratorConfig::$offsPercent
 * @property string $complainsPercent @see GeneratorConfig::$complainsPercent
 * @property string $inaccuracyPercent @see GeneratorConfig::$inaccuracyPercent
 */
class GeneratorConfig
{
  /** @var string секретное слово в приемщике КП */
  protected $kpSecret;
  /** @var string ссылка на приемщик в микросервисе. Например http://mcms-ml-handler.dev */
  protected $pbHandlerUrl;
  /** @var  int фильтруем по источнику */
  protected $sourceId;
  /** @var  int фильтруем по оператору */
  protected $operatorId;
  /** @var float Какой процент подписок от существующих хитов создаём. */
  protected $subsPercent;
  /** @var string Дата Y-m-d с которой подбираем хиты для подписок */
  protected $hitsDateFrom;
  /** @var int Сколько хитов генерить */
  protected $hitsCount;
  /** @var  string УРЛ хэндлера в микросервисе. Например http://mcms-api-handler.dev */
  protected $hitHandlerUrl;
  /** @var float Какой процент ребиллов от подписок создаём. */
  protected $rebillsPercent;
  /** @var  float Процент отписок от кол-ва подписок */
  protected $offsPercent;
  /** @var  float Какой процент жалоб от подписок создаём. */
  protected $complainsPercent;
  /** @var  float погрешность в применении процентов и кол-ва хитов. */
  protected $inaccuracyPercent;

  /**
   * Помимо конструктора конфиг никак не изменить. Никаких сеттеров!
   * @param array $config
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  public function __construct(array $config = [])
  {
    $this->setProp($config, 'kpSecret', $this->getAppParam('kpSecret'));
    $this->setProp($config, 'pbHandlerUrl', $this->getAppParam('pbHandlerUrl', 'http://mcms-ml-handler.lc'));
    $this->setProp($config, 'hitHandlerUrl', $this->getAppParam('hitHandlerUrl', 'http://mcms-api-handler.lc'));
    $this->setProp($config, 'sourceId');
    $this->setProp($config, 'operatorId');
    $this->setProp($config, 'subsPercent', 10);
    $this->setProp($config, 'rebillsPercent', 90);
    $this->setProp($config, 'complainsPercent', 0.1);
    $this->setProp($config, 'offsPercent', 50);
    $this->setProp($config, 'hitsDateFrom', '-2 days');
    $this->setProp($config, 'hitsCount', 1000);
    $this->setProp($config, 'inaccuracyPercent', 0);
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
   * @param $name
   * @param $default
   * @return mixed
   */
  private function getAppParam($name, $default = null)
  {
    return ArrayHelper::getValue(Yii::$app->params, $name, $default);
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
}
