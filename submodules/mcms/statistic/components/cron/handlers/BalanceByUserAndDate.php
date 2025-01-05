<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\statistic\components\cron\AbstractTableHandler;
use PDO;
use Yii;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Хэндлер для старых группировок (когда не группировали по стране.)
 * В тот момент мы прописали всем балансам country_id=0 и создали новый хэндлер @see BalanceByUserDateCountry
 * Class BalanceByUserAndDate
 * @package mcms\statistic\components\cron\handlers
 */
class BalanceByUserAndDate extends AbstractTableHandler
{
  public function run()
  {
    $dateTo = $this->getMaxDateBalanceCountryEmpty();

    if (!$dateTo) {
      /**
       * никогда не было пустых стран (видимо новая реселлерка),
       * следовательно используем только хэндлер @see BalanceByUserDateCountry
       */
      return;
    }

    if ($this->params->fromDate > $dateTo) {
      /**
       * период когда не группировали по странам не попал в диапазон крона
       */
      return;
    }

    if (!$this->params->allowBalanceByUserAndDate) {
      $errMsg = 'Старый крон балансов не разрешено использовать. Если очень надо, то можете активировать через --allowBalanceByUserAndDate';
      Yii::error($errMsg, __METHOD__);
      $this->log($errMsg, [Console::FG_RED]);
      return;
    }

    // Партнеры, которые меняли валюту
    // TRICKY: Результат используется в запросе напрямую, через implode
    $partnersChangedCur = (new Query())->select('user_id')->from('currency_log')
      ->andWhere(['>=', 'created_at', $this->params->fromTime])
      ->groupBy('user_id')
      ->column();

    $notIn = '';
    $in = '';
    if (count($partnersChangedCur)) {
      $partnersChangedCurIds = implode(',', $partnersChangedCur);
      $notIn = "AND ss.user_id NOT IN ($partnersChangedCurIds)";
      $in = "AND ss.user_id IN ($partnersChangedCurIds)";
    }

    /* Сперва считаем без учета партнеров, которые меняли валюту */
    Yii::$app->db->createCommand("INSERT INTO `user_balances_grouped_by_day`
        (`date`, `user_id`, `type`, `profit_rub`, `profit_eur`, `profit_usd`, `user_currency`)
        SELECT
          `date`, `ss`.`user_id`,
          :typeRebill,
          SUM(partner_revshare_profit_rub) AS profit_rub,
          SUM(partner_revshare_profit_eur) AS profit_eur,
          SUM(partner_revshare_profit_usd) AS profit_usd,
          ups.currency
        FROM `statistic` ss
        INNER JOIN `user_payment_settings` `ups` ON ups.user_id = ss.user_id
        WHERE date >= :dateFrom AND date <= :dateTo AND (partner_revshare_profit_rub > 0 OR partner_revshare_profit_usd > 0 OR partner_revshare_profit_eur > 0)
        $notIn
        GROUP BY `date`, `ss`.`user_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE profit_rub = VALUES(profit_rub), profit_eur = VALUES(profit_eur), profit_usd = VALUES(profit_usd)")
      ->bindValue(':typeRebill', $this->getTypeRebill())
      ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
      ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `user_balances_grouped_by_day`
        (`date`, `user_id`, `type`, `profit_rub`, `profit_eur`, `profit_usd`, `user_currency`)
        SELECT
          `date`, `ss`.`user_id`,
          :typeOnetime,
          SUM(profit_rub) AS profit_rub,
          SUM(profit_eur) AS profit_eur,
          SUM(profit_usd) AS profit_usd,
          ups.currency
        FROM `onetime_subscriptions` ss
        INNER JOIN `user_payment_settings` `ups` ON ups.user_id = ss.user_id
        WHERE date >= :dateFrom AND date <= :dateTo AND (profit_rub > 0 OR profit_usd > 0 OR profit_eur > 0) AND ss.`is_visible_to_partner` = 1
        $notIn
        GROUP BY `date`, `ss`.`user_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE profit_rub = VALUES(profit_rub), profit_eur = VALUES(profit_eur), profit_usd = VALUES(profit_usd)")
      ->bindValue(':typeOnetime', $this->getTypeOnetime())
      ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
      ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
      ->execute();

    Yii::$app->db->createCommand("INSERT INTO `user_balances_grouped_by_day`
        (`date`, `user_id`, `type`, `profit_rub`, `profit_eur`, `profit_usd`, `user_currency`)
        SELECT
          `date`, `ss`.`user_id`,
          :typeBuyout,
          SUM(profit_rub) AS profit_rub,
          SUM(profit_eur) AS profit_eur,
          SUM(profit_usd) AS profit_usd,
          ups.currency
        FROM `sold_subscriptions` ss
        INNER JOIN `user_payment_settings` `ups` ON ups.user_id = ss.user_id
        WHERE date >= :dateFrom AND date <= :dateTo AND (profit_rub > 0 OR profit_usd > 0 OR profit_eur > 0) AND `is_visible_to_partner` = 1
        $notIn
        GROUP BY `date`, `ss`.`user_id`
        ORDER BY NULL
        ON DUPLICATE KEY UPDATE
          profit_rub = VALUES(profit_rub),
          profit_eur = VALUES(profit_eur),
          profit_usd = VALUES(profit_usd)")
      ->bindValue(':typeBuyout', $this->getTypeBuyout(), PDO::PARAM_STR)
      ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
      ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
      ->execute();

    /**
     * TRICKY: Теперь считаем только тех партнеров, которые меняли валюту
     * Из currency_log берутся диапазоны, в которых у пользователя была определенная валюта
     * Если доход партнера был получен в этом диапазоне времени, значит ему присваивается соответствующая валюта из currency_log
     */
    if ($in) {
      // Условие, по которому вычисляется диапазон
      $dateHourCondition = '(
        (-- выкуп в период между сменами валюты если обратная смена в тот же день (ограничим часами)
          ss.date = FROM_UNIXTIME(log.from_time, \'%Y-%m-%d\') 
          AND ss.date = FROM_UNIXTIME(log.to_time, \'%Y-%m-%d\') 
          AND ss.hour <= FROM_UNIXTIME(log.to_time, \'%k\') 
          AND ss.hour > FROM_UNIXTIME(log.from_time, \'%k\')
        )
        OR
        (-- выкуп в тот же день что и смена валюты
          ss.date = FROM_UNIXTIME(log.from_time, \'%Y-%m-%d\') 
          AND FROM_UNIXTIME(log.from_time, \'%Y-%m-%d\') <> FROM_UNIXTIME(log.to_time, \'%Y-%m-%d\') 
          AND ss.hour > FROM_UNIXTIME(log.from_time, \'%k\')
        )
        OR
        (-- выкуп в тот же день что и обратная смена валюты
          ss.date = FROM_UNIXTIME(log.to_time, \'%Y-%m-%d\') 
          AND FROM_UNIXTIME(log.from_time, \'%Y-%m-%d\') <> FROM_UNIXTIME(log.to_time, \'%Y-%m-%d\') 
          AND ss.hour <= FROM_UNIXTIME(log.to_time, \'%k\')
        )
        OR
        (-- выкуп в период между сменами валюты когда все даты разные
          ss.date > FROM_UNIXTIME(log.from_time, \'%Y-%m-%d\') 
          AND ss.date < FROM_UNIXTIME(log.to_time, \'%Y-%m-%d\')
        )
      )';

      // Запрос, определяющий диапазон, в котором использовалась определенная валюта
      $currencyUsingRange = 'SELECT user_id, from_time, currency, IFNULL(to_time, UNIX_TIMESTAMP()) AS to_time FROM
            (SELECT
               user_id,
               created_at AS from_time,
               currency,
               (SELECT created_at
                FROM currency_log inn
                WHERE inn.user_id = ext.user_id AND inn.created_at > ext.created_at
                ORDER BY created_at ASC
                LIMIT 1)  AS to_time
             FROM
               currency_log ext) t';

      Yii::$app->db->createCommand("INSERT INTO `user_balances_grouped_by_day`
          (`date`, `user_id`, `type`, `profit_rub`, `profit_eur`, `profit_usd`, `user_currency`)
          SELECT
            `date`, `ss`.`user_id`,
            :typeRebill,
            SUM(IF($dateHourCondition, partner_revshare_profit_rub, 0)) AS profit_rub,
            SUM(IF($dateHourCondition, partner_revshare_profit_eur, 0)) AS profit_eur,
            SUM(IF($dateHourCondition, partner_revshare_profit_usd, 0)) AS profit_usd,
            log.currency
          FROM `statistic` ss
          LEFT JOIN ($currencyUsingRange) log ON ss.user_id = log.user_id
          INNER JOIN `user_payment_settings` `ups` ON ups.user_id = ss.user_id
          WHERE date >= :dateFrom AND date <= :dateTo AND (partner_revshare_profit_rub > 0 OR partner_revshare_profit_usd > 0 OR partner_revshare_profit_eur > 0)
          $in
          GROUP BY `date`, `ss`.`user_id`, log.currency
          ORDER BY NULL
          ON DUPLICATE KEY UPDATE profit_rub = VALUES(profit_rub), profit_eur = VALUES(profit_eur), profit_usd = VALUES(profit_usd)")
        ->bindValue(':typeRebill', $this->getTypeRebill(), PDO::PARAM_STR)
        ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
        ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
        ->execute();

      Yii::$app->db->createCommand("INSERT INTO `user_balances_grouped_by_day`
          (`date`, `user_id`, `type`, `profit_rub`, `profit_eur`, `profit_usd`, `user_currency`)
          SELECT
            `date`, `ss`.`user_id`,
            :typeOnetime,
            SUM(IF($dateHourCondition, profit_rub, 0)) AS profit_rub,
            SUM(IF($dateHourCondition, profit_eur, 0)) AS profit_eur,
            SUM(IF($dateHourCondition, profit_usd, 0)) AS profit_usd,
            log.currency
          FROM `onetime_subscriptions` ss
          LEFT JOIN ($currencyUsingRange) log ON ss.user_id = log.user_id
          INNER JOIN `user_payment_settings` `ups` ON ups.user_id = ss.user_id
          WHERE date >= :dateFrom AND date <= :dateTo AND (profit_rub > 0 OR profit_usd > 0 OR profit_eur > 0) AND ss.`is_visible_to_partner` = 1
          $in
          GROUP BY `date`, `ss`.`user_id`, log.currency
          ORDER BY NULL
          ON DUPLICATE KEY UPDATE profit_rub = VALUES(profit_rub), profit_eur = VALUES(profit_eur), profit_usd = VALUES(profit_usd)")
        ->bindValue(':typeOnetime', $this->getTypeOnetime(), PDO::PARAM_STR)
        ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
        ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
        ->execute();

      Yii::$app->db->createCommand("INSERT INTO `user_balances_grouped_by_day`
          (`date`, `user_id`, `type`, `profit_rub`, `profit_eur`, `profit_usd`, `user_currency`)
          SELECT
            `date`, `ss`.`user_id`,
            :typeBuyout,
            SUM(IF($dateHourCondition, profit_rub, 0)) AS profit_rub,
            SUM(IF($dateHourCondition, profit_eur, 0)) AS profit_eur,
            SUM(IF($dateHourCondition, profit_usd, 0)) AS profit_usd,
            log.currency
          FROM `sold_subscriptions` ss
          LEFT JOIN ($currencyUsingRange) log ON ss.user_id = log.user_id
          INNER JOIN `user_payment_settings` `ups` ON ups.user_id = ss.user_id
          WHERE date >= :dateFrom AND date <= :dateTo AND (profit_rub > 0 OR profit_usd > 0 OR profit_eur > 0) AND `is_visible_to_partner` = 1
          $in
          GROUP BY `date`, `ss`.`user_id`, log.currency
          ORDER BY NULL
          ON DUPLICATE KEY UPDATE
            profit_rub = VALUES(profit_rub),
            profit_eur = VALUES(profit_eur),
            profit_usd = VALUES(profit_usd)")
        ->bindValue(':typeBuyout', $this->getTypeBuyout(), PDO::PARAM_STR)
        ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
        ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
        ->execute();
    }

    /**
     * группируем доходы по реферралам
     * сначала в таблицу user_balances_grouped_by_day, где общие доходы по всем реферралам.
     * затем в таблицу referral_incomes где уже расписано по каким реферралам суммы.
     */
    // TODO: юзаются напрямую таблицы users_referrals (модуль users), user_payment_settings (модуль payments).
    Yii::$app->db->createCommand('
        INSERT INTO `user_balances_grouped_by_day`
          (`date`, `user_id`, `type`, `profit_rub`, `profit_eur`, `profit_usd`, `user_currency`)
          SELECT
            `day_balance`.`date`,
            `u_ref`.`user_id`,
            :typeReferral,
            SUM(day_balance.profit_rub * ups.referral_percent / 100) AS `profit_rub`,
            SUM(day_balance.profit_eur * ups.referral_percent / 100) AS `profit_eur`,
            SUM(day_balance.profit_usd * ups.referral_percent / 100) AS `profit_usd`, 
            `ups`.`currency`
          FROM `user_balances_grouped_by_day` `day_balance`
          INNER JOIN `users_referrals` `u_ref` ON day_balance.user_id = u_ref.referral_id
          INNER JOIN `user_payment_settings` `ups` ON ups.user_id = u_ref.user_id
          WHERE `day_balance`.`date` >= :dateFrom AND `day_balance`.`date` <= :dateTo
          GROUP BY `u_ref`.`user_id`, `day_balance`.`date`
          ORDER BY NULL
          ON DUPLICATE KEY UPDATE
            profit_rub = VALUES(profit_rub),
            profit_eur = VALUES(profit_eur),
            profit_usd = VALUES(profit_usd)
      ')
      ->bindValue(':typeReferral', $this->getTypeReferral(), PDO::PARAM_STR)
      ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
      ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
      ->execute();

    Yii::$app->db->createCommand('
        INSERT INTO `referral_incomes`
          (`date`, `user_id`, `referral_id`, `profit_rub`, `profit_eur`, `profit_usd`, `referral_percent`)
          SELECT
            `day_balance`.`date`,
            `u_ref`.`user_id`,
            `u_ref`.`referral_id`,
            SUM(day_balance.profit_rub * ups.referral_percent / 100) AS `profit_rub`,
            SUM(day_balance.profit_eur * ups.referral_percent / 100) AS `profit_eur`,
            SUM(day_balance.profit_usd * ups.referral_percent / 100) AS `profit_usd`,
            `ups`.referral_percent
          FROM `user_balances_grouped_by_day` `day_balance`
          INNER JOIN `users_referrals` `u_ref` ON day_balance.user_id = u_ref.referral_id
          INNER JOIN `user_payment_settings` `ups` ON ups.user_id = u_ref.user_id
          WHERE `day_balance`.`date` >= :dateFrom AND `day_balance`.`date` <= :dateTo
          GROUP BY `u_ref`.`referral_id`, `day_balance`.`date`
          ORDER BY NULL
          ON DUPLICATE KEY UPDATE
            profit_rub = VALUES(profit_rub),
            profit_eur = VALUES(profit_eur),
            profit_usd = VALUES(profit_usd)')
      ->bindValue(':dateFrom', $this->params->fromDate, PDO::PARAM_STR)
      ->bindValue(':dateTo', $dateTo, PDO::PARAM_STR)
      ->execute();
  }
}
