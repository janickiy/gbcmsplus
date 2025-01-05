<?php

namespace mcms\statistic\components\api;

use mcms\common\module\api\ApiResult;
use mcms\statistic\models\mysql\UserDayGroupStatistic as UserDayGroupStatisticModel;
use mcms\statistic\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class UserDayGroupStatistic extends ApiResult
{
  const CACHE_KEY_ALIVE_SUBSCRIPTIONS = 'cache_key_alive_subscriptions_';
  const CACHE_KEY_ALIVE_SUBSCRIPTIONS_DURATION = 600;

  /** @var \mcms\statistic\models\mysql\UserDayGroupStatistic */
  private $model;
  /** @var Module */
  private $module;

  public function init($params = [])
  {
    $this->model = new UserDayGroupStatisticModel();
    $this->filterData($params);

    $this->model->attributes = $params;

    $this->module = Yii::$app->getModule('statistic');
    return $this;
  }

  public function setAttributes($attributes)
  {
    $this->model->attributes = $attributes;
  }

  public function getByDateRange()
  {
    $this->setDataProvider($this->model->getByDateRange());
    $this->setResultTypeArray();

    return $this;
  }

  public function getSum($field) {
    return $this->model->getSum($field);
  }

  /**
   * @return $this
   */
  public function getTopPartners()
  {
    $dataProvider = new ActiveDataProvider([
      'query' => $this->model->getTopPartnersQuery(),
      'pagination' => [
        'pageSize' => 10
      ]
    ]);
    $this->setDataProvider($dataProvider);
    return $this;
  }

  /**
   * Получаем кол-во "живых" подписок пользователя
   * @return false|null|string
   */
  public function getPartnersAliveSubscriptions()
  {
    $data = Yii::$app->cache->get(self::CACHE_KEY_ALIVE_SUBSCRIPTIONS . $this->model->userId);
    if ($data !== false) {
      return $data;
    }

    $days = $this->module->getCountDaysAliveSubsCalc();
    $daysMinus = $days - 1;
    //Количество подписок с ребилами за вчера и сегодня
    $countFromYesterdaySubsWithRebills = (new Query())
      ->select([
        'COUNT(DISTINCT sr.hit_id)',
      ])
      ->from(['s' => 'subscriptions'])
      ->leftJoin(['sr' => 'subscription_rebills'], 'sr.hit_id = s.hit_id')
      ->leftJoin(['sor' => 'sources'], 's.source_id = sor.id')
      ->andWhere(['>=', 'sr.time', new Expression('UNIX_TIMESTAMP(:date)', [':date' => Yii::$app->formatter->asDate("-{$daysMinus} days", 'php:Y-m-d')])])
      ->andWhere(['sor.user_id' => $this->model->userId])
      ->andWhere(['s.is_cpa' => 0])
      ->scalar();

    //Количество отписок по ребиллам за вчера и сегодня
    $countOffsForFromYesterdaySubsWithRebills = (new Query())
      ->select([
        'COUNT(DISTINCT sr.hit_id)',
      ])
      ->from(['so' => 'subscription_offs'])
      ->leftJoin(['sr' => 'subscription_rebills'], 'sr.hit_id = so.hit_id')
      ->leftJoin(['sor' => 'sources'], 'sr.source_id = sor.id')
      ->andWhere(['>=', 'sr.time', new Expression('UNIX_TIMESTAMP(:date)', [':date' => Yii::$app->formatter->asDate("-{$daysMinus} days", 'php:Y-m-d')])])
      ->andWhere(['so.is_cpa' => 0])
      ->andWhere(['sor.user_id' => $this->model->userId])->scalar();

    //Количество подписок без ребилов за вчера и сегодня
    $countSubsWithoutRebillsFromYesterdey = (new Query())
      ->select([
        'COUNT(s.hit_id)',
      ])
      ->from(['s' => 'subscriptions'])
      ->leftJoin(['sr' => 'subscription_rebills'], 'sr.hit_id = s.hit_id')
      ->leftJoin(['sor' => 'sources'], 's.source_id = sor.id')
      ->andWhere('sr.id IS NULL')
      ->andWhere(['s.is_cpa' => 0])
      ->andWhere(['>=', 's.date', Yii::$app->formatter->asDate("-{$daysMinus} days", 'php:Y-m-d')])
      ->andWhere(['sor.user_id' => $this->model->userId])
      ->scalar();

    //Количество отписок по подпискам без ребилов за вчера и сегодня
    $countOffsForSubsWithoutRebillsFromYesterdey = (new Query())
      ->select([
        'COUNT(so.hit_id)',
      ])
      ->from(['so' => 'subscription_offs'])
      ->leftJoin(['s' => 'subscriptions'], 's.hit_id = so.hit_id')
      ->leftJoin(['sr' => 'subscription_rebills'], 'sr.hit_id = s.hit_id')
      ->leftJoin(['sor' => 'sources'], 'so.source_id = sor.id')
      ->andWhere('sr.id IS NULL')
      ->andWhere(['so.is_cpa' => 0])
      ->andWhere(['>=', 's.date', Yii::$app->formatter->asDate("-{$daysMinus} days", 'php:Y-m-d')])
      ->andWhere(['sor.user_id' => $this->model->userId])
      ->scalar();


    $count = $countFromYesterdaySubsWithRebills - $countOffsForFromYesterdaySubsWithRebills
      + $countSubsWithoutRebillsFromYesterdey - $countOffsForSubsWithoutRebillsFromYesterdey;

    $data = ($count && $count > 0)
      ? $count
      : 0;

    Yii::$app->cache->set(
      self::CACHE_KEY_ALIVE_SUBSCRIPTIONS . $this->model->userId,
      $data,
      self::CACHE_KEY_ALIVE_SUBSCRIPTIONS_DURATION
    );
    return $data;
  }
}
