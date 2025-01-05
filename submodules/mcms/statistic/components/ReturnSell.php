<?php

namespace mcms\statistic\components;

use mcms\payments\models\UserBalancesGroupedByDay;
use mcms\statistic\components\queue\postbacks\Payload;
use mcms\statistic\components\queue\postbacks\Worker;
use mcms\statistic\models\Complain;
use mcms\statistic\models\Postback;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\db\Query;

/**
 * Автоматический возврат подписки партнеру после поступления жалобы
 */
class ReturnSell extends Component
{
  public $hitId;
  public $subscription;

  public function init()
  {
    $this->subscription = $this->findSubscription();
    parent::init();
  }

  /**
   * Поиск проданной подписки
   * @return array|bool
   */
  public function findSubscription()
  {
    $query = (new Query())
      ->select([
        'user_id',
        'operator_id',
        'landing_id',
        'date',
        'time'
      ])
      ->from(['st' => 'sold_subscriptions'])
      ->where(['st.hit_id' => $this->hitId, 'st.is_visible_to_partner' => 0]);

    return $query->one();
  }

  /**
   * Получение профитов которые будут указаны у возвращаемой подписки
   * @return array|bool
   */
  public function getProfits()
  {
    $query = (new Query())
      ->select([
        'profit_rub',
        'profit_eur',
        'profit_usd',
      ])
      ->from('sold_subscriptions')
      ->where([
        'user_id' => $this->subscription['user_id'],
        'operator_id' => $this->subscription['operator_id'],
        'landing_id' => $this->subscription['landing_id'],
        'is_visible_to_partner' => 1])
      ->orderBy('date DESC');

    return $query->one();
  }

  /**
   * Сделать подписку видимой для партнера
   * @throws \yii\db\Exception
   */
  public function setVisibleToPartner()
  {
    $profits = $this->getProfits();
    if (!$profits) {
      return false;
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
      Yii::$app->db->createCommand()
        ->update('sold_subscriptions', [
          'profit_rub' => $profits['profit_rub'],
          'profit_eur' => $profits['profit_eur'],
          'profit_usd' => $profits['profit_usd'],
          'is_visible_to_partner' => 1,
        ], ['hit_id' => $this->hitId])
        ->execute();

      //нужно пересчитать баланс партнера за день выкупа возвращенной подписки
      (new RecalcStatJob($this->subscription['time'], 'return_sell_hit_id_' . $this->hitId))->addRecalcStatJob();

      $transaction->commit();

      //Отправляем постбек по проданной подписке
      Yii::$app->queue->push(
        Worker::CHANNEL_NAME,
        new Payload([
          'hitIds' => $this->hitId,
          'type' => Postback::TYPE_SOLD_SUBSCRIPTION,
        ])
      );

      //Отправляем постбек по жалобе с задержкой
      Yii::$app->queue->push(
        Worker::CHANNEL_NAME,
        new Payload([
          'hitIds' => $this->hitId,
          'type' => Postback::TYPE_COMPLAIN,
        ]),
        Complain::getPostbackDelay()
      );
    } catch (Exception $e) {
      $transaction->rollBack();
      Yii::error($e->getMessage(), __METHOD__);

      return false;
    } catch (\Exception $e) {
      Yii::error(Worker::CHANNEL_NAME . ' worker exception! ' . $e->getMessage());
      return false;
    }

    return true;
  }
}
