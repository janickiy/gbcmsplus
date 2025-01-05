<?php

namespace mcms\statistic\models\mysql;

use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\AbstractStatistic;
use mcms\statistic\components\StatisticQuery;
use mcms\user\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class Referrals
 * @package mcms\statistic\models\mysql
 */
class Referrals extends BaseReferrals
{
  const STATISTIC_NAME = 'referrals';
  protected $includeReferralsCount = true;
  protected $includeReferralsPercent = true;

  /**
   * @inheritdoc
   */
  public function getQuery(array $select = [])
  {
    return parent::getQueryInternal('st.user_id', 'st.user_id', $select);
  }
}