<?php
namespace mcms\promo\commands;

use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use Yii;
use yii\console\Controller;

/**
 * Class UsersOperatorLandingsController
 * @package mcms\promo\commands
 */
class UsersOperatorLandingsController extends Controller
{
  public function actionIndex()
  {
    $time = time();
    Yii::$app->db->createCommand('
    INSERT INTO users_operator_landings (user_id, operator_id, landing_id, updated_at)
      SELECT s.user_id, sol.operator_id, sol.landing_id, :time
      FROM sources_operator_landings sol
      LEFT JOIN sources s ON s.id=sol.source_id
      GROUP BY s.user_id, sol.operator_id, sol.landing_id
      ORDER BY NULL
      ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)
    ', [':time' => $time])->execute();

    Yii::$app->db->createCommand('DELETE FROM users_operator_landings WHERE updated_at <> :time', [':time' => $time])->execute();
  }
}