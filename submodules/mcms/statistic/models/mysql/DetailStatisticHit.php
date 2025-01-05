<?php


namespace mcms\statistic\models\mysql;

use mcms\statistic\components\AbstractDetailStatistic;
use mcms\statistic\components\StatisticQuery;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DetailStatisticHit extends AbstractDetailStatistic
{
  const GROUP_NAME = 'hit';

  public $id;

  /**
   * Возвращает масстив с колонками фильтра
   * @return array
   */
  function getFilterFields()
  {
    return ['id'];
  }

  /**
   * @return array
   */
  function gridColumnLabels()
  {
    return [
      'date' => Yii::_t('statistic.statistic.date'),
      'is_unique' => Yii::_t('statistic.statistic.is_unique'),
      'is_tb' => Yii::_t('statistic.statistic.is_tb'),
      'stream' => Yii::_t('statistic.statistic.detail-stream-name'),
      'source' => Yii::_t('statistic.statistic.sources'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'landingPayType' => Yii::_t('statistic.statistic.landing_pay_types'),
      'referrer' => Yii::_t('statistic.statistic.referrer'),
      'userAgent' => Yii::_t('statistic.statistic.userAgent'),
      'email' => Yii::_t('statistic.statistic.email'),
      'ip' => Yii::_t('statistic.statistic.detail-ip'),
      'subid1' => Yii::_t('statistic.statistic.subid1'),
      'subid2' => Yii::_t('statistic.statistic.subid2'),
      'cid' => Yii::_t('statistic.statistic.cid'),
    ];
  }

  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('statistic.statistic.id'),
    ];
  }

  /**
   * Добавляет фильтрацию к запросу
   * @param Query $query
   * @return void
   */
  function handleFilters(Query &$query)
  {
    $query->andFilterWhere(['id', $this->id]);
  }

  /**
   * @inheritdoc
   */
  function findOne($recordId)
  {
    $query = (new StatisticQuery())
      ->select([
        'time' => 'st.time',
        'is_unique' => 'st.is_unique',
        'is_tb' => 'st.is_tb',
        'ip' => 'hp.ip',
        'referrer' => 'hp.referer',
        'userAgent' => 'hp.user_agent',
        'email' => 'u.email',
        'user_id' => 'u.id',
        'subid1' => 'hp.subid1',
        'subid2' => 'hp.subid2',
        'get_params' => 'hp.get_params',
      ])
      ->from(['st' => 'hits'])
      ->where(['st.id' => $recordId])
      ->innerJoin('hit_params hp', 'hp.hit_id = st.id');

    $this->addOriginalSourceJoin($query, $recordId);
    $query = $this->addQueryJoins($query);

    $record = $query->one();
    if (!$record) {
      return null;
    }

    // Вытаскиваем cid из get_params
    parse_str($record['get_params'], $getParams);
    $record['cid'] = ArrayHelper::getValue($getParams, 'cid');

    return $record;
  }

  protected function getJoinFields()
  {
    // TRICKY При изменении, нужно учесть, что метод переопределен в DetailStatisticHit
    return [
      'operator' => 'st.operator_id',
      'landing' => 'st.landing_id',
      'source' => 'correct.source_id',
      'platform' => 'st.platform_id',
      'payType' => 'st.landing_pay_type_id',
      'stream' => 'source.stream_id',
      'country' => 'operator.country_id',
      'user' => 'source.user_id',
    ];
  }

  /**
   * Джоин для получения оригинального источника
   * @param Query $query
   * @param int $recordId id хита
   */
  protected function addOriginalSourceJoin(&$query, $recordId)
  {
    $newQuery = (new Query())
      ->select([
        'source_id' => 'IFNULL(ss.source_id, h.source_id)',
        'hit_id' => 'h.id'
      ])
      ->from(['h' => 'hits'])
      ->leftJoin('sold_subscriptions ss', 'ss.hit_id = h.id')
      ->where([
        'h.id' => $recordId
      ]);

    $query->leftJoin(['correct' => $newQuery], 'correct.hit_id = st.id');
  }

  /**
   * @inheritdoc
   */
  protected function isCpaVisible($gridRow){}
}