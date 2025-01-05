<?php


namespace mcms\promo\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\currency\models\Currency;
use mcms\promo\Module;
use yii\caching\TagDependency;
use yii\data\ArrayDataProvider;
use Yii;

class MainCurrencies extends ApiResult
{
  const CACHE_KEY = 'main_currencies';

  const RUB = 'rub';
  const USD = 'usd';
  const EUR = 'eur';

  protected $cacheTags = ['currency'];
  protected $availablesOnly;

  public function init($params = [])
  {
    $this->availablesOnly = ArrayHelper::getValue($params, 'availablesOnly', false);

    $modelsArr = Yii::$app->cache->get($this->getCacheKey($this->availablesOnly));

    if (!$modelsArr) {
      $models = Currency::find()->where(['code' => [self::RUB, self::USD, self::EUR]])->andFilterWhere($this->getCondition())->orderBy(['id' => SORT_ASC])->all();

      $modelsArr = array_map(function($model){
        $modelArr = ArrayHelper::toArray($model);
        $modelArr['label'] = $modelArr['symbol']; // TODO: на всякий случай, т.к. раньше поле называлось label когда валюты брались из констант
        $modelArr['name'] = (string)$model->name;
        return $modelArr;
      }, $models);

      Yii::$app->cache->set($this->getCacheKey($this->availablesOnly), $modelsArr, 3600, new TagDependency(['tags' => $this->cacheTags]));
    }

    $this->setDataProvider(new ArrayDataProvider([
      'pagination' => [
        'pageSize' => count($modelsArr),
      ],
      'allModels' => $modelsArr
    ]));
  }

  public function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, $this->cacheTags);
  }

  /**
   * @param bool $availablesOnly
   * @return string
   */
  private function getCacheKey($availablesOnly)
  {
    return self::CACHE_KEY . Yii::$app->language . ($availablesOnly ? '_avaliables' : '_all');
  }

  /**
   * Условие, исключающее отключенные валюты
   * @return array
   */
  private function getCondition()
  {
    if (!$this->availablesOnly) {
      return [];
    }

    $result = [];
    /** @var Module $module */
    $module = Yii::$app->getModule('promo');

    if (!$module->isRubAvailable()) {
      $result[] = ['<>', 'code', self::RUB];
    }
    if (!$module->isUsdAvailable()) {
      $result[] = ['<>', 'code', self::USD];
    }
    if (!$module->isEurAvailable()) {
      $result[] = ['<>', 'code', self::EUR];
    }

    if (count($result)) {
      array_unshift($result, 'AND');
    }

    return $result;
  }
}