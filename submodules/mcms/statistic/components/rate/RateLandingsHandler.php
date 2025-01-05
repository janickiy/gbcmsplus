<?php

namespace mcms\statistic\components\rate;

use mcms\common\helpers\Console;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\Source;
use Yii;
use yii\console\Application;
use yii\db\Query;

/**
 * Class RateLandingsHandler обновляет данные в таблице statistic_landing_hour_group
 * @package mcms\statistic\components\rate
 */
class RateLandingsHandler extends \yii\base\Object
{
  /**
   * Название категории для лога
   */
  const LOG_CATEGORY = 'LandingRotationHandler';

  /**
   * @var string
   */
  public $dateFrom = '-3 days';

  /**
   * @var bool
   */
  public $withLog = false;

  /**
   * @var int|int[]
   */
  public $sourceId;

  /**
   * @var int Минимальное количество хитов для присвоения рейтинга
   */
  public $minHitsCount = 1000;

  /**
   * @var int Максимальное количество дней для хранения в таблице статистики
   */
  public $maxDaysStorage = 7;

  /**
   * @var bool если метод запущен в консоли - выводит лог в консоль (иначе в Yii::trace())
   */
  protected $isConsole;

  /**
   * @var bool Включена ли возможность авторотации лендов
   */
  protected $isEnabled = true;

  /**
   * Initializes the object.
   * This method is invoked at the end of the constructor after the object is initialized with the
   * given configuration.
   */
  public function init()
  {
    /** @var \mcms\promo\Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');

    $this->isEnabled = $promoModule->getIsLandingsAutoRotationGlobalEnabled();
    $this->minHitsCount = $promoModule->getMinCountHitsOnLanding();

    $this->isConsole = Yii::$app instanceof Application;

    // форматируем дату
    $this->dateFrom = Yii::$app->formatter->asDate(strtotime($this->dateFrom), 'php:Y-m-d');

    parent::init();
  }

  /**
   * Запускает скрипты обновления рейтинга лендов
   */
  public function run()
  {
    if (!$this->isEnabled) {
      $this->log('Global auto rotation landings is disabled!', Console::BOLD, Console::FG_RED);

      return;
    }

    $this->updateHitsCount();
    $this->updateSubscriptions();
    $this->updateSolds();
    $this->updateOnetimes();
    $this->calculateRating();
    $this->clearUnusedLandings();
    $this->clearOutDatedStatistic();
  }

  /**
   * Обновляет часовую статистику по лендам для хитов
   */
  protected function updateHitsCount()
  {
    $this->log('Start updating hits', Console::BOLD);

    $subQuerySql = (new Query())
      ->select([
        'h.source_id',
        'h.landing_id',
        'h.operator_id',
        'h.date',
        'h.hour',
        'SUM(h.count_hits)',
      ])
      ->from('hits_day_hour_group h')
      ->andWhere([
        'and',
        ['>=', 'h.date', $this->dateFrom],
        ['sources.is_auto_rotation_enabled' => Source::IS_AUTO_ROTATION_ENABLED],
      ])
      ->andFilterWhere(['sources.id' => $this->sourceId])
      ->innerJoin('sources', 'sources.id = h.source_id')
      ->groupBy('h.date, h.hour, h.source_id, h.landing_id, h.operator_id')
      ->orderBy(null)
      ->createCommand()
      ->rawSql;

    $insertSql = <<<SQL
      INSERT INTO `statistic_landing_hour_group` (
        `source_id`,
        `landing_id`,
        `operator_id`,
        `date`,
        `hour`,
        `count_hits`
      ) 
      $subQuerySql
      ON DUPLICATE KEY UPDATE 
        count_hits = VALUES(count_hits)
SQL;

    Yii::$app->db->createCommand($insertSql)->execute();

    $this->log('Done', Console::FG_GREEN);
  }

  /**
   * Обновляет часовую статистику по лендам для подписок
   */
  protected function updateSubscriptions()
  {
    $this->log('Start updating subscriptions', Console::BOLD);

    $subQuerySql = (new Query())
      ->select([
        's.source_id',
        's.landing_id',
        's.operator_id',
        's.date',
        's.hour',
        'IFNULL(SUM(s.`count_ons`), 0)',
      ])
      ->from(['s' => 'subscriptions_day_hour_group'])
      ->andWhere([
        'and',
        ['>=', 's.date', $this->dateFrom],
        ['sources.is_auto_rotation_enabled' => Source::IS_AUTO_ROTATION_ENABLED],
      ])
      ->andFilterWhere(['sources.id' => $this->sourceId])
      ->innerJoin('sources', 'sources.id = s.source_id')
      ->groupBy('s.date, s.hour, s.source_id, s.landing_id, s.operator_id')
      ->orderBy(null)
      ->createCommand()
      ->rawSql;

    $insertSql = <<<SQL
      INSERT INTO `statistic_landing_hour_group` (
        `source_id`,
        `landing_id`,
        `operator_id`,
        `date`,
        `hour`,
        `count_subscriptions`
      ) 
      $subQuerySql
      ON DUPLICATE KEY UPDATE 
        count_subscriptions = VALUES(count_subscriptions)
SQL;

    Yii::$app->db->createCommand($insertSql)->execute();

    $this->log('Done', Console::FG_GREEN);
  }

  /**
   * Обновляет часовую статистику по лендам для выкупов
   */
  protected function updateSolds()
  {
    $this->log('Start updating solds', Console::BOLD);

    $subQuerySql = (new Query())
      ->select([
        's.source_id',
        's.landing_id',
        's.operator_id',
        's.date',
        's.hour',
        'COUNT(s.id)',
      ])
      ->from(['s' => 'sold_subscriptions'])
      ->andWhere([
        'and',
        ['>=', 's.date', $this->dateFrom],
        ['sources.is_auto_rotation_enabled' => Source::IS_AUTO_ROTATION_ENABLED],
      ])
      ->andFilterWhere(['sources.id' => $this->sourceId])
      ->innerJoin('sources', 'sources.id = s.source_id')
      ->groupBy('s.date, s.hour, s.source_id, s.landing_id, s.operator_id')
      ->orderBy(null)
      ->createCommand()
      ->rawSql;

    $insertSql = <<<SQL
      INSERT INTO `statistic_landing_hour_group` (
        `source_id`,
        `landing_id`,
        `operator_id`,
        `date`,
        `hour`,
        `count_subscriptions`
      ) 
      $subQuerySql
      ON DUPLICATE KEY UPDATE 
        count_subscriptions = count_subscriptions + VALUES(count_subscriptions)
SQL;

    Yii::$app->db->createCommand($insertSql)->execute();

    $this->log('Done', Console::FG_GREEN);
  }

  /**
   * Обновляет часовую статистику по лендам для единоразовых подписок
   */
  public function updateOnetimes()
  {
    $this->log('Start updating onetimes', Console::BOLD);

    $subQuerySql = (new Query())
      ->select([
        's.source_id',
        's.landing_id',
        's.operator_id',
        's.date',
        's.hour',
        'COUNT(s.id)',
      ])
      ->from(['s' => 'onetime_subscriptions'])
      ->andWhere([
        'and',
        ['>=', 's.date', $this->dateFrom],
        ['sources.is_auto_rotation_enabled' => Source::IS_AUTO_ROTATION_ENABLED],
      ])
      ->andFilterWhere(['sources.id' => $this->sourceId])
      ->innerJoin('sources', 'sources.id = s.source_id')
      ->groupBy('s.date, s.hour, s.source_id, s.landing_id, s.operator_id')
      ->orderBy(null)
      ->createCommand()
      ->rawSql;

    $insertSql = <<<SQL
      INSERT INTO `statistic_landing_hour_group` (
        `source_id`,
        `landing_id`,
        `operator_id`,
        `date`,
        `hour`,
        `count_subscriptions`
      ) 
      $subQuerySql
      ON DUPLICATE KEY UPDATE 
        count_subscriptions = count_subscriptions + VALUES(count_subscriptions)
SQL;

    Yii::$app->db->createCommand($insertSql)->execute();

    $this->log('Done', Console::FG_GREEN);
  }

  /**
   * Обновляет рейтинги у лендов источника
   */
  public function calculateRating()
  {
    $this->log('Start calculating landing rating', Console::BOLD);

    $subQuerySql = (new Query())
      ->select([
        'source_id',
        'landing_id',
        'operator_id',
        'count_hits' => 'SUM(count_hits)',
        'rating' => 'SUM(count_subscriptions) / SUM(count_hits)',
      ])
      ->from('statistic_landing_hour_group')
      ->groupBy('source_id, landing_id, operator_id')
      ->having(['>=', 'count_hits', $this->minHitsCount])
      ->createCommand()
      ->rawSql;

    $andWhere = $this->sourceId ? 'AND sources.id IN (' . implode(',', (array) $this->sourceId) . ')' : '';

    $updateSql = <<<SQL
      UPDATE sources_operator_landings sol
        INNER JOIN landings ON sol.landing_id = landings.id
        INNER JOIN sources ON sol.source_id = sources.id
        LEFT JOIN landing_unblock_requests lur ON lur.user_id = sources.user_id and lur.landing_id = sol.landing_id
        INNER JOIN ($subQuerySql) s
          ON sol.source_id = s.source_id
             AND sol.landing_id = s.landing_id
             AND sol.operator_id = s.operator_id
      SET sol.rating = s.rating
      WHERE landings.status = :status AND sources.is_auto_rotation_enabled = :auto_rotation_enabled
      AND (landings.access_type = :normal_at OR (landings.access_type IN (:hidden_at, :by_request_at) AND lur.status = :lur_unblocked))
      $andWhere
SQL;

    Yii::$app->db->createCommand($updateSql)
      // присваиваем рейтинг только активным лендингам
      ->bindValue(':status', Landing::STATUS_ACTIVE)
      ->bindValue(':auto_rotation_enabled', Source::IS_AUTO_ROTATION_ENABLED)
      ->bindValue(':normal_at', Landing::ACCESS_TYPE_NORMAL)
      ->bindValue(':hidden_at', Landing::ACCESS_TYPE_HIDDEN)
      ->bindValue(':by_request_at', Landing::ACCESS_TYPE_BY_REQUEST)
      ->bindValue(':lur_unblocked', LandingUnblockRequest::STATUS_UNLOCKED)
      ->execute();

    $this->log('Done', Console::FG_GREEN);
  }

  /**
   * Удаляет рейтинг у лендов без трафика
   */
  public function clearUnusedLandings()
  {
    $this->log('Start clearing rating for unused landings', Console::BOLD);

    $subQuerySql = (new Query())
      ->select([
        'h.source_id',
        'h.landing_id',
        'h.operator_id',
      ])
      ->from('hits_day_group h')
      ->andWhere(['>=', 'h.date', $this->dateFrom])
      ->groupBy('source_id, landing_id, operator_id')
      ->createCommand()
      ->rawSql;

    $andWhere = $this->sourceId ? 'AND sources.id IN (' . implode(',', (array) $this->sourceId) . ')' : '';

    $updateSql = <<<SQL
      UPDATE sources_operator_landings sol
        INNER JOIN sources ON sol.source_id = sources.id
        LEFT JOIN ($subQuerySql) h
          ON sol.source_id = h.source_id
             AND sol.landing_id = h.landing_id
             AND sol.operator_id = h.operator_id
      SET sol.rating = 0
      WHERE sources.is_auto_rotation_enabled = :auto_rotation_enabled AND h.source_id IS NULL
      $andWhere
SQL;

    Yii::$app->db->createCommand($updateSql)
      ->bindValue(':auto_rotation_enabled', Source::IS_AUTO_ROTATION_ENABLED)
      ->execute();

    $this->log('Done', Console::FG_GREEN);
  }

  /**
   * Удаляет устаревшие данные из таблицы statistic_landing_hour_group
   */
  public function clearOutDatedStatistic()
  {
    $this->log('Start clearing outdated data', Console::BOLD);

    $date = Yii::$app->formatter->asDate(time() - $this->maxDaysStorage * 3600 * 24, 'php:Y-m-d');
    $hour = date('G');

    $query = <<<SQL
      DELETE FROM statistic_landing_hour_group
      WHERE date <= :date
      AND hour <= :hour
SQL;

    Yii::$app->db->createCommand($query, [':date' => $date, ':hour' => $hour])->execute();

    $this->log('Done', Console::FG_GREEN);
  }

  /**
   * @param string $message
   */
  protected function log($message)
  {
    if ($this->isConsole) {
      $args = func_get_args();
      $message = array_shift($args) . PHP_EOL;
      $this->withLog && Console::stdout(Console::ansiFormat($message, $args));
    } else {
      Yii::trace($message, self::LOG_CATEGORY);
    }
  }
}