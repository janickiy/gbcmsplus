<?php

namespace mcms\statistic\controllers;

use mcms\promo\components\api\CountryList;
use mcms\promo\Module;
use mcms\statistic\models\ResellerHoldRule;
use Yii;
use yii\base\Exception;

/**
 * Class ResellerHoldRulesController
 * @package mcms\statistic\controllers
 */
class ResellerHoldRulesController extends AbstractStatisticController
{
  /**
   * Lists all Country models.
   * @return mixed
   */
  public function actionIndex()
  {
    // tricky: если возникает ошибка при коннекте к МГМП, показываем сообщение об ошибке
    $mgmpUrlAvailable = true;
    $holdRules = [];
    try {
      $holdRules = ResellerHoldRule::getCountriesRules();
    } catch (Exception $e) {
      $mgmpUrlAvailable = false;
    }

    /** @var Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');
    /** @var CountryList $api */
    $api = $promoModule->api('countries', [
      'conditions' => Yii::$app->request->queryParams,
      'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
      'pagination'  => ['pageSize' => 20]
    ]);

    return $this->render('index', [
      'searchModel' => $api->getSearchModel(),
      'dataProvider' => $api->getResult(),
      'holdRules' => $holdRules,
      'mgmpUrlAvailable' => $mgmpUrlAvailable,
    ]);
  }
}