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
 * Отписки: берём подписки и создаем отписки за сегодняшний день.
 * Создаём только для тех пдп, для которых сегодня не было отписок и которые не отписаны ещё.
 * Количество отписок которые надо сгенерить считаем исходя из существующей конверсии
 * (соотношение сегодняшних отписок ко всем неотписавшимся пдп) и той, которая требуется
 * в свойстве @see GeneratorConfig::$offsPercent
 * То есть при необходимости создаем отписки. В противном случае не создаём ничего.
 */
class OffsGenerator extends AbstractGenerator
{

  public function execute()
  {
    if (!$this->cfg->pbHandlerUrl) {
      throw new InvalidConfigException("Не указан параметр pbHandlerUrl. Например 'pbHandlerUrl' => 'http://mcms-ml-handler.dev'");
    }

    if (!$this->cfg->offsPercent) {
      throw new InvalidConfigException('Не указан параметр offsPercent');
    }

    $this->generateConversions($this->getNeedConversionsCount());
  }

  /**
   * @return Cr Отношение кол-ва сегодняшних отписок к кол-ву подписок без отписки
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function getCurrentCr()
  {
    $q = (new Query())
      ->select([
        'todayOffs' => new Expression('COUNT(DISTINCT off.id)'), // сегодняшние отписки
        'activeSubs' => new Expression('COUNT(DISTINCT s.id)') // подписки без отписки, либо с сегодняшней отпиской
      ])
      ->from(['s' => 'subscriptions'])
      ->leftJoin('subscription_offs off', 'off.hit_id = s.hit_id')
      ->andWhere([
        'or',
        ['off.id' => null],
        ['off.date' => Yii::$app->formatter->asDate('today', 'php:Y-m-d')]
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
    $model->convertionsCount = ArrayHelper::getValue($result, 'todayOffs', 0);
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
      ->leftJoin('subscription_offs off', 'off.hit_id = s.hit_id')
      ->andWhere(['off.id' => null])
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

    $needCr = $this->randomizeWithInacurracy($this->cfg->offsPercent);

    $this->log('   need CR=' . Yii::$app->formatter->asDecimal($needCr) . '%', [OutputInterface::BREAK_AFTER]);

    if (!$currentCrModel->fullCount) {
      $this->log('   NO SUBS AVAILABLE! (generate more subs to create more offs)', [OutputInterface::BREAK_AFTER]);
      return 0;
    }

    /** @var int $idealConversions Сколько должно быть конверсий. */
    /** @var int $needConversions Сколько надо ещё создать конверсий. */
    $idealConversions = (int)$needCr * $currentCrModel->fullCount / 100;
    $needConversions = $idealConversions - $currentCrModel->convertionsCount;
    $needConversions = $needConversions >= 0 ? $needConversions : 0;

    $this->log(
      "   $needConversions NEW OFFS SHOULD BE CREATED! (increase offsPercent to create more offs)",
      [OutputInterface::BREAK_AFTER]
    );

    return $needConversions;
  }

  /**
   * Генерим конверсии
   * @param $needConversions
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

      $offsToSend = [];

      foreach ($hitsModels as $hit) {
        // проверяем чтобы время ребилла было всяко больше времени подписки
        // todo проверять время последнего ребилла
        $offTime = time() + mt_rand(1, 600);
        $offTime = $offTime >= $hit->sub->time ? $offTime : $hit->sub->time + mt_rand(1, 3600);

        $offsToSend[] = [
          'label1' => $hit->id,
          'transaction_type' => 'off',
          'action_time' => $offTime,
          'transaction_id' => self::TRANS_ID_PREFIX . substr(md5($hit->id . $offTime), 0, 8),
          'action_date' => Yii::$app->formatter->asDate($offTime, 'php:Y-m-d'),
        ];
      }

      if (count($offsToSend) && ($successCount = $this->sendConversions($offsToSend)) !== null) {
        $this->log("   +$successCount NEW OFFS CREATED", [OutputInterface::BREAK_AFTER]);
      }
    }
  }
}
