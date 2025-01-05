<?php

namespace mcms\statistic\commands;

use mcms\statistic\components\ReturnSell;
use mcms\statistic\Module;
use Yii;
use yii\console\Controller;

/**
 * Возврат подписок после жалобы
 */
class SubscriptionController extends Controller
{

  /**
   * @param string $hitIds
   * @return bool
   * @throws \yii\db\Exception
   */
  public function actionReturnSell($hitIds)
  {
    $hitIds = json_decode($hitIds);

    foreach ($hitIds as $hitId) {
      (new ReturnSell(['hitId' => $hitId]))->setVisibleToPartner();
    }

    return true;
  }
}
