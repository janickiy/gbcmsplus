<?php
namespace mcms\statistic\controllers\apiv1;

use Yii;
use mcms\common\helpers\ArrayHelper;
use mcms\common\controller\ApiController;
use mcms\statistic\components\response\TopPartnersSerializer;
use mcms\statistic\models\mysql\UserDayGroupStatistic;
use mcms\common\auth\TokenAuth;

/**
 * Class ApiController
 */
class TopController extends ApiController
{
  public function beforeAction($action)
  {
    $result = parent::beforeAction($action);
    if (!$result) return $result;

    $this->serializer = TopPartnersSerializer::class;

    return true;
  }

  public function behaviors()
  {
    $user = Yii::$app->getUser();
    $user->enableSession = false;

    return ArrayHelper::merge(parent::behaviors(), [
      'authenticator' => [
        'class' => TokenAuth::class,
        'user' => $user,
      ]
    ]);
  }


  /**
   * @param $startDate
   * @param $endDate
   * @param $order
   * @return \yii\data\ActiveDataProvider
   */
  public function actionPartners($startDate, $endDate, $order)
  {
    return (new UserDayGroupStatistic([
      'startDate' => $startDate,
      'endDate' => $endDate,
      'order' => $order
    ]))->getTopPartners();
  }
}
