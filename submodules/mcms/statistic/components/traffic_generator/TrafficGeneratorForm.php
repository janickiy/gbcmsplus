<?php

namespace mcms\statistic\components\traffic_generator;


use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Форма для валидации данных генератора трафа
 */
class TrafficGeneratorForm extends Model
{
  /** @var string секретное слово в приемщике КП */
  public $kpSecret;
  /** @var string ссылка на приемщик в микросервисе. Например http://mcms-ml-handler.lc */
  public $pbHandlerUrl;
  /** @var  int фильтруем по источнику */
  public $sourceId;
  /** @var  int фильтруем по оператору */
  public $operatorId;
  /** @var float Какой процент подписок от существующих хитов создаём. */
  public $subsPercent;
  /** @var string Дата Y-m-d с которой подбираем хиты для подписок */
  public $hitsDateFrom;
  /** @var int Сколько хитов генерить */
  public $hitsCount;
  /** @var  string УРЛ хэндлера в микросервисе. Например http://mcms-api-handler.lc */
  public $hitHandlerUrl;
  /** @var float Какой процент ребиллов от подписок создаём. */
  public $rebillsPercent;
  /** @var  float Процент отписок от кол-ва подписок */
  public $offsPercent;
  /** @var  float Какой процент жалоб от подписок создаём. */
  public $complainsPercent;
  /** @var  float погрешность в применении процентов и кол-ва хитов. */
  public $inaccuracyPercent;

  public function init()
  {
    parent::init();

    $this->kpSecret = ArrayHelper::getValue(Yii::$app->params, 'kpSecret');
    $this->hitHandlerUrl = ArrayHelper::getValue(Yii::$app->params, 'hitHandlerUrl', 'http://mcms-api-handler.lc');
    $this->pbHandlerUrl = ArrayHelper::getValue(Yii::$app->params, 'pbHandlerUrl', 'http://mcms-ml-handler.lc');
    $this->subsPercent = 10;
    $this->rebillsPercent = 90;
    $this->complainsPercent = 0.1;
    $this->offsPercent = 50;
    $this->hitsDateFrom = (new \DateTime('-2 days'))->format('Y-m-d');
    $this->hitsCount = 1000;
    $this->inaccuracyPercent = 0;
  }

  public function rules()
  {
    return [
      [['pbHandlerUrl', 'hitHandlerUrl', 'hitsCount', 'subsPercent', 'rebillsPercent', 'offsPercent', 'complainsPercent', 'inaccuracyPercent', 'hitsDateFrom'], 'required'],
      [['kpSecret'], 'string'],
      [['pbHandlerUrl', 'hitHandlerUrl'], 'url'],
      [['sourceId', 'operatorId', 'hitsCount'], 'integer'],
      [['subsPercent', 'rebillsPercent', 'offsPercent', 'complainsPercent', 'inaccuracyPercent'], 'number'],
      [['hitsDateFrom'], 'date', 'format' => 'php:Y-m-d'],
    ];
  }
}