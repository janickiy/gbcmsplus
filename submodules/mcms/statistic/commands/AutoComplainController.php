<?php

namespace mcms\statistic\commands;

use mcms\statistic\models\Complain;
use Yii;
use yii\console\Controller;
use yii\db\Expression;

/**
 * Этот крон нужен только для добавления жалоб которые не были добавлены через микросервис
 * Сейчас жалобы в микросервисе добавляются правильно
 * Class AutoComlainController
 * @package mcms\statistic\commands
 */
class AutoComplainController extends Controller
{
  const TYPE_AUTO_FAKE_TRANS_ID_PREFIX = 'autocomplain_';
  /** Кол-во секунд за которое считаем отписку 24 */
  public $delta24 = 24 * 60 * 60;
  /** Кол-во секунд за которое считаем отписку моментальной */
  public $deltaMoment = 15 * 60;
  /** Дата с который были созданы данные типы жалоб */
  public $dateFrom = '2017-10-20';

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return ['dateFrom', 'deltaMoment', 'delta24'];
  }

  /**
   * Добавляем моментальные жалобы которые не были созданы через микросервис
   */
  public function actionAdd()
  {
    $complainHits = $this->getInstantOffsHitIds();
    foreach ($complainHits as $complainHit => $isMoment) {

      $type = $isMoment ? Complain::TYPE_AUTO_MOMENT : Complain::TYPE_AUTO_24;
      $info = $this->getHitInformation($complainHit);
      echo 'Insert complain for hit ' . $complainHit . PHP_EOL;
      $this->insertComplain($info, $type);
    }
  }

  /**
   * Добавление моментальной жалобы
   * @param $info
   * @param $type
   */
  protected function insertComplain($info, $type)
  {
    Yii::$app->db->createCommand()->insert('complains', [
      'hit_id' => $info['id'],
      'trans_id' => self::TYPE_AUTO_FAKE_TRANS_ID_PREFIX . microtime(1),
      'time' => $info['time'],
      'date' => date('Y-m-d', $info['time']),
      'hour' => date('h', $info['time']),
      'landing_id' => $info['landing_id'],
      'source_id' => $info['source_id'],
      'operator_id' => $info['operator_id'],
      'platform_id' => $info['platform_id'],
      'landing_pay_type_id' => $info['landing_pay_type_id'],
      'provider_id' => $info['provider_id'],
      'country_id' => $info['country_id'],
      'stream_id' => $info['stream_id'],
      'user_id' => $info['user_id'],
      'phone' => $info['phone'],
      'created_at' => $info['time'],
      'updated_at' => $info['time'],
      'type' => $type,
    ])->execute();

  }


  /**
   * Возвращает хиты по которым нужно добавить моментальные жалобы
   * @return array
   */
  protected function getInstantOffsHitIds()
  {
    $result = [];
    $hits = (new \yii\db\Query())
      ->select(['off.hit_id', new Expression("IF(off.time <= (s.time + :deltaMoment), 1, 0) as is_moment")])
      ->from('subscription_offs off')
      ->innerJoin('subscriptions s', 's.hit_id=off.hit_id')
      ->leftJoin('complains c', 'c.hit_id=off.hit_id')
      ->where("off.date >= :dateFrom AND (off.time <= (s.time + :delta24) OR off.time <= (s.time + :deltaMoment)) AND c.id IS NULL",
        [':dateFrom' => $this->dateFrom, ':delta24' => $this->delta24, ':deltaMoment' => $this->deltaMoment])
      ->each();

    foreach ($hits as $hit) {
      $result[$hit['hit_id']] = !!$hit['is_moment'];
    }

    return $result;
  }

  /**
   * Информация по хиту для добавления жалобы
   * @param $id
   * @return array|bool
   */
  protected function getHitInformation($id)
  {
    return (new \yii\db\Query())
      ->select(['h.id', 'off.time','h.landing_id', 'h.source_id', 'h.operator_id', 'h.platform_id', 'h.landing_pay_type_id',
        'l.provider_id', 'o.country_id', 's.stream_id', 's.user_id', 'sub.phone'])
      ->from('hits h')
      ->innerJoin('hit_params hp', 'hp.hit_id=h.id')
      ->innerJoin('sources s', 's.id=h.source_id')
      ->innerJoin('landings l', 'l.id=h.landing_id')
      ->innerJoin('subscriptions sub', 'sub.hit_id=h.id')
      ->innerJoin('operators o', 'o.id=h.operator_id')
      ->innerJoin('subscription_offs off', 'off.hit_id=h.id')
      ->where(['h.id' => $id])
      ->one();
  }


}
