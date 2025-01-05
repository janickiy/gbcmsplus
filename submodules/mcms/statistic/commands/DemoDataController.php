<?php

namespace mcms\statistic\commands;

use mcms\common\helpers\curl\Curl;
use mcms\common\output\FakeOutput;
use mcms\common\output\OutputInterface;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class DemoDataController
 * @package mcms\statistic\commands
 * @deprecated
 * @see \mcms\statistic\commands\DemoTrafficGeneratorController
 */
class DemoDataController extends Controller
{

  const EUR = 71.24;
  const RUB = 1;
  const USD = 64.82;

  const RESELLER_PERCENT = 90;
  const PARTNER_PERCENT = 90;

  const INVESTOR_PRICE_PERCENT = 10;
  const RESELLER_PRICE_PERCENT = 90;
  const PARTNER_PRICE_PERCENT = 90;

  public function actionIndex($krows = 100)
  {
    $this->stdout('DEPRECATED! Используй \mcms\statistic\commands\DemoTrafficGeneratorController или \mcms\statistic\commands\TrafficGeneratorController' . "\n");
    return;

    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    $this->stdout("Start populate DB with demo data" . "\n", Console::FG_GREEN);

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
    Yii::$app->db->createCommand('TRUNCATE TABLE user_balances_grouped_by_day')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE statistic_data_hour_group')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    $this->stdout("TRUNCATED\n");

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

    $unique = [0, 0, 1, 1, 1, 1];
    $trafficback = [0, 0, 0, 0, 1];

    $landings = (new \yii\db\Query())
      ->select('`landings`.id, `landings`.provider_id, `landing_operators`.`operator_id`')
      ->from('landings')
      ->innerJoin('landing_operators', '`landings`.`id` = `landing_operators`.`landing_id`')
      ->indexBy('id')
      ->all();

    $operators = (new \yii\db\Query())
      ->select('id,country_id')
      ->from('operators')
      ->indexBy('id')
      ->all();

    $platforms = (new \yii\db\Query())
      ->select('id')
      ->from('platforms')
      ->indexBy('id')
      ->all();

    $sources = (new \yii\db\Query())
      ->select('sources.id, sources.user_id, sources.stream_id')
      ->innerJoin('users', '`users`.`id` = `sources`.`user_id`')
      ->innerJoin('auth_assignment', '`users`.`id` = `auth_assignment`.`user_id`')
      ->from('sources')
      ->where(['`auth_assignment`.item_name' => 'partner'])
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

    $currencies = (new \yii\db\Query())
      ->from('currencies')
      ->indexBy('id')
      ->all();


    for ($j = 0; $j <= $krows; $j++) {

      $hits = [];
      $hit_params = [];
      $subscriptions = [];
      $subscription_rebills = [];
      $subscription_offs = [];
      $sold_subscriptions = [];
      $onetime_subscriptions = [];

      for ($i = 1; $i <= 1000; $i++) {

        $hit_id = $j * 1000 + $i;

        $time = rand(time() - 3600 * 24 * 60, time());

        $source = $sources[array_rand($sources)];
        $source_id = $source['id'];
        $stream_id = $source['stream_id'];
        $user_id = $source['user_id'];

        $investor_source = $investor_sources[array_rand($investor_sources)];
        $to_source_id = $investor_source['id'];
        $to_stream_id = $investor_source['stream_id'];
        $to_user_id = $investor_source['user_id'];

        $landing = $landings[array_rand($landings)];
        $land_id = $landing['id'];

        $currency = $currencies[array_rand($currencies)];
        $currency_id = $currency['id'];

        $operator_id = $landing['operator_id'];

        $is_cpa = rand(1, 5) == 1;

        if(!empty($platforms)) {
          $platform = $platforms[array_rand($platforms)];
          $platform_id = $platform['id'];
        } else {
          $platform_id = rand(1,10);
        }

        $country_id = $operators[$operator_id]['country_id'];
        $provider_id = $landings[$land_id]['provider_id'];

        $landing_pay_type_id = rand(1, 3);

        $hits[] = [
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

        $hit_params[] = [
          $hit_id,
          ip2long(rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255)),
          $referers[array_rand($referers)],
          $agents[array_rand($agents)]
        ];

        if (rand(0, 100) >= 12 && rand(0, 100) <= 25) {
          $def_profit = rand(10, 120) . '.' . rand(0, 99);
          $real_profit_rub = $def_profit * 0.12;
          $onetime_subscriptions[] = [
            $hit_id,
            $this->generateUuid(),
            $time,
            date('Y-m-d', $time),
            date('H', $time),
            $def_profit,
            $currency_id,
            $real_profit_rub,
            $real_profit_rub / self::EUR,
            $real_profit_rub / self::USD,
            $real_profit_rub * self::RESELLER_PERCENT / 100,
            $real_profit_rub * self::RESELLER_PERCENT / 100 / self::EUR,
            $real_profit_rub * self::RESELLER_PERCENT / 100 / self::USD,
            $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
            $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100 / self::EUR,
            $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100 / self::USD,
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

        }

        if (rand(0, 100) <= 12) {
          $long_time = $time;

          $subscriptions[] = [
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

          if (rand(0, 100) <= 30) {
            $sold_price = rand(10, 120);
            $sold_subscriptions[] = [
              $hit_id,
              $sold_price * self::INVESTOR_PRICE_PERCENT / 100 + $sold_price,
              $sold_price * self::INVESTOR_PRICE_PERCENT / 100 / self::EUR + $sold_price,
              $sold_price * self::INVESTOR_PRICE_PERCENT / 100 / self::USD + $sold_price,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 / self::EUR,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 / self::USD,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100 / self::EUR,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100 / self::USD,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100 / self::EUR,
              $sold_price * self::RESELLER_PRICE_PERCENT / 100 * self::PARTNER_PRICE_PERCENT / 100 / self::USD,
              $time,
              date('Y-m-d', $time),
              date('H', $time),
              $stream_id,
              $source_id,
              $user_id,
              $to_stream_id,
              $to_source_id,
              $to_user_id,
              $land_id,
              $operator_id,
              $platform_id,
              $landing_pay_type_id,
              $provider_id,
              $country_id,
              $currency_id,
              rand(1, 5) == 1
            ];
          }

          for ($k = 0; $k < 20; $k++) {
            if (rand(1, 5) == 1) {
              $long_time = $long_time + 3600 * 24 * rand(1, 10);

              if ($long_time > time()) continue;

              $def_profit = rand(3, 8) . '.' . rand(0, 99);
              $real_profit_rub = $def_profit * 0.12;
              $subscription_rebills[] = [
                $hit_id,
                $this->generateUuid(),
                $long_time,
                date('Y-m-d', $long_time),
                date('H', $long_time),
                $def_profit,
                $currency_id,
                $real_profit_rub,
                $real_profit_rub / self::EUR,
                $real_profit_rub / self::USD,
                $real_profit_rub * self::RESELLER_PERCENT / 100,
                $real_profit_rub * self::RESELLER_PERCENT / 100 / self::EUR,
                $real_profit_rub * self::RESELLER_PERCENT / 100 / self::USD,
                $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100,
                $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100 / self::EUR,
                $real_profit_rub * self::RESELLER_PERCENT / 100 * self::PARTNER_PERCENT / 100 / self::USD,
                $land_id,
                $source_id,
                $operator_id,
                $platform_id,
                $landing_pay_type_id,
                $currency_id,
                $is_cpa,
              ];
            }
          }
          // 30 % отписок
          if (rand(0, 100) <= 30) {
            $time_off = $long_time + rand(100, 3600 * 15);
            $subscription_offs[] = [
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
        ['hit_id', 'ip', 'referer', 'user_agent'],
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

    $this->stdout("Demo data successfully loaded" . "\n", Console::FG_GREEN);

    Yii::$container->set(OutputInterface::class, [
      'class' => FakeOutput::class
    ]);
    $this->stdout("Run Cron Controller" . "\n", Console::FG_YELLOW);
    $this->run('/statistic/cron/index');
    $this->stdout("\nCron Controller complete" . "\n", Console::FG_YELLOW);

    $this->stdout("Run Buyout Controller" . "\n", Console::FG_YELLOW);
    $this->run('/statistic/buyout/index');
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

      $time = time() - rand(1, 20*24*60*60);
      $post[] = [
        'param1' => $i,
        'status' => 'on',
        'profit' => rand(3, 5),
        'usd_profit' => null,
        'eur_profit' => null,
        'currency' => 1,
        'phone' => 78889889889,
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

      $time = time()/* - rand(1, 20*24*60*60)*/;
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
}
