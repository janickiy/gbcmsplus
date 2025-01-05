<?php

namespace mcms\promo\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\AvailableOperators;
use mcms\promo\models\Operator;
use Yii;
use mcms\common\module\api\ApiResult;
use mcms\promo\models\Country;
use yii\caching\TagDependency;

class GetCachedCountries extends ApiResult
{

  const CACHE_COUNTRIES_KEY_PREFIX = 'mcms.partners.promo.countries';
  const DURATION = 1800;

  protected $userId;
  protected $data;

  protected $cacheTags = ['country', 'operator', 'landing'];

  public function init($params = [])
  {
    $this->userId = ArrayHelper::getValue($params, 'userId');
  }

  public function getResult()
  {
    if (!($this->data = Yii::$app->getCache()->get($this->getCacheKey()))) {
      $data = Country::find()->where([Country::tableName() . '.status' => Country::STATUS_ACTIVE]);

      // Tricky: Если передать id партнера, будут возвращены страны, которые имеют операторы не заблокированные для данного партнера
      // Иначе вернутся просто страны с активными операторами
      $this->data = $this->userId === null
        ? $data->with(['activeOperator'])->all()
        : $data->innerJoin(Operator::tableName(), Operator::tableName() . '.country_id=' . Country::tableName() . '.id')
          ->andWhere([Operator::tableName() . '.id' => AvailableOperators::getInstance($this->userId)->getIds()])->all();

      Yii::$app->cache->set(
        $this->getCacheKey(),
        $this->data,
        self::DURATION,
        new TagDependency(['tags' => $this->cacheTags])
      );
    }

    return $this->data;
  }

  public function invalidateCache()
  {
    Yii::$app->getCache()->delete($this->getCacheKey());
  }

  private function getCacheKey()
  {
    return self::CACHE_COUNTRIES_KEY_PREFIX . $this->userId;
  }
}