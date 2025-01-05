<?php

namespace mcms\promo\commands;


use mcms\promo\models\Landing;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\Module;
use Yii;
use yii\console\Controller;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class LandingsRateController
 * @package mcms\promo\commands
 */
class RateLandingsController extends Controller
{
  /**
   * @var int минимальное количество лендингов в источнике
   */
  public $minLandingsCount = 2;

  /**
   * @var bool
   */
  public $withLog = false;

  /**
   * Returns the names of valid options for the action (id)
   * An option requires the existence of a public member variable whose
   * name is the option name.
   * Child classes may override this method to specify possible options.
   *
   * Note that the values setting via options are not available
   * until [[beforeAction()]] is being called.
   *
   * @param string $actionID the action id of the current request
   * @return array the names of the options valid for the action
   */
  public function options($actionID)
  {
    return array_merge(parent::options($actionID), [
      'withLog',
    ]);
  }

  /**
   *
   */
  public function actionIndex()
  {
    /** @var Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');

    if (!$promoModule->getIsLandingsAutoRotationGlobalEnabled()) {
      $this->stdout('Global auto rotation landings is disabled!', Console::BOLD, Console::FG_RED);

      return;
    }

    $this->removeLandings();

    $this->addNewLandings();

    $this->checkExcluded();
  }

  /**
   * Удаляет лендинги с низким рейтингом из источника
   */
  public function removeLandings()
  {
    $this->stdout('Creating temporary table with landings for remove', Console::BOLD);

    $avgQuery = (new Query())
      ->select([
        'sol.source_id AS source_id',
        'sol.operator_id AS operator_id',
        'AVG(sol.rating) AS value',
        'COUNT(sol.source_id) AS count',
        'l.status AS lstatus',
        'l.access_type',
        'lur.status',
      ])
      ->from('sources_operator_landings sol')
      ->leftJoin('sources s', 'sol.source_id = s.id')
      ->innerJoin('landings l', 'sol.landing_id = l.id')
      ->leftJoin('landing_unblock_requests lur', 'lur.user_id = s.user_id and lur.landing_id = sol.landing_id')
      ->groupBy('sol.source_id, sol.operator_id')
      ->having(['or',
        ['>', 'count', $this->minLandingsCount],
        ['<>', 'lstatus', Landing::STATUS_ACTIVE],
        [
          'and',
          ['lstatus' => Landing::STATUS_ACTIVE],
          ['l.access_type' => [Landing::ACCESS_TYPE_HIDDEN, Landing::ACCESS_TYPE_BY_REQUEST]],
          ['or',
            ['<>', 'lur.status', LandingUnblockRequest::STATUS_UNLOCKED],
            ['IS', 'lur.status', null],
          ]
        ]
      ]);

    $removedSql = (new Query())
      ->select([
        'id' => 'sol.id',
        'source_id' => 's.id',
        'operator_id' => 'sol.operator_id',
        'landing_id' => 'sol.landing_id',
        'created_at' => 'UNIX_TIMESTAMP()',
        'rating' => 'sol.rating',
      ])
      ->from('sources s')
      ->leftJoin('sources_operator_landings sol', 'sol.source_id = s.id')
      ->innerJoin('landings', 'sol.landing_id = landings.id')
      ->leftJoin('landing_unblock_requests lur', 'lur.user_id = s.user_id and lur.landing_id = sol.landing_id')
      ->innerJoin(['avg' => $avgQuery], 'avg.source_id = s.id AND avg.operator_id = sol.operator_id')
      ->andWhere([
        's.is_auto_rotation_enabled' => Source::IS_AUTO_ROTATION_ENABLED,
      ])
      ->andWhere([
        'or',
        ['and', 'sol.rating > 0', 'sol.rating < avg.value'],
        // удаляем также неактивные лендинги
        [
          'and',
          ['<>', 'landings.status', Landing::STATUS_ACTIVE],
          ['landings.access_type' => Landing::ACCESS_TYPE_NORMAL],
        ],
        // и активные лендинги но скрытые и не доступные данному пользователю
        [
          'and',
          ['landings.status' => Landing::STATUS_ACTIVE],
          ['landings.access_type' => [Landing::ACCESS_TYPE_HIDDEN, Landing::ACCESS_TYPE_BY_REQUEST]],
          ['or',
            ['<>', 'lur.status', LandingUnblockRequest::STATUS_UNLOCKED],
            ['IS', 'lur.status', null],
          ]
        ]
      ])
      ->createCommand()
      ->rawSql;

    $sql = <<<SQL
      DROP TEMPORARY TABLE IF EXISTS temporary_sol;
      CREATE TEMPORARY TABLE IF NOT EXISTS temporary_sol AS (
        $removedSql
      );
SQL;

    Yii::$app->db->createCommand($sql)->execute();

    $this->stdout('Done', Console::FG_GREEN);
    $this->stdout('Start removing landings', Console::BOLD);

    $sql = <<<SQL
      DELETE FROM sources_operator_landings WHERE id IN (
        SELECT id FROM temporary_sol
      )
SQL;

    Yii::$app->db->createCommand($sql)->execute();

    $this->stdout('Done', Console::FG_GREEN);
    $this->stdout('Start inserting landings into excluded table', Console::BOLD);

    $sql = <<<SQL
      INSERT IGNORE INTO sources_operator_landings_excluded (
        source_id, 
        landing_id,
        operator_id,
        created_at,
        rating
      )
      SELECT
        source_id, 
        landing_id,
        operator_id,
        created_at,
        rating
      FROM temporary_sol
SQL;

    Yii::$app->db->createCommand($sql)->execute();

    $this->stdout('Done', Console::FG_GREEN);
  }

  /**
   * Добавляет новые лендинги к источникам
   */
  protected function addNewLandings()
  {
    $this->stdout('Prepare query for insert new landings', Console::BOLD);

    $subQuery = (new Query())
      ->select([
        'source_id' => 's.id',
        'operator_id' => 'lo.operator_id',
        'landing_id' => 'lo.landing_id',
        'profit_type' => 's.default_profit_type',
        new Expression(':choose_type AS `landing_choose_type`', [
          ':choose_type' => SourceOperatorLanding::LANDING_CHOOSE_TYPE_AUTO
        ]), // иначе будет `1` AS choose_type
      ])
      ->from('sources s')
      ->where([
        's.is_auto_rotation_enabled' => Source::IS_AUTO_ROTATION_ENABLED,
        'sol.source_id' => null,
        'sole.source_id' => null,
      ])
      ->innerJoin('landings l', 'l.category_id = s.category_id')
      ->innerJoin('landing_operators lo', 'lo.landing_id = l.id')
      ->leftJoin('sources_operator_landings sol',
        'sol.source_id = s.id' .
        ' AND sol.operator_id = lo.operator_id' .
        ' AND sol.landing_id = lo.landing_id'
      )
      ->leftJoin('landing_unblock_requests lur', 'lur.user_id = s.user_id and lur.landing_id = sol.landing_id')
      // проверяем. что лендинг не добавлен в текущий источник
      ->leftJoin('sources_operator_landings_excluded sole',
        'sole.source_id = s.id' .
        ' AND sole.operator_id = lo.operator_id' .
        ' AND sole.landing_id = lo.landing_id'
      )// проверяем. что лендинг не был исключен из источника из-за низкой конверсии
      // убираем неактивные лендинги
      ->andWhere(['l.status' => Landing::STATUS_ACTIVE])
      // и активные лендинги но скрытые и не доступные данному пользователю
      ->andWhere(['or',
        [
          'l.access_type' => Landing::ACCESS_TYPE_NORMAL,
        ],
        [
          'l.access_type' => [Landing::ACCESS_TYPE_HIDDEN, Landing::ACCESS_TYPE_BY_REQUEST],
          'lur.status' =>  LandingUnblockRequest::STATUS_UNLOCKED,
        ]
      ])
      ->createCommand()
      ->rawSql;

    $this->stdout('Start inserting new landings', Console::BOLD);

    $query = <<<SQL
      INSERT INTO sources_operator_landings (
        source_id, 
        operator_id,
        landing_id,
        profit_type,
        landing_choose_type
      )
      $subQuery
SQL;

    Yii::$app->db->createCommand($query)->execute();

    $this->stdout('Done', Console::FG_GREEN);
  }

  /**
   * Возвращает исключенные лендинги, если у них рейтинг был выше рейтинга текущих
   */
  public function checkExcluded()
  {
    $this->stdout('Prepare query for check excluded landings', Console::BOLD);

    $minQuery = (new Query())
      ->select([
        'source_id',
        'operator_id',
        'MIN(rating) AS value',
      ])
      ->from('sources_operator_landings')
      ->where(['>', 'rating', 0])
      ->groupBy('source_id, operator_id');

    $subQuery = (new Query())
      ->select([
        'source_id' => 'sole.source_id',
        'operator_id' => 'sole.operator_id',
        'landing_id' => 'sole.landing_id',
        'profit_type' => 's.default_profit_type',
        new Expression(':choose_type AS `landing_choose_type`', [
          ':choose_type' => SourceOperatorLanding::LANDING_CHOOSE_TYPE_AUTO
        ]), // иначе будет `1` AS choose_type
        'rating' => 'sole.rating',
      ])
      ->from('sources_operator_landings_excluded sole')
      ->innerJoin('landings l', 'l.id = sole.landing_id')
      ->leftJoin('sources s', 'sole.source_id = s.id')
      ->leftJoin('landing_unblock_requests lur', 'lur.user_id = s.user_id and lur.landing_id = sole.landing_id')
      ->innerJoin(['min' => $minQuery], 'min.source_id = sole.source_id AND min.operator_id = sole.operator_id')
      ->where([
        's.is_auto_rotation_enabled' => Source::IS_AUTO_ROTATION_ENABLED,
      ])
      ->andWhere('sole.rating > min.value')
      // убираем неактивные лендинги
      ->andWhere(['l.status' => Landing::STATUS_ACTIVE])
      // и активные лендинги но скрытые и не доступные данному пользователю
      ->andWhere(['or',
        [
          'l.access_type' => Landing::ACCESS_TYPE_NORMAL,
        ],
        [
          'l.access_type' => [Landing::ACCESS_TYPE_HIDDEN, Landing::ACCESS_TYPE_BY_REQUEST],
          'lur.status' =>  LandingUnblockRequest::STATUS_UNLOCKED,
        ]
      ])
      ->createCommand()
      ->rawSql;

    $query = <<<SQL
      INSERT IGNORE INTO sources_operator_landings (
        source_id, 
        operator_id,
        landing_id,
        profit_type,
        landing_choose_type,
        rating
      )
      $subQuery
SQL;

    Yii::$app->db->createCommand($query)->execute();

    $this->stdout('Done', Console::FG_GREEN);
  }

  /**
   * Prints a string to STDOUT
   *
   * You may optionally format the string with ANSI codes by
   * passing additional parameters using the constants defined in [[\yii\helpers\Console]].
   *
   * Example:
   *
   * ```
   * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
   * ```
   *
   * @param string $string the string to print
   * @return int|boolean Number of bytes printed or false on error
   */
  public function stdout($string)
  {
    if (!$this->withLog) {
      return;
    }
    $args = func_get_args();
    $args[0] = $args[0] . PHP_EOL;
    return call_user_func_array('parent::stdout', $args);
  }
}