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
 * Ребиллы: берём подписки и создаем ребиллы за сегодняшний день.
 * Создаём только для тех пдп, для которых сегодня не было ребиллов и которые не отписаны ещё.
 * Количество ребиллов которые надо сгенерить считаем исходя из существующей конверсии
 * (соотношение сегодняшних ребиллов ко всем неотписавшимся пдп) и той, которая требуется
 * в свойстве @see GeneratorConfig::$rebillsPercent
 * То есть при необходимости создаем ребиллы. В противном случае не создаём ничего.
 */
class RebillsGenerator extends AbstractGenerator
{

  public function execute()
  {
    if (!$this->cfg->pbHandlerUrl) {
      throw new InvalidConfigException("Не указан параметр pbHandlerUrl. Например 'pbHandlerUrl' => 'http://mcms-ml-handler.dev'");
    }

    if (!$this->cfg->rebillsPercent) {
      throw new InvalidConfigException('Не указан параметр rebillsPercent');
    }

    $this->generateConversions($this->getNeedConversionsCount());
  }

  /**
   * @return Cr Отношение кол-ва сегодняшних ребиллов к кол-ву ВСЕХ подписок без отписок
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function getCurrentCr()
  {
    $q = (new Query())
      ->select([
        'todayRebills' => new Expression('COUNT(DISTINCT sr.id)'), // сегодняшние ребиллы
        'activeSubs' => new Expression('COUNT(DISTINCT s.id)') // активные подписки
      ])
      ->from(['s' => 'subscriptions'])
      ->leftJoin('subscription_offs off', 'off.hit_id = s.hit_id')
      ->leftJoin('subscription_rebills sr', 'sr.hit_id = s.hit_id AND sr.date = :today', [
        'today' => Yii::$app->formatter->asDate('today', 'php:Y-m-d')
      ])
      ->andWhere(['off.id' => null])
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
    $model->convertionsCount = ArrayHelper::getValue($result, 'todayRebills', 0);
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
        'lo.rebill_price_rub',
        'lo.rebill_price_usd',
        'lo.rebill_price_eur',
        'lo.default_currency_rebill_price',
        'lo.default_currency_id'
      ])
      ->from(['s' => 'subscriptions'])
      ->leftJoin('subscription_offs off', 'off.hit_id = s.hit_id')
      ->leftJoin('subscription_rebills sr', 'sr.hit_id = s.hit_id AND sr.date = :today', [
        'today' => Yii::$app->formatter->asDate('today', 'php:Y-m-d')
      ])
      ->innerJoin('landing_operators lo', 'lo.landing_id = s.landing_id AND lo.operator_id = s.operator_id')
      ->andWhere(['off.id' => null])
      ->andWhere(['sr.id' => null])
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
      $model->rebillPriceRub = $hitArr['rebill_price_rub'];
      $model->rebillPriceUsd = $hitArr['rebill_price_usd'];
      $model->rebillPriceEur = $hitArr['rebill_price_eur'];
      $model->defaultCurrencyRebillPrice = $hitArr['default_currency_rebill_price'];
      $model->defaultCurrencyId = $hitArr['default_currency_id'];
      $sub = new Sub();
      $sub->time = $hitArr['subTime'];
      $model->sub = $sub;
      return $model;
    }, $hits);
  }

  /**
   * Сколько конверсий надо создать?
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

    $needCr = $this->randomizeWithInacurracy($this->cfg->rebillsPercent);

    $this->log('   need CR=' . Yii::$app->formatter->asDecimal($needCr) . '%', [OutputInterface::BREAK_AFTER]);

    if (!$currentCrModel->fullCount) {
      $this->log('   NO SUBS AVAILABLE! (generate more subs to create more rebills)', [OutputInterface::BREAK_AFTER]);
      return 0;
    }

    /** @var int $idealConversions Сколько должно быть конверсий. */
    /** @var int $needConversions Сколько надо ещё создать конверсий. */
    $idealConversions = (int)$needCr * $currentCrModel->fullCount / 100;
    $needConversions = $idealConversions - $currentCrModel->convertionsCount;
    $needConversions = $needConversions >= 0 ? $needConversions : 0;

    $this->log(
      "   $needConversions NEW REBILLS SHOULD BE CREATED! (increase rebillsPercent to create more subs)",
      [OutputInterface::BREAK_AFTER]
    );

    return $needConversions;
  }

  /**
   * Создать конверсии
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

      $rebillsToSend = [];

      foreach ($hitsModels as $hit) {
        // проверяем чтобы время ребилла было всяко больше времени подписки
        $rebillTime = time() + mt_rand(1, 600);
        $rebillTime = $rebillTime >= $hit->sub->time ? $rebillTime : $hit->sub->time + mt_rand(1, 600);

        $rebillsToSend[] = [
          'label1' => $hit->id,
          'transaction_type' => 'rebill',
          'action_time' => $rebillTime,
          'transaction_id' => self::TRANS_ID_PREFIX . substr(md5($hit->id . $rebillTime), 0, 8),
          'action_date' => Yii::$app->formatter->asDate($rebillTime, 'php:Y-m-d'),
          'sum' => $hit->defaultCurrencyRebillPrice,
          'currency' => $hit->getDefaultCurrencyCode(),
          'default_sum' => $hit->defaultCurrencyRebillPrice,
          'default_currency' => $hit->getDefaultCurrencyCode(),
        ];
      }

      if (count($rebillsToSend) && ($successCount = $this->sendConversions($rebillsToSend)) !== null) {
        $this->log("   +$successCount NEW REBILLS CREATED", [OutputInterface::BREAK_AFTER]);
      }
    }
  }
}
