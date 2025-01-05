<?php

namespace mcms\promo\components\api;

use Yii;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\Country;
use mcms\promo\models\Operator;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingPayType;
use yii\caching\TagDependency;
use yii\db\Query;

class GetCachedLandingPayTypes extends ApiResult
{

  const CACHE_LANDING_PAY_METHODS_KEY = 'mcms.partners.promo.paytypes';
  const DURATION = 1800;

  protected $data;

  protected $cacheTags = ['country', 'operator', 'landing'];

  public function init($params = [])
  {

  }

  public function getResult()
  {
    if (!($this->data = Yii::$app->getCache()->get(self::CACHE_LANDING_PAY_METHODS_KEY))) {
      $this->data = [
        'country' => [],
        'operator' => [],
        'paytypes' => [],
      ];

      $ctTable = Country::tableName();
      $lTable = Landing::tableName();
      $loTable = LandingOperator::tableName();
      $opTable = Operator::tableName();
      $lopTable = LandingOperator::TABLE_OPERATOR_PAY_TYPES;
      $ptTable = LandingPayType::tableName();

      $query = (new Query());
      $query->select([
        $opTable. '.id AS operator_id',
        $opTable. '.country_id AS country_id',
        $ptTable. '.id AS paytype_id'
      ])->from([
        $loTable,
      ])->innerJoin($lTable, sprintf('%s.`id` = %s.`landing_id`', $lTable, $loTable))
        ->innerJoin($opTable, sprintf('%s.`id` = %s.`operator_id`', $opTable, $loTable))
        ->innerJoin($ctTable, sprintf('%s.`id` = %s.`country_id`', $ctTable, $opTable))
        ->innerJoin($lopTable, sprintf('%s.operator_id = %s.operator_id AND %s.landing_id = %s.landing_id', $loTable, $lopTable, $loTable, $lopTable))
        ->innerJoin($ptTable, sprintf('%s.id = %s.landing_pay_type_id', $ptTable, $lopTable))
        ->andWhere(['=', $lTable . '.status', Landing::STATUS_ACTIVE])
        ->andWhere(['!=', $lTable . '.access_type', Landing::ACCESS_TYPE_HIDDEN])
        ->andWhere(['=', $opTable . '.status', Operator::STATUS_ACTIVE])
        ->andWhere(['=', $ptTable . '.status', LandingPayType::STATUS_ACTIVE])
        ->andWhere(['=', $ctTable . '.status', Country::STATUS_ACTIVE])
        ->groupBy(['operator_id', 'paytype_id'])
      ;

      foreach ($query->each() as $row) {
        $this->data['country'][$row['country_id']][] = $row['paytype_id'];
        $this->data['operator'][$row['operator_id']][] = $row['paytype_id'];
        $this->data['paytypes'][] = $row['paytype_id'];
      }

      foreach ($this->data['country'] as $countryId => $payTypes) {
        $this->data['country'][$countryId] = array_unique($payTypes);
      }

      foreach ($this->data['operator'] as $operatorId => $payTypes) {
        $this->data['operator'][$operatorId] = array_unique($payTypes);
      }

      $this->data['paytypes'] = array_unique($this->data['paytypes']);

      Yii::$app->cache->set(
        self::CACHE_LANDING_PAY_METHODS_KEY,
        $this->data,
        self::DURATION,
        new TagDependency(['tags' => $this->cacheTags])
      );
    }

    return $this;
  }

  public function getCountryPayTypes()
  {
    return $this->data['country'];
  }

  public function getOperatorPayTypes()
  {
    return $this->data['operator'];
  }

  public function getPayTypes()
  {
    return $this->data['paytypes'];
  }

  public function invalidateCache()
  {
    Yii::$app->getCache()->delete(self::CACHE_LANDING_PAY_METHODS_KEY);
  }
}