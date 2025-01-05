<?php


namespace mcms\promo\commands;

use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingUnblockRequest;
use yii\console\Controller;
use yii\console\Exception;

/**
 * Class RequestController
 * @package mcms\promo\commands
 */
class RequestController extends Controller
{
  private $users = [
    1096,
    1105,
    1409,
    1920,
    169,
    610,
    1702,
    546,
    1086,
    1989,
    51,
    1877,
    175,
    293,
    169,
    1614,
    1507,
    1130,
    1456,
    1575,
    24

  ];

  /**
   * @throws Exception
   * @throws \yii\base\ExitException
   */
  public function actionIndex()
  {
    foreach ($this->users as $userId) {
      foreach (LandingOperator::find()->joinWith('landing')->where(['status' => Landing::STATUS_ACTIVE, 'operator_id' => 3])->each() as $landing) {

        $request = new LandingUnblockRequest();
        $request->user_id = $userId;
        $request->landing_id = $landing->landing_id;
        $request->description = 'Мега2';
        $request->status = LandingUnblockRequest::STATUS_UNLOCKED;
        $request->traffic_type = 6;
        $request->save();

      }

    }


  }
}
