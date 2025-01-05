<?php
namespace mcms\statistic\components\traffic_generator\conversions_generators;

use Exception;
use mcms\common\output\OutputInterface;
use mcms\statistic\components\traffic_generator\AbstractGenerator;
use mcms\statistic\components\traffic_generator\GeneratorConfig;
use mcms\statistic\models\Cr;
use mcms\statistic\models\Hit;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Подписки: берём хиты, и создаем подписки (или единоразовые)
 * Количество пдп которые надо сгенерить считаем исходя из существующей конверсии и той,
 * которая требуется в свойстве @see GeneratorConfig::$subsPercent
 * То есть при необходимости создаем подписки. В противном случае не создаём ничего.
 * Создаем единоразовую или обычную пдп в зависимости от настроек ленд-оператора.
 */
class SubsGenerator extends AbstractGenerator
{
  /**
   * Запуск
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \yii\base\InvalidParamException
   * @throws Exception
   * @throws InvalidConfigException
   */
  public function execute()
  {
    if (!$this->cfg->pbHandlerUrl) {
      throw new InvalidConfigException("Не указан параметр pbHandlerUrl. Например 'pbHandlerUrl' => 'http://mcms-ml-handler.dev'");
    }

    if (!$this->cfg->hitsDateFrom) {
      throw new InvalidConfigException('Не указан параметр hitsDateFrom');
    }

    if (!$this->cfg->subsPercent) {
      throw new InvalidConfigException('Не указан параметр subsPercent');
    }

    $this->generateConversions($this->getNeedConversionsCount());
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

    $needCr = $this->randomizeWithInacurracy($this->cfg->subsPercent);

    $this->log('   need CR=' . Yii::$app->formatter->asDecimal($needCr) . '%', [OutputInterface::BREAK_AFTER]);

    if (!$currentCrModel->fullCount) {
      $this->log('   NO HITS AVAILABLE! (generate more hits to create more subs)', [OutputInterface::BREAK_AFTER]);
      return 0;
    }

    /** @var int $idealConversions Сколько должно быть конверсий. */
    /** @var int $needConversions Сколько надо ещё создать конверсий. */
    $idealConversions = (int)$needCr * $currentCrModel->fullCount / 100;
    $needConversions = $idealConversions - $currentCrModel->convertionsCount;
    $needConversions = $needConversions >= 0 ? $needConversions : 0;

    $this->log(
      "   $needConversions NEW SUBS OR ONETIMES SHOULD BE CREATED! (increase subsPercent to create more subs)",
      [OutputInterface::BREAK_AFTER]
    );
    return $needConversions;
  }

  /**
   * @return Cr Конверт кол-ва пдп (или onetime) к кол-ву хитов
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function getCurrentCr()
  {
    $q = (new Query())
      ->select([
        'conversions' => new Expression('SUM(IF(s.id OR onetime.id, 1, 0))'),
        'hits' => new Expression('COUNT(1)')
      ])
      ->from(['h' => 'hits'])
      ->leftJoin('subscriptions s', 'h.id = s.hit_id')
      ->leftJoin('onetime_subscriptions onetime', 'h.id = onetime.hit_id')
      ->andFilterWhere([
        '>=',
        'h.date',
        Yii::$app->formatter->asDate($this->cfg->hitsDateFrom, 'php:Y-m-d')
      ])
      ->andFilterWhere([
        'h.source_id' => $this->cfg->sourceId,
        'h.operator_id' => $this->cfg->operatorId,
      ]);
    $result = $q->one();

    $model = new Cr();
    $model->convertionsCount = ArrayHelper::getValue($result, 'conversions', 0);
    $model->fullCount = ArrayHelper::getValue($result, 'hits', 0);

    return $model;
  }

  /**
   * Хиты, для которых надо создать пдп. Лимит ограничивает выборку чтобы добиться нужного процента конверта
   * @param int $limit
   * @return Query
   * @throws \yii\base\InvalidParamException
   * @throws \yii\base\InvalidConfigException
   */
  private function getHitsQuery($limit = 0)
  {
    return (new Query())
      ->select([
        'h.id',
        'h.time',
        'sub_type' => 'sub_type.code',
        'lo.rebill_price_rub',
        'lo.rebill_price_usd',
        'lo.rebill_price_eur',
        'lo.default_currency_rebill_price',
        'lo.default_currency_id'
      ])
      ->from(['h' => 'hits'])
      ->leftJoin('subscriptions s', 'h.id = s.hit_id')
      ->leftJoin('onetime_subscriptions onetime', 'h.id = onetime.hit_id')
      ->innerJoin('landing_operators lo', 'lo.landing_id = h.landing_id AND lo.operator_id = h.operator_id')
      ->innerJoin('landing_subscription_types sub_type', 'sub_type.id = lo.subscription_type_id')
      ->andWhere(['s.id' => null])
      ->andWhere(['onetime.id' => null])
      ->andFilterWhere([
        'h.source_id' => $this->cfg->sourceId,
        'h.operator_id' => $this->cfg->operatorId,
      ])
      ->andFilterWhere([
        '>=',
        'h.date',
        Yii::$app->formatter->asDate($this->cfg->hitsDateFrom, 'php:Y-m-d')
      ])
      ->orderBy(['h.id' => SORT_DESC])
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
      $model->id = $hitArr['id'];
      $model->time = $hitArr['time'];
      $model->subType = $hitArr['sub_type'] === Hit::SUB_TYPE_ONETIME ? Hit::SUB_TYPE_ONETIME : Hit::SUB_TYPE_SUB;
      $model->rebillPriceRub = $hitArr['rebill_price_rub'];
      $model->rebillPriceUsd = $hitArr['rebill_price_usd'];
      $model->rebillPriceEur = $hitArr['rebill_price_eur'];
      $model->defaultCurrencyRebillPrice = $hitArr['default_currency_rebill_price'];
      $model->defaultCurrencyId = $hitArr['default_currency_id'];
      return $model;
    }, $hits);
  }

  /**
   * Создаёт подписки
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

    foreach ($this->getHitsQuery($needConversions)->batch() as $hits) {
      $hitsModels = $this->getHitsModels($hits);

      $subsToSend = [];
      $onetimesToSend = [];

      foreach ($hitsModels as $hit) {
        $postData = [
          'label1' => $hit->id,
          'phone' => mt_rand(70000000000, 79999999999),
          'transaction_id' => self::TRANS_ID_PREFIX . substr(md5($hit->id), 0, 8),
          'action_time' => $subTime = $hit->time + mt_rand(1, 600),
          'action_date' => Yii::$app->formatter->asDate($subTime, 'php:Y-m-d')
        ];

        if ($hit->subType === Hit::SUB_TYPE_SUB) {
          $postData['transaction_type'] = 'on';
          $subsToSend[] = $postData;
          continue;
        }

        // ЕСЛИ ЕДИНОРАЗОВАЯ ПДП
        $postData['transaction_type'] = 'onetime';
        $postData['sum'] = $hit->defaultCurrencyRebillPrice;
        $postData['currency'] = $hit->getDefaultCurrencyCode();
        $postData['default_sum'] = $hit->defaultCurrencyRebillPrice;
        $postData['default_currency'] = $hit->getDefaultCurrencyCode();
        $onetimesToSend[] = $postData;
      }

      $logMessages = [];

      if (count($subsToSend) && ($successCount = $this->sendConversions($subsToSend)) !== null) {
        $logMessages[] = "+$successCount NEW SUBS CREATED";
      }

      if (count($onetimesToSend) && ($successCount = $this->sendConversions($onetimesToSend)) !== null) {
        $logMessages[] = "+$successCount NEW ONETIME CREATED";
      }

      $this->log('   ' . implode(';', $logMessages), [OutputInterface::BREAK_AFTER]);
    }
  }
}
