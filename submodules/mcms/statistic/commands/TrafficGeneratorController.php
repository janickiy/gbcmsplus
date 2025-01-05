<?php
namespace mcms\statistic\commands;

use Exception;
use mcms\statistic\components\traffic_generator\AbstractGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\ComplainsGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\OffsGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\RebillsGenerator;
use mcms\statistic\components\traffic_generator\conversions_generators\SubsGenerator;
use mcms\statistic\components\traffic_generator\GeneratorConfig;
use mcms\statistic\components\traffic_generator\TrafficGenerator;
use yii\console\Controller;
use yii\helpers\Console;
use Yii;

/**
 * Траф-генератор. Отправляет реальные хиты и конверсии в микросервис
 */
class TrafficGeneratorController extends Controller
{

  const WITH_ALL = 'all';

  /**
   * @var bool whether to run the command interactively.
   */
  public $interactive = false;
  /**
   * @var string Какие генераторы запустить через запятую.
   * Возможные варианты: all|hits|subs|rebills|offs|complains
   * По-умолчанию all (запускает все генераторы).
   */
  public $with = self::WITH_ALL;
  /**
   * @var int Фильтруем по источнику
   */
  public $sourceId;
  /**
   * @var int Фильтруем по оператору
   */
  public $operatorId;
  /**
   * @var float Какой процент подписок от существующих хитов создаём (см. также параметр hitsDateFrom).
   * Количество пдп которые надо сгенерить считаем исходя из существующей конверсии и той,
   * которая требуется в этой опции.
   * Создаем единоразовую или обычную пдп в зависимости от настроек ленд-оператора.
   * По-умолчанию 10.
   */
  public $subsPercent;
  /**
   * @var float Какой процент ребиллов от подписок создаём.
   * Создаём только для тех пдп, для которых сегодня не было ребиллов и которые не отписаны ещё.
   * Количество ребиллов которые надо сгенерить считаем исходя из существующей конверсии
   * (соотношение сегодняшних ребиллов ко всем неотписавшимся пдп) и той, которая требуется
   * в этой опции.
   * Например всего 10к активных пдп без отписки. Сегодня создано 5к ребиллов. Значит если rebillsPercent=90, то
   * надо создать ещё 4к ребиллов на сегодня.
   * По-умолчанию 90.
   */
  public $rebillsPercent;
  /**
   * @var float Какой процент отписок делаем в день.
   * Создаём только для тех пдп, для которых сегодня не было отписок и которые не отписаны ещё.
   * Количество отписок которые надо сгенерить считаем исходя из существующей конверсии
   * (соотношение сегодняшних отписок ко всем неотписавшимся пдп) и той, которая требуется
   * в этой опции.
   * Например сейчас есть 6к пдп без отписок. Сегодня создано 1к отписок. Если offsPercent=50, то надо создать ещё
   * 2к отписок на сегодня.
   * По-умолчанию 50.
   */
  public $offsPercent;
  /**
   * @var float Какой процент жалоб от подписок создаём.
   * Например сейчас есть 10к пдп без жалоб. Сегодня создано 4 жалобы. Если complainsPercent=0.1, то надо создать ещё
   * 6 жалоб на сегодня.
   * По-умолчанию 0.1 (именно в смысле 0.1%)
   */
  public $complainsPercent;
  /**
   * @var string URL приемщика хитов в микросервисе. Например http://mcms-ml-handler.lc
   * По-умолчанию берём из конфига Yii::$app->params['pbHandlerUrl']
   * Если не указан и в конфиге, то берём http://mcms-ml-handler.lc
   */
  public $pbHandlerUrl;
  /**
   * @var string Секретное слово в приемщике КП.
   * По-умолчанию берём из конфига Yii::$app->params['kpSecret'].
   */
  public $kpSecret;
  /**
   * @var string Дата в формате 2017-11-24 по которой фильтруем хиты для генерации подписок.
   * Можно писать текстом, например "-2 days"
   * По-умолчанию "-2 days".
   */
  public $hitsDateFrom;
  /**
   * @var int Сколько хитов генерить.
   * По-умолчанию 1000.
   */
  public $hitsCount;
  /**
   * @var string URL приемщика хитов в микросервисе. Например http://mcms-api-handler.lc
   * По-умолчанию берём из конфига Yii::$app->params['hitHandlerUrl']
   * Если не указан и в конфиге, то берём http://mcms-api-handler.lc
   */
  public $hitHandlerUrl;

  /**
   * @var float погрешность в применении процентов и кол-ва хитов. Например если inaccuracyPercent=5, то
   * при hitsCount=1000 будет создано 1000+-50 хитов. А при subsPercent=10 будет создано 10+-0.5 процентов пдп.
   * По-умолчанию 0.
   */
  public $inaccuracyPercent;

  /**
   * @var array Список генератор => класс
   */
  private $generators = [
    'hits' => TrafficGenerator::class,
    'subs' => SubsGenerator::class,
    'rebills' => RebillsGenerator::class,
    'offs' => OffsGenerator::class,
    'complains' => ComplainsGenerator::class,
  ];

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return [
      'with',
      'hitsCount',
      'sourceId',
      'operatorId',
      'subsPercent',
      'rebillsPercent',
      'offsPercent',
      'complainsPercent',
      'kpSecret',
      'pbHandlerUrl',
      'hitHandlerUrl',
      'hitsDateFrom',
      'help',
      'interactive',
      'inaccuracyPercent',
    ];
  }

  /**
   * `php yii statistic/traffic-generator --help` чтобы увидеть все опции запуска.
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  public function actionIndex()
  {
    $exploded = explode(',', $this->with);
    $generators = array_filter($this->generators, function ($generatorKey) use ($exploded) {
      if ($this->with === self::WITH_ALL) {
        return true;
      }
      if (in_array($generatorKey, $exploded, true)) {
        return true;
      }
      return false;
    }, ARRAY_FILTER_USE_KEY);

    $config = new GeneratorConfig([
      'sourceId' => $this->sourceId,
      'operatorId' => $this->operatorId,
      'subsPercent' => $this->subsPercent,
      'rebillsPercent' => $this->rebillsPercent,
      'offsPercent' => $this->offsPercent,
      'complainsPercent' => $this->complainsPercent,
      'kpSecret' => $this->kpSecret,
      'pbHandlerUrl' => $this->pbHandlerUrl,
      'hitHandlerUrl' => $this->hitHandlerUrl,
      'hitsDateFrom' => $this->hitsDateFrom,
      'hitsCount' => $this->hitsCount,
      'inaccuracyPercent' => $this->inaccuracyPercent
    ]);

    $this->stdout('CONFIG ' . print_r($config->toArray(), true));

    if (!$this->interactive && !Console::confirm('Config is OK?', true)) {
      $this->stdout('Cancelled by user.' . PHP_EOL);
      return;
    }

    try {
      foreach ($generators as $key => $generatorClass) {
        $this->stdout(mb_strtoupper($key) . ' ...' . PHP_EOL);
        /** @var AbstractGenerator $generator */
        $generator = Yii::createObject($generatorClass, [$config]);
        $generator->execute();
      }
    } catch (Exception $exception) {
      $this->stdout($exception->getMessage() . PHP_EOL, Console::FG_RED);
      return;
    }

    $this->stdout('GENERATOR COMPLETED' . PHP_EOL);
  }
}
