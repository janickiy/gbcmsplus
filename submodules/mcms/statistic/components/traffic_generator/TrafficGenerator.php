<?php
namespace mcms\statistic\components\traffic_generator;

use Exception;
use mcms\common\output\OutputInterface;
use yii\base\InvalidConfigException;
use yii\db\Query;

/**
 * Простенький генератор отправляет запрос на микросервис, имитируя тем самым трафик
 */
class TrafficGenerator extends AbstractGenerator
{

  private $sourceOperators;

  /**
   * @throws Exception
   */
  public function execute()
  {
    if (!$this->cfg->hitHandlerUrl) {
      throw new InvalidConfigException("Не указан параметр hitHandlerUrl. Например 'hitHandlerUrl' => 'http://mcms-api-handler.dev'");
    }

    if (count($this->getSourceOperators()) === 0) {
      $this->log('   NO SOURCE_LANDING_OPERATOR IS MATCHING ... ');
      return;
    }

    $maxHitsCount = (int) $this->randomizeWithInacurracy($this->cfg->hitsCount);

    $this->log('   ');
    for ($hitsCounter = 1; $hitsCounter <= $maxHitsCount; $hitsCounter++) {
      $randomSourceOperator = $this->getRandomSourceOperator();

      $hitUrl = sprintf(
        '%s/?hash=%s&debug=yes&op=%s&l1=%s&l2=%s&subid1=%s&subid2=%s',
        rtrim($this->cfg->hitHandlerUrl, '/'),
        $randomSourceOperator['hash'],
        $randomSourceOperator['operator_id'],
        mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2),
        mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2),
        mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2),
        mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2) . ':' . mt_rand(1, 2)
      );
      $result = $this->sendRequest($hitUrl);

      if (!preg_match("/\[hit_id\]\s\=\>\s(\d+)\n/isU", $result)) {
        $this->log("Hit ID is not found in response, check it: $hitUrl", [OutputInterface::BREAK_AFTER]);
      }

      if ($hitsCounter % 100 === 0) {
        $this->log(" | $hitsCounter/{$maxHitsCount} done");
      }
    }

    if ($hitsCounter % 100 !== 0) { // иначе мы только каждые 100 хитов показываем в консоли. Надо показать оставшиеся
      $hitsCounter--;
      $this->log(" | $hitsCounter/{$maxHitsCount} done", [OutputInterface::BREAK_AFTER]);
    }
  }


  /**
   * @return array
   */
  private function getSourceOperators()
  {
    if ($this->sourceOperators) {
      return $this->sourceOperators;
    }

    $q = (new Query())
      ->select(['hash', 'operator_id'])
      ->from(['sol' => 'sources_operator_landings'])
      ->leftJoin('sources', 'sources.id = sol.source_id')
      ->andWhere([
        'sources.status' => 1,
      ])
      ->andFilterWhere([
        'sol.source_id' => $this->cfg->sourceId,
        'sol.operator_id' => $this->cfg->operatorId,
      ])
      ->groupBy(['source_id', 'operator_id']);

    $this->sourceOperators = $q->all();

    return $this->sourceOperators;
  }

  /**
   * @return array
   */
  private function getRandomSourceOperator()
  {
    return $this->getSourceOperators()[array_rand($this->getSourceOperators())];
  }
}
