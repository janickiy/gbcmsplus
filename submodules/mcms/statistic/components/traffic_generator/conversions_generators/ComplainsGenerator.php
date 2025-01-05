<?php
namespace mcms\statistic\components\traffic_generator\conversions_generators;

use mcms\common\output\OutputInterface;
use mcms\statistic\components\traffic_generator\AbstractGenerator;
use mcms\statistic\models\Cr;
use mcms\statistic\models\Hit;
use mcms\statistic\models\Sub;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Жалобы: берём подписки и создаем жалобы за сегодняшний день.
 * Создаём только для тех пдп, для которых сегодня не было жалоб.
 * Количество жалоб которые надо сгенерить считаем исходя из существующей конверсии
 * (соотношение сегодняшних жалоб ко всем пдп) и той, которая требуется
 * в свойстве @see GeneratorConfig::$complainsPercent
 * То есть при необходимости создаем жалобы. В противном случае не создаём ничего.
 */
class ComplainsGenerator extends AbstractGenerator
{

  /**
   * @var array тексты жалоб
   */
  private static $descriptions = [
    'Nulla at metus viverra, mollis urna sed, bibendum massa',
    'Aenean ut eros volutpat, pellentesque erat vitae, lobortis ante',
    'Mauris id elit convallis, ornare tellus a, suscipit purus',
    'Proin auctor ex tristique velit vestibulum lobortis',
    'Aenean laoreet est non neque pharetra, quis ultricies ligula accumsan',
    'Aenean et metus volutpat, fermentum velit interdum, dignissim turpis',
    'Quisque ac tellus commodo, tristique arcu eu, venenatis nisi',
    'Nulla a tellus consequat, finibus tortor id, commodo leo',
    'Sed imperdiet nisi quis felis consequat, in convallis tortor accumsan',
    'Cras facilisis tellus sit amet magna blandit tempor',
    'Aenean ac orci eu neque condimentum posuere',
    'Mauris non tellus nec tortor aliquet pharetra in id lacus',
    'Ut id orci sagittis, scelerisque nunc at, ultricies tellus',
    'Fusce gravida dolor et pulvinar varius',
    'Nam ullamcorper odio nec augue dignissim, ac varius nulla hendrerit',
    'Praesent vel felis eu erat mollis consectetur',
    'Quisque ut mi eu nulla tempor varius',
    'Integer nec ex ac sapien interdum molestie et quis dolor',
    'Aliquam sed tortor eu sapien molestie congue quis eu magna',
  ];

  /**
   * @inheritdoc
   * @throws \yii\base\InvalidParamException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \yii\base\InvalidConfigException
   */
  public function execute()
  {
    if (!$this->cfg->pbHandlerUrl) {
      throw new InvalidConfigException("Не указан параметр pbHandlerUrl. Например 'pbHandlerUrl' => 'http://mcms-ml-handler.dev'");
    }

    if (!$this->cfg->complainsPercent) {
      throw new InvalidConfigException('Не указан параметр complainsPercent');
    }

    $this->generateConversions($this->getNeedConversionsCount());
  }

  /**
   * @return Cr Отношение кол-ва сегодняшних жалоб к кол-ву ВСЕХ подписок
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function getCurrentCr()
  {
    $q = (new Query())
      ->select([
        'todayComplains' => new Expression('COUNT(DISTINCT c.id)'), // сегодняшние жалобы
        'activeSubs' => new Expression('COUNT(DISTINCT s.id)') // подписки без жалобы, либо с сегодняшней жалобой
      ])
      ->from(['s' => 'subscriptions'])
      ->leftJoin('complains c', 'c.hit_id = s.hit_id')
      ->andWhere([
        'or',
        ['c.id' => null],
        ['c.date' => Yii::$app->formatter->asDate('today', 'php:Y-m-d')]
      ])
      ->andFilterWhere([
        '>=',
        's.date',
        Yii::$app->formatter->asDate($this->cfg->hitsDateFrom, 'php:Y-m-d')
      ])
      ->andFilterWhere([
        's.source_id' => $this->cfg->sourceId,
        's.operator_id' => $this->cfg->operatorId,
      ]);
    $result = $q->one();

    $model = new Cr();
    $model->convertionsCount = ArrayHelper::getValue($result, 'todayComplains', 0);
    $model->fullCount = ArrayHelper::getValue($result, 'activeSubs', 0);

    return $model;
  }

  /**
   * @param int $limit
   * @return Query
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function getSubsQuery($limit = 0)
  {
    return (new Query())
      ->select([
        's.hit_id',
        'subTime' => 's.time',
      ])
      ->from(['s' => 'subscriptions'])
      ->leftJoin('complains c', 'c.hit_id = s.hit_id')
      ->andWhere(['c.id' => null])
      ->andFilterWhere([
        's.source_id' => $this->cfg->sourceId,
        's.operator_id' => $this->cfg->operatorId,
      ])
      ->andFilterWhere([
        '>=',
        's.date',
        Yii::$app->formatter->asDate($this->cfg->hitsDateFrom, 'php:Y-m-d')
      ])
      ->limit($limit);
  }

  /**
   * конвертим данные из БД в виде модели Hit
   * @param $hits
   * @return Hit[]
   */
  private function getHitsModels($hits)
  {
    return array_map(function ($hitArr) {
      $model = new Hit();
      $model->id = $hitArr['hit_id'];
      $sub = new Sub();
      $sub->time = $hitArr['subTime'];
      $model->sub = $sub;
      return $model;
    }, $hits);
  }

  /**
   * Сколько надо создать конверсий?
   * @return int
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function getNeedConversionsCount()
  {
    $currentCrModel = $this->getCurrentCr();

    // getRate возвращает просто отношение двух чисел. Умножение на 100 чтоб получить в процентах
    $currentCr = $currentCrModel->getRate() * 100;

    $this->log(
      sprintf(
        '   current CR=%s%% (%s/%s)',
        Yii::$app->formatter->asDecimal($currentCr),
        $currentCrModel->convertionsCount,
        $currentCrModel->fullCount
      ),
      [OutputInterface::BREAK_AFTER]
    );

    $needCr = $this->randomizeWithInacurracy($this->cfg->complainsPercent);

    $this->log(
      '   need CR=' . Yii::$app->formatter->asDecimal($needCr) . '%',
      [OutputInterface::BREAK_AFTER]
    );

    if (!$currentCrModel->fullCount) {
      $this->log('   NO SUBS AVAILABLE! (generate more subs to create more complains)', [OutputInterface::BREAK_AFTER]);
      return 0;
    }

    /** @var int $idealConversions Сколько должно быть конверсий. */
    /** @var int $needConversions Сколько надо ещё создать конверсий. */
    $idealConversions = (int)$needCr * $currentCrModel->fullCount / 100;
    $needConversions = $idealConversions - $currentCrModel->convertionsCount;
    $needConversions = $needConversions >= 0 ? $needConversions : 0;

    $this->log(
      "   $needConversions NEW COMPLAINS SHOULD BE CREATED! (increase complainsPercent to create more complains)",
      [OutputInterface::BREAK_AFTER]
    );

    return $needConversions;
  }

  /**
   * Генерим конверсии
   * @param int $needConversions
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function generateConversions($needConversions)
  {
    if (!$needConversions) {
      return;
    }

    foreach ($this->getSubsQuery($needConversions)->batch() as $hits) {
      $hitsModels = $this->getHitsModels($hits);

      $complainsToSend = [];

      foreach ($hitsModels as $hit) {
        // проверяем чтобы время жалобы было всяко больше времени подписки
        $complainTime = time() + mt_rand(1, 600);
        $complainTime = $complainTime >= $hit->sub->time ? $complainTime : $hit->sub->time + mt_rand(1, 3600);

        $typeId = mt_rand(1, 6);
        $types = [
          1 => 'complaint',
          2 => 'complaint',
          3 => 'complaint',
          4 => 'complaint',
          5 => 'call',
          6 => 'call_mno',
        ];

        $complainsToSend[] = [
          'label1' => $hit->id,
          'transaction_id' => self::TRANS_ID_PREFIX . substr(md5($hit->id . $complainTime), 0, 8),
          'complaint_description' => self::$descriptions[array_rand(self::$descriptions)],
          'complaint_type' => $types[$typeId], // todo возможно потом появится модель на это
          'transaction_type' => '', // todo выпилить как только перестанет быть нужен.
          'action_time' => $complainTime,
          'action_date' => Yii::$app->formatter->asDate($complainTime, 'php:Y-m-d'),
        ];
      }

      if (count($complainsToSend) && ($successCount = $this->sendConversions($complainsToSend, '/kp-complains')) !== null) {
        $this->log("   +$successCount NEW COMPLAINS CREATED", [OutputInterface::BREAK_AFTER]);
      }
    }
  }
}
