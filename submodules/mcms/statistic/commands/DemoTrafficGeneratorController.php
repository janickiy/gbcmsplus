<?php

namespace mcms\statistic\commands;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\curl\Curl;
use mcms\common\output\FakeOutput;
use mcms\common\output\OutputInterface;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Console;

class DemoTrafficGeneratorController extends Controller
{

  const EUR_RUB = 70.61;
  const USD_RUB = 59.97;
  const RUB_EUR = 0.0141;
  const RUB_USD = 0.0166;
  const USD_EUR = 0.849;
  const EUR_USD = 1.177;


  const RESELLER_PERCENT = 90;
  const PARTNER_PERCENT = 90;

  const INVESTOR_PRICE_PERCENT = 10;
  const RESELLER_PRICE_PERCENT = 90;
  const PARTNER_PRICE_PERCENT = 90;

  public $dateFrom;
  public $dateTo;
  public $landingCount = 50;

  public $hitCount = 500;
  public $subscribeOnCount = 15;
  public $subscribeOffCount = 6;
  public $soldCount = 5;
  public $rebillCount = 10;
  public $ontimeCount = 10;
  public $complaintCount = 2;
  public $cronCount = 48;
  public $flushAll = 0;
  public $showOutput;

  private $oldParams = [];

  const STATUS_ACTIVE = 1;

  public function options($actionID)
  {
    return ['dateFrom', 'dateTo', 'landingCount', 'hitCount', 'subscribeOnCount', 'subscribeOffCount', 'soldCount', 'rebillCount', 'ontimeCount', 'complaintCount', 'cronCount', 'flushAll', 'showOutput'];
  }


  public function actionIndex()
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    // данные для дашборда очищаем в любом случае
    Yii::$app->db->createCommand('TRUNCATE TABLE dashboard_profits_ons')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE dashboard_landings')->execute();

    if ($this->flushAll) {

      $this->stdout("Start flush all data" . "\n", Console::FG_GREEN);

      Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();

      Yii::$app->db->createCommand('TRUNCATE TABLE hits')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE hit_params')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE onetime_subscriptions')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE subscriptions')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE subscription_rebills')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE subscription_offs')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE sold_subscriptions')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE postbacks')->execute();

      Yii::$app->db->createCommand('TRUNCATE TABLE hits_day_group')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE subscriptions_day_group')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE hits_day_hour_group')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE subscriptions_day_hour_group')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE search_subscriptions')->execute();

      Yii::$app->db->createCommand('TRUNCATE TABLE statistic_data_hour_group')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE statistic_day_user_group')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE user_balances_grouped_by_day')->execute();

      Yii::$app->db->createCommand('TRUNCATE TABLE stat_filters')->execute();

      Yii::$app->db->createCommand('TRUNCATE TABLE exchanger_courses')->execute();

      //TODO добавить очистку группированных таблиц дашборда

      Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();

      $this->stdout("TRUNCATED\n");
    }

    $this->stdout("Start populate DB with demo data" . "\n", Console::FG_GREEN);

    $this->dateFrom = !empty($this->dateFrom) ? $this->dateFrom : date('Y-m-d', strtotime('-5 days'));
    $this->dateTo = !empty($this->dateTo) ? $this->dateTo : date('Y-m-d');

    $this->stdout("DateFrom: $this->dateFrom\n", Console::FG_GREEN);
    $this->stdout("DateTo: $this->dateTo\n", Console::FG_GREEN);

    $agents = [
      'Android-x86-1.6-r2 — Mozilla/5.0 (Linux; U; Android 1.6; en-us; eeepc Build/Donut) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1',
      'Mozilla/5.0 (compatible; YandexImageResizer/2.0)',
      'Mozilla/5.0 (Macintosh; I; Intel Mac OS X 10_6_7; ru-ru) AppleWebKit/534.31+ (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
      'Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.8.0.5) Gecko/20060706 K-Meleon/1.0',
      'Mozilla/5.0 (Linux; Android 4.4.2; SM-G350E Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.133 Mobile Safari/537.36',
      'Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; Microsoft; Lumia 535 Dual SIM) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537',
      'Opera/9.80 (Series 60; Opera Mini/7.1.32444/36.1487; U; ru) Presto/2.12.423 Version/12.16 Mozilla/5.0 (SymbianOS/9.4; U; Series60/5.0 Nokia5230/21.0.102; Profile/MIDP-2.1 Configuration/CLDC-1.1; ru) AppleWebKit/525 (KHTML, like Gecko) BrowserNG/7.2.5.2',
      'Mozilla/5.0 (Linux; U; Android 4.0.3; ru-ru; LG-E612 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30'
    ];

    $referers = [
      'http://fun4video.ru/',
      'http://ca.yjvfuzdr.pw/c?m=c&b=36yIzn7SUBh6233xqLhO5DKLvnF8rrZ4hcou26nkeF9VnhgiaNGBUlXLFDjouEaditlfRia6ac81GykpHYWLQoKfYqP8x3ecku0&ref=',
      'http://c6.cllvw.pw/c?m=c&b=8tf4G1PrhwjwfcEoexuLMAF',
      'http://c3.cllvw.pw/c?m=c&b=hlQDL_zRcKtsdZYmNw1549o',
      'http://w.igdz.ru/frame.php?url=http%3A%2F%2Fw.igdz.ru%2F37gYEjTf%3Ffrom%3Djs%26type%3Dpop2%26rtime%3D1'
    ];

    $hitLabels = [];
    for($i = 0; $i < 100; $i++) {
      $l1 = Yii::$app->security->generateRandomString(4);
      $l2 = Yii::$app->security->generateRandomString(4);
      $lid1 = md5($l1);
      $lid2 = md5($l2);
      $hitLabels[] = [$l1, $l2, $lid1, $lid2];
    }
    $unique = [0, 0, 1, 1, 1, 1];
    $trafficback = [0, 0, 0, 0, 1];

    $currencies = (new \yii\db\Query())
      ->from('currencies')
      ->indexBy('id')
      ->all();

    $operators = (new \yii\db\Query())
      ->select('id,country_id')
      ->from('operators')
      ->indexBy('id')
      ->all();

    // вытащим старые курсы валют если они есть (для запуска без flushAll)
    $exchanger_courses = (new \yii\db\Query())
      ->select(['usd_rur', 'rur_usd', 'usd_eur', 'eur_usd', 'eur_rur', 'rur_eur', 'created_at'])
      ->from('exchanger_courses')
      ->indexBy('created_at')
      ->all();

    $platforms = (new \yii\db\Query())
      ->select('id')
      ->from('platforms')
      ->indexBy('id')
      ->all();

    $investor_sources = (new \yii\db\Query())
      ->select('sources.id, sources.user_id, sources.stream_id')
      ->innerJoin('users', '`users`.`id` = `sources`.`user_id`')
      ->innerJoin('auth_assignment', '`users`.`id` = `auth_assignment`.`user_id`')
      ->from('sources')
      ->where(['`auth_assignment`.item_name' => 'investor'])
      ->indexBy('id')
      ->all();

    $sourceOperatorLandings = (new \yii\db\Query())
      ->select('sources_operator_landings.id, sources_operator_landings.source_id, sources_operator_landings.operator_id, sources_operator_landings.landing_id, sources.stream_id, sources.user_id')
      ->innerJoin('sources', '`sources`.`id` = `sources_operator_landings`.`source_id`')
      ->from('sources_operator_landings')
      ->where(['sources.status' => self::STATUS_ACTIVE])
      ->indexBy('id')
      ->orderBy('RAND()')
      ->limit($this->landingCount)
      ->all();

    foreach ($sourceOperatorLandings as $sourceOperatorLanding) {

      $landingOperator = (new \yii\db\Query())
        ->select('`landings`.id, `landings`.provider_id, `landing_operators`.`operator_id`, rebill_price_usd,
        rebill_price_eur, rebill_price_rub, buyout_price_usd, buyout_price_eur, buyout_price_rub, default_currency_id, subscription_type_id, default_currency_rebill_price')
        ->from('landings')
        ->innerJoin('landing_operators', '`landings`.`id` = `landing_operators`.`landing_id`')
        ->indexBy('id')
        ->where(['landing_id' => $sourceOperatorLanding['landing_id'], 'operator_id' => $sourceOperatorLanding['operator_id']])
        ->one();

      $this->stdout("landingOperator: $landingOperator[id]\n");

      $landingDefaultCurrencyCode = ArrayHelper::getValue($currencies, [$landingOperator['default_currency_id'], 'code']);

      $this->stdout("landingDefaultCurrencyCode: $landingDefaultCurrencyCode\n");

      $dateFrom = strtotime($this->dateFrom);
      $dateTo = strtotime($this->dateTo);

      for ($date = $dateFrom; $date <= $dateTo; $date = $date + 24 * 3600) {

        $this->stdout("\tDay: " . date('Y-m-d', $date) . "\n");

        if (!isset($exchanger_courses[$date])) {
          $exchanger_courses[$date] = $exchangerCourses[$date] = [
            'usd_rur' => $this->randomNumberValue(self::USD_RUB, 5),
            'rur_usd' => $this->randomNumberValue(self::RUB_USD, 5),
            'usd_eur' => $this->randomNumberValue(self::USD_EUR, 5),
            'eur_usd' => $this->randomNumberValue(self::EUR_USD, 5),
            'eur_rur' => $this->randomNumberValue(self::EUR_RUB, 5),
            'rur_eur' => $this->randomNumberValue(self::RUB_EUR, 5),
            'created_at' => $date
          ];
          $this->stdout("\t\tExchangerCourses: " . print_r($exchanger_courses[$date], true) . "\n");
        }

        for ($cronCount = 1; $cronCount <= $this->cronCount; $cronCount++) {

          $this->stdout("\t\tCronCount: $cronCount\n");

          $this->hitCount = $this->getRandParamCount('hitCount');
          $this->subscribeOnCount = $this->getRandParamCount('subscribeOnCount');
          $this->subscribeOffCount = $this->getRandParamCount('subscribeOffCount');
          $this->rebillCount = rand(0, $this->getRandParamCount('rebillCount'));
          $this->ontimeCount = $this->getRandParamCount('ontimeCount');
          $this->soldCount = $this->getRandParamCount('soldCount');
          $this->complaintCount = $this->getRandParamCount('complaintCount');

          $hits = [];
          $hit_params = [];
          $subscriptions = [];
          $subscription_rebills = [];
          $subscription_offs = [];
          $sold_subscriptions = [];
          $onetime_subscriptions = [];

          $onetimeCounter = 0;
          $subscribeOnCounter = 0;
          $subscribeOffCounter = 0;
          $soldCounter = 0;

          $hit_id = (new \yii\db\Query())
            ->select('MAX(id) + 1')
            ->from('hits')
            ->scalar() ?: 1;

          for ($hitCounter = 1; $hitCounter <= $this->hitCount; $hitCounter++) {

            $hit_id++;

            $source_id = $sourceOperatorLanding['source_id'];
            $stream_id = $sourceOperatorLanding['stream_id'] ?: 0;
            $user_id = $sourceOperatorLanding['user_id'];


            $land_id = $sourceOperatorLanding['landing_id'];

            $currency_id = $landingOperator['default_currency_id'];

            $operator_id = $sourceOperatorLanding['operator_id'];

            $allConvertCount = $this->ontimeCount + $this->soldCount + $this->subscribeOnCount;
            $cpaCount = $this->ontimeCount + $this->soldCount;

            $is_cpa = ($landingOperator['subscription_type_id'] == 4 || rand(1, $allConvertCount) < $cpaCount) ? 1 : 0;

            if (!empty($platforms)) {
              $platform = $platforms[array_rand($platforms)];
              $platform_id = $platform['id'];
            } else {
              $platform_id = rand(1, 10);
            }

            $country_id = $operators[$operator_id]['country_id'];
            $provider_id = $landingOperator['provider_id'];

            $landing_pay_type_id = rand(1, 3);

            $time = rand($date, $date + 24 * 3600);

            $hits[$hit_id] = [
              $hit_id,
              $unique[array_rand($unique)],
              $trafficback[array_rand($trafficback)],
              $time,
              date('Y-m-d', $time),
              date('H', $time),
              $operator_id,
              $land_id,
              $source_id,
              $platform_id,
              $landing_pay_type_id,
              $is_cpa,
            ];

            $phone = '7' . rand(900, 999) . rand(1000000, 9999999);

            list($l1, $l2, $sub1, $sub2) = $hitLabels[array_rand($hitLabels)];

            $hit_params[$hit_id] = [
              $hit_id,
              ip2long(rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255)),
              $referers[array_rand($referers)],
              $agents[array_rand($agents)],
              $l1,
              $l2,
              $sub1,
              $sub2,
            ];


            if (!empty($is_cpa) && $onetimeCounter < $this->ontimeCount) {
              $onetimeCounter++;

              $def_profit = $landingOperator['default_currency_rebill_price'];
              $real_profit_rub = $landingOperator['rebill_price_rub'];
              $real_profit_usd = $landingOperator['rebill_price_usd'];
              $real_profit_eur = $landingOperator['rebill_price_eur'];

              // вычисляем профиты по курсам если не заданы
              // TODO можно добавить проверку на индивидуальные рейты партнеров (но для этого проверить как они генярятся)

              list($real_profit_rub, $real_profit_usd, $real_profit_eur) = $this->convertProfits($real_profit_rub, $real_profit_usd, $real_profit_eur, $landingDefaultCurrencyCode, $exchanger_courses[$date]);

              $onetime_subscriptions[$hit_id] = [
                $hit_id,
                $this->generateUuid(),
                $time,
                date('Y-m-d', $time),
                date('H', $time),
                $def_profit,
                $currency_id,
                $real_profit_rub,
                $real_profit_eur,
                $real_profit_usd,
                $real_profit_rub * self::RESELLER_PERCENT / 100,
                $real_profit_eur * self::RESELLER_PERCENT / 100,
                $real_profit_usd * self::RESELLER_PERCENT / 100,
                $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
                $real_profit_eur * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
                $real_profit_usd * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
                $land_id,
                $source_id,
                $operator_id,
                $platform_id,
                $landing_pay_type_id,
                $provider_id,
                $country_id,
                $stream_id,
                $user_id,
                $phone,
                $currency_id
              ];

              continue;
            }

            if ($subscribeOnCounter < $this->subscribeOnCount) {

              $subscribeOnCounter++;

              //$this->stdout("\t\tsubscribeOnCounter: $subscribeOnCounter\n");

              $long_time = $time + rand(10, 120);

              $subscriptions[$hit_id] = [
                $hit_id,
                $this->generateUuid(),
                $time,
                date('Y-m-d', $time),
                date('H', $time),
                $land_id,
                $source_id,
                $operator_id,
                $platform_id,
                $landing_pay_type_id,
                $phone,
                $is_cpa,
                $currency_id
              ];

              if (!empty($is_cpa) && $soldCounter < $this->soldCount) {

                $soldCounter++;

                //$this->stdout("\t\t-soldCounter: $soldCounter\n");

                $sold_price_rub = $landingOperator['buyout_price_rub'];
                $sold_price_usd = $landingOperator['buyout_price_usd'];
                $sold_price_eur = $landingOperator['buyout_price_eur'];

                // вычисляем профиты по курсам если не заданы
                // TODO можно добавить проверку на индивидуальные рейты партнеров (но для этого проверить как они генярятся)
                list($sold_price_rub, $sold_price_usd, $sold_price_eur) = $this->convertProfits($sold_price_rub, $sold_price_usd, $sold_price_eur, $landingDefaultCurrencyCode, $exchanger_courses[$date]);

                $sold_subscriptions[$hit_id] = [
                  $hit_id,
                  $sold_price_rub * self::INVESTOR_PRICE_PERCENT / 100 + $sold_price_rub,
                  $sold_price_eur * self::INVESTOR_PRICE_PERCENT / 100 + $sold_price_eur,
                  $sold_price_usd * self::INVESTOR_PRICE_PERCENT / 100 + $sold_price_usd,
                  $sold_price_rub * self::RESELLER_PRICE_PERCENT / 100,
                  $sold_price_eur * self::RESELLER_PRICE_PERCENT / 100,
                  $sold_price_usd * self::RESELLER_PRICE_PERCENT / 100,
                  $sold_price_rub * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
                  $sold_price_eur * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
                  $sold_price_usd * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
                  $sold_price_rub * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
                  $sold_price_eur * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
                  $sold_price_usd * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
                  $time,
                  date('Y-m-d', $long_time),
                  date('H', $long_time),
                  $stream_id,
                  $source_id,
                  $user_id,
                  $stream_id,
                  $source_id,
                  $user_id,
                  $land_id,
                  $operator_id,
                  $platform_id,
                  $landing_pay_type_id,
                  $provider_id,
                  $country_id,
                  $currency_id,
                  rand(1, 5) == 1
                ];

                // переписываем данные по хиту на инвестора
                $hits[$hit_id] = [
                  $hit_id,
                  1,
                  0,
                  $time,
                  date('Y-m-d', $time),
                  date('H', $time),
                  $operator_id,
                  $land_id,
                  $source_id,
                  $platform_id,
                  $landing_pay_type_id,
                  $is_cpa,
                ];

                // переписываем данные по подписке
                $subscriptions[$hit_id] = [
                  $hit_id,
                  $this->generateUuid(),
                  $time,
                  date('Y-m-d', $time),
                  date('H', $time),
                  $land_id,
                  $source_id,
                  $operator_id,
                  $platform_id,
                  $landing_pay_type_id,
                  $phone,
                  $is_cpa,
                  $currency_id
                ];
              }

              //$this->stdout("\t\t-rebillCount: $this->rebillCount\n");

              for ($rebillCounter = 0; $rebillCounter < $this->rebillCount; $rebillCounter++) {

                $randDays = rand(1, 5) * 24 * 3600 + rand(1, 3600);
                $long_time = $long_time + $randDays;

                // выходим из цикла если дата ребила больше чем последняя дата генерации статы
                if ($long_time > $dateTo) break;

                $def_profit = $landingOperator['default_currency_rebill_price'];
                $real_profit_rub = $landingOperator['rebill_price_rub'];
                $real_profit_usd = $landingOperator['rebill_price_usd'];
                $real_profit_eur = $landingOperator['rebill_price_eur'];

                // вычисляем профиты по курсам если не заданы
                // TODO можно добавить проверку на индивидуальные рейты партнеров (но для этого проверить как они генярятся)

                if (!$real_profit_eur) $real_profit_eur = mt_rand(1, 4);
                if (!$real_profit_rub) $real_profit_rub = mt_rand(1, 4);
                if (!$real_profit_usd) $real_profit_usd = mt_rand(1, 4);

                list($real_profit_rub, $real_profit_usd, $real_profit_eur) = $this->convertProfits($real_profit_rub, $real_profit_usd, $real_profit_eur, $landingDefaultCurrencyCode, $exchanger_courses[$date]);

                $subscription_rebills[] = [
                  $hit_id,
                  $this->generateUuid(),
                  $long_time,
                  date('Y-m-d', $long_time),
                  date('H', $long_time),
                  $def_profit,
                  $currency_id,
                  $real_profit_rub,
                  $real_profit_eur,
                  $real_profit_usd,
                  $real_profit_rub * self::RESELLER_PERCENT / 100,
                  $real_profit_eur * self::RESELLER_PERCENT / 100,
                  $real_profit_usd * self::RESELLER_PERCENT / 100,
                  $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
                  $real_profit_eur * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
                  $real_profit_usd * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
                  $land_id,
                  $source_id,
                  $operator_id,
                  $platform_id,
                  $landing_pay_type_id,
                  $currency_id,
                  $is_cpa,
                ];
              }

              if ($subscribeOffCounter <= $this->subscribeOffCount) {

                //$this->stdout("\t\tsubscribeOffCounter: $subscribeOffCounter\n");

                $subscribeOffCounter++;
                $time_off = $long_time + rand(60, 60 * 15);

                // не пишем отписку если дата больше чем последняя дата генерации скрипта
                if ($time_off > $dateTo) continue;

                $subscription_offs[$hit_id] = [
                  $hit_id,
                  $this->generateUuid(),
                  $time_off,
                  date('Y-m-d', $time_off),
                  date('H', $time_off),
                  $land_id,
                  $source_id,
                  $operator_id,
                  $platform_id,
                  $landing_pay_type_id,
                  $is_cpa,
                  $currency_id,
                ];
              }

            }

          }

          Yii::$app->db->createCommand()->batchInsert(
            'hits',
            ['id', 'is_unique', 'is_tb', 'time', 'date', 'hour', 'operator_id', 'landing_id', 'source_id', 'platform_id', 'landing_pay_type_id', 'is_cpa'],
            $hits
          )->execute();

          Yii::$app->db->createCommand()->batchInsert(
            'hit_params',
            ['hit_id', 'ip', 'referer', 'user_agent', 'label1', 'label2', 'subid1', 'subid2'],
            $hit_params
          )->execute();

          if (count($subscriptions) > 0) {
            Yii::$app->db->createCommand()->batchInsert(
              'subscriptions',
              ['hit_id', 'trans_id', 'time', 'date', 'hour', 'landing_id', 'source_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'phone', 'is_cpa', 'currency_id'],
              $subscriptions
            )->execute();
          }

          if (count($subscription_rebills) > 0) {
            Yii::$app->db->createCommand()->batchInsert(
              'subscription_rebills',
              ['hit_id', 'trans_id', 'time', 'date', 'hour', 'default_profit', 'default_profit_currency',
                'real_profit_rub', 'real_profit_eur', 'real_profit_usd',
                'reseller_profit_rub', 'reseller_profit_eur', 'reseller_profit_usd',
                'profit_rub', 'profit_eur', 'profit_usd', 'landing_id', 'source_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'currency_id', 'is_cpa'],
              $subscription_rebills
            )->execute();
          }

          if (count($subscription_offs) > 0) {
            Yii::$app->db->createCommand()->batchInsert(
              'subscription_offs',
              ['hit_id', 'trans_id', 'time', 'date', 'hour', 'landing_id', 'source_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'is_cpa', 'currency_id'],
              $subscription_offs
            )->execute();
          }

          if (count($sold_subscriptions) > 0) {
            Yii::$app->db->createCommand()->batchInsert(
              'sold_subscriptions',
              ['hit_id',
                'real_price_rub', 'real_price_eur', 'real_price_usd',
                'reseller_price_rub', 'reseller_price_eur', 'reseller_price_usd',
                'price_rub', 'price_eur', 'price_usd', 'profit_rub', 'profit_eur', 'profit_usd',
                'time', 'date', 'hour',
                'stream_id', 'source_id', 'user_id',
                'to_stream_id', 'to_source_id', 'to_user_id',
                'landing_id', 'operator_id', 'platform_id', 'provider_id', 'landing_pay_type_id', 'country_id', 'currency_id',
                'is_visible_to_partner'],
              $sold_subscriptions
            )->execute();
          }

          if (count($onetime_subscriptions) > 0) {
            Yii::$app->db->createCommand()->batchInsert(
              'onetime_subscriptions',
              ['hit_id', 'trans_id', 'time', 'date', 'hour', 'default_profit', 'default_profit_currency', 'real_profit_rub',
                'real_profit_eur', 'real_profit_usd', 'reseller_profit_rub', 'reseller_profit_eur', 'reseller_profit_usd', 'profit_rub', 'profit_eur', 'profit_usd', 'landing_id', 'source_id', 'operator_id', 'platform_id', 'landing_pay_type_id',
                'provider_id', 'country_id', 'stream_id', 'user_id', 'phone', 'currency_id'],
              $onetime_subscriptions
            )->execute();
          }
        }
      }
    }

    // записываем курсы валют
    if (isset($exchangerCourses) && count($exchangerCourses) > 0) {
      Yii::$app->db->createCommand()->batchInsert(
        'exchanger_courses',
        ['usd_rur', 'rur_usd', 'usd_eur', 'eur_usd', 'eur_rur', 'rur_eur', 'created_at'],
        $exchangerCourses
      )->execute();
    }

    $this->stdout("Demo data successfully loaded" . "\n", Console::FG_GREEN);
    if (!$this->showOutput) {
      Yii::$container->set(OutputInterface::class, [
        'class' => FakeOutput::class
      ]);
    }

    $this->stdout("Run Cron Controller" . "\n", Console::FG_YELLOW);
    $this->run('/statistic/cron/index');
    $this->stdout("\nCron Controller complete" . "\n", Console::FG_YELLOW);

    $this->stdout("Run Buyout Controller" . "\n", Console::FG_YELLOW);
    $this->run('/statistic/buyout/index');

    $this->stdout("Run Dashboard Controller" . "\n", Console::FG_YELLOW);
    $this->run('/statistic/dashboard/index', [32]);
    $this->run('/statistic/postbacks/index', ['dummyExec' => true, 'timeFrom' => strtotime($this->dateFrom)]);
  }

  public static function generateUuid()
  {

    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }


  public function actionSendHits($url, $count = 100)
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    for ($i = 1; $i <= $count; $i++) {
      $curl = new Curl([
        'url' => $url
      ]);

      $curl->notUseProxy()->getResult();
      $this->stdout($i . "/" . $count . PHP_EOL);
    }
  }

  public function actionSendSubs($url, $hitsFrom, $hitsTo, $percent = 100)
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    $post = [];
    for ($i = $hitsFrom; $i <= $hitsTo; $i++) {

      $randomPercent = mt_rand(1, 100000) / 1000;
      if ($randomPercent > $percent) continue;

      $time = time() - rand(1, 20 * 24 * 60 * 60);
      $post[] = [
        'param1' => $i,
        'status' => 'on',
        'profit' => rand(3, 5),
        'usd_profit' => null,
        'eur_profit' => null,
        'currency' => 1,
        'phone' => mt_rand(70000000000, 79999999999),
        'trans_id' => self::generateUuid(),
        'time' => $time,
        'date' => date('Y-m-d', $time),
        'operator_id' => 1
      ];

      if (count($post == 10)) {
        $this->sendBatchSubs($post, $url);
        $post = [];
      }
    }
  }

  protected function sendBatchSubs($post, $url)
  {
    $curl = new Curl([
      'url' => $url,
      'isPost' => true,
      'postFields' => json_encode($post)
    ]);

    $curl->notUseProxy()->getResult();
  }

  public function actionSendOnetimes($url, $hitsFrom, $hitsTo, $percent = 100, $batch = 10)
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    $post = [];
    for ($i = $hitsFrom; $i <= $hitsTo; $i++) {

      $randomPercent = mt_rand(1, 100000) / 1000;
      if ($randomPercent > $percent) continue;

      $time = time()/* - rand(1, 20*24*60*60)*/
      ;
      $post[] = [
        'param1' => $i,
        'status' => 'rebill',
        'profit' => rand(18, 25),
        'usd_profit' => null,
        'eur_profit' => null,
        'currency' => 1,
        'phone' => 78889889889,
        'trans_id' => self::generateUuid(),
        'time' => $time,
        'date' => date('Y-m-d', $time),
        'operator_id' => 1
      ];

      if (count($post) == $batch) {
        $this->sendBatchSubs($post, $url);
        $post = [];
      }
    }
  }


  /**
   * @param int $krows
   */
  public function actionBannersDayGroup($krows = 100)
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    if (!$this->confirm('Are you sure?')) return;


    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE banners_day_group')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    $this->stdout("TRUNCATED\n");

    $this->stdout("Start populate DB with demo data" . "\n", Console::FG_GREEN);

    $operators = (new Query())
      ->select('id,country_id')
      ->from('operators')
      ->indexBy('id')
      ->all();
    $platforms = (new Query())
      ->select('id')
      ->from('platforms')
      ->indexBy('id')
      ->all();
    $sources = (new Query())
      ->select('sources.id, sources.user_id, sources.stream_id')
      ->from('sources')
      ->indexBy('id')
      ->all();
    $currencies = (new Query())
      ->from('currencies')
      ->indexBy('id')
      ->all();

    $banners = (new Query())
      ->from('banners')
      ->indexBy('id')
      ->all();

    for ($j = 1; $j <= $krows; $j++) {
      $insert = [];
      for ($i = 1; $i <= 1000; $i++) {
        $time = rand(time() - 3600 * 24 * 60, time());
        $source = $sources[array_rand($sources)];
        $source_id = $source['id'];
        $user_id = $source['user_id'];
        $currency = $currencies[array_rand($currencies)];
        $currency_id = $currency['id'];
        $operator = $operators[array_rand($operators)];
        $operator_id = $operator['id'];
        $banner = $banners[array_rand($banners)];
        $banner_id = $banner['id'];
        if (!empty($platforms)) {
          $platform = $platforms[array_rand($platforms)];
          $platform_id = $platform['id'];
        } else {
          $platform_id = rand(1, 10);
        }
        $country_id = $operators[$operator_id]['country_id'];
        $insert[] = [
          $banner_id,
          date('Y-m-d', $time),
          $source_id,
          $operator_id,
          $platform_id,
          $user_id,
          $country_id,
          rand(100, 200),
          rand(10, 100),
          rand(0, 5),
          rand(0, 5),
          rand(0, 5),
          rand(0, 20) == 1,
          rand(0, 20) >= 4,
        ];
      }
      $sql = Yii::$app->db->createCommand()->batchInsert(
        'banners_day_group',
        [
          'banner_id',
          'date',
          'source_id',
          'operator_id',
          'platform_id',
          'user_id',
          'country_id',
          'count_shows',
          'count_hits',
          'count_ons',
          'count_onetimes',
          'count_solds',
          'is_fake',
          'is_visible_to_partner'
        ],
        $insert
      )->rawSql;
      Yii::$app->db->createCommand($sql . ' ON DUPLICATE KEY UPDATE banner_id = VALUES(banner_id)')->execute();
      $this->stdout($j . "k/" . $krows . "k loaded" . "\n", Console::FG_GREEN);
    }
    $this->stdout("Demo data successfully loaded" . "\n", Console::FG_GREEN);

  }

  /**
   * getRandParamCount - рандомизируем значение параметра
   * @param $param
   * @param int $percent
   * @return int
   */
  private function getRandParamCount($param, $percent = 30)
  {
    // храним данные о старых значениях свойств чтоб они рандомились от фиксированного значения
    if (isset($this->oldParams[$param])) {
      $this->{$param} = $this->oldParams[$param];
    } else {
      $this->oldParams[$param] = $this->{$param};
    }

    return (int)$this->randomNumberValue($this->{$param}, $percent);
  }

  /**
   * @param $value
   * @param int $percent
   * @return float
   */
  private function randomNumberValue($value, $percent = 10)
  {

    $percent = rand(-$percent, $percent);
    return $value + ($value * ($percent / 100));

  }

  /**
   * @param $profit_rub
   * @param $profit_usd
   * @param $profit_eur
   * @param string $defaultCurrencyCode
   * @param $exchangerCourses
   * @return array
   */
  private function convertProfits($profit_rub, $profit_usd, $profit_eur, $defaultCurrencyCode = 'rub', $exchangerCourses)
  {

    switch ($defaultCurrencyCode) {
      case 'rub':
        $profit_usd = $profit_usd != 0
          ? $profit_usd
          : $profit_rub * $exchangerCourses['rur_usd'];
        $profit_eur = $profit_eur != 0
          ? $profit_eur
          : $profit_rub * $exchangerCourses['rur_eur'];
        break;
      case 'usd':
        $profit_rub = $profit_rub != 0
          ? $profit_rub
          : $profit_usd * $exchangerCourses['usd_rur'];
        $profit_eur = $profit_eur != 0
          ? $profit_eur
          : $profit_usd * $exchangerCourses['usd_eur'];
        break;
      case 'eur':
        $profit_rub = $profit_rub != 0
          ? $profit_rub
          : $profit_eur * $exchangerCourses['eur_rur'];
        $profit_usd = $profit_usd != 0
          ? $profit_usd
          : $profit_eur * $exchangerCourses['eur_usd'];

        break;
      default:
        throw new \yii\base\InvalidParamException('Error! Invalid currency code!');
        break;
    }

    return [$profit_rub, $profit_usd, $profit_eur];
  }
}
