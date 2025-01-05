<?php
namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\statistic\models\resellerStatistic\Group;
use mcms\statistic\models\resellerStatistic\ItemSearch;
use mcms\statistic\models\resellerStatistic\UnholdPlanSearch;
use Yii;

class ResellerProfitController extends AdminBaseController
{
  /**
   * Страница статистики
   * @return string
   */
  public function actionIndex()
  {
    $requestData = Yii::$app->request->isPost ? Yii::$app->request->post() : Yii::$app->request->get();

    $profitSearch = new ItemSearch(['groupType' => Group::WEEK]);

    return $this->render('index', [
      'searchModel' => $profitSearch,
      'dataProvider' => $profitSearch->search($requestData),
    ]);
  }


  /**
   * Модалка для отображения сроков расхолда с разбивкой по странам.
   * @param $currency
   * @return string
   */
  public function actionUnholdPlan($currency)
  {
    $unholdPlanSearch = new UnholdPlanSearch(['filterEmptyByCurrency' => $currency]);
    return $this->renderAjax('unhold_plan', [
      'unholdPlan' => $unholdPlanSearch->search(Yii::$app->request->get()),
      'currency' => $currency
    ]);
  }
}