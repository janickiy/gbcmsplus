<?php

namespace mcms\statistic\components\response;

use yii\helpers\ArrayHelper;
use yii\rest\Serializer;

class TopPartnersSerializer extends Serializer
{
  protected function serializeModels(array $models)
  {
    return array_map(function ($model) {
      return [
        'username' => ArrayHelper::getValue($model, 'username'),
        'topname' => ArrayHelper::getValue($model, 'topname'),
        'count_ons' => (int) ArrayHelper::getValue($model, 'count_ons', 0),
        'count_offs' => (int) ArrayHelper::getValue($model, 'count_offs', 0),
        'count_rebills' => (int) ArrayHelper::getValue($model, 'count_rebills', 0),
        'count_onetimes' => (int) ArrayHelper::getValue($model, 'count_onetimes', 0),
        'count_solds' => (int) ArrayHelper::getValue($model, 'count_solds', 0),
        'count_cpa_revshare_ons' => (int)ArrayHelper::getValue($model, 'count_cpa_revshare_ons', 0),
        'sum_rebill_profit_rub' => (int) ArrayHelper::getValue($model, 'sum_rebill_profit_rub', 0),
        'sum_rebill_profit_eur' => (int) ArrayHelper::getValue($model, 'sum_rebill_profit_eur', 0),
        'sum_rebill_profit_usd' => (int) ArrayHelper::getValue($model, 'sum_rebill_profit_usd', 0),
        'sum_onetime_profit_rub' => (int) ArrayHelper::getValue($model, 'sum_onetime_profit_rub', 0),
        'sum_onetime_profit_eur' => (int) ArrayHelper::getValue($model, 'sum_onetime_profit_eur', 0),
        'sum_onetime_profit_usd' => (int) ArrayHelper::getValue($model, 'sum_onetime_profit_usd', 0),
        'sum_sold_profit_rub' => (int) ArrayHelper::getValue($model, 'sum_sold_profit_rub', 0),
        'sum_sold_profit_eur' => (int) ArrayHelper::getValue($model, 'sum_sold_profit_eur', 0),
        'sum_sold_profit_usd' => (int) ArrayHelper::getValue($model, 'sum_sold_profit_usd', 0),
        'user_id' => (int) ArrayHelper::getValue($model, 'user_id', 0),
      ];
    }, $models);
  }

}