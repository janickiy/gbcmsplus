<?php

namespace mcms\partners\components\subidStat;

use mcms\common\helpers\ArrayHelper;
use mcms\partners\components\subidStat\query\SdbQuery;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Connection;

/**
 */
class Fetch extends BaseFetch
{
  /** @var string */
  private $db;

  /**
   * @inheritdoc
   * @return ActiveDataProvider|ArrayDataProvider
   * @throws \yii\base\InvalidConfigException
   */
  public function getDataProvider($config = [])
  {
    $dataProvider = new ActiveDataProvider($config);

    $this->db = ArrayHelper::getValue($config, 'db', 'db');

    if (!$this->getFormModel()->validate()) {
      return new ArrayDataProvider();
    }

    if (!$this->isStatisticUserTableExists()) {
      return new ArrayDataProvider();
    }

    $dataProvider->query = Yii::createObject([
      'class' => $this->getQueryClass(),
      'formModel' => $this->getFormModel(),
    ]);

    $dataProvider->query->makePrepare();

    $dataProvider->setSort([
      'attributes' => [
        'subid1',
        'subid2',
        'hits',
        'uniques',
        'tb',
        'accepted',
        'revshare_ons',
        'revshare_offs',
        'revshare_ratio',
        'revshare_rebills',
        'revshare_profit',
        'cpa_ons',
        'cpa_ecpm',
        'cpa_ratio',
        'cpa_profit',
        'total_profit',
      ]
    ]);

    return $dataProvider;
  }

  /**
   * Список класснеймов для получения инфы
   * @return string
   */
  protected function getQueryClass()
  {
    return SdbQuery::class;
  }

  /**
   * @return bool
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\db\Exception
   */
  private function isStatisticUserTableExists()
  {
    /** @var Connection $connection */
    $connection = Yii::$app->get($this->db);
    $userId = Yii::$app->user->id;
    return (bool)$connection->createCommand("SHOW TABLES LIKE 'statistic_user_$userId';")->queryScalar();
  }
}
