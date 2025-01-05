<?php


namespace mcms\promo\commands;

use mcms\promo\components\handlers\KP;
use mcms\promo\components\handlers\Mobleaders;
use mcms\promo\components\ProviderSync;
use mcms\promo\models\Country;
use mcms\promo\models\Domain;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingCategory;
use mcms\promo\models\Operator;
use mcms\promo\models\OperatorIp;
use mcms\promo\models\PersonalProfit;
use mcms\promo\models\Provider;
use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\models\Stream;
use mcms\user\models\User;
use rgk\geoservice_client\Configuration;
use rgk\geoservice_client\endpoints\Countries;
use rgk\geoservice_client\endpoints\Operators;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\console\Exception;
use Yii;
use yii\httpclient\Client;

class DemoDataController extends Controller
{
  protected $operatorIpsCount = 3;
  protected $landingsCount = 100;
  protected $personalProfitsCount = 300;
  protected $sourcesCount = 50;
  protected $investorSourcesCount = 20;
  protected $linksCount = 50;
  protected $webmasterSourceOperatorsCount = 1;
  protected $arbitrarySourceOperatorsCount = 3;
  protected $landImages = ['land2.png', 'land4.png', 'land5.png', 'land6.png', 'land8.png'];
  protected $mobleadersUserId = 4;
  protected $apiUrl = 'https://billing.rgk.tools';
  protected $flushAll = 0;

  const LOAD_COUNTRIES = true;
  const LOAD_OPERATORS = true;
  const LOAD_LANDING_CATEGORIES = true;
  const LOAD_PROVIDERS = true;
  const LOAD_LANDINGS = true;
  const LOAD_PERSONAL_PROFITS = true;
  const LOAD_SOURCES = true;
  const LOAD_INVESTOR_SOURCES = true;

  /** @var Configuration */
  private $geoServiceConfiguration;

  public function init()
  {
    parent::init();

    $this->geoServiceConfiguration = new Configuration('http://geo.wap.group/api/v1', 'ZcCXkJJfR4sjvu2HtnzPl_O8mBbHnsPi');
  }


  public function options($actionID)
  {
    return ['landingsCount', 'sourcesCount', 'linksCount', 'personalProfitsCount', 'operatorIpsCount',
      'webmasterSourceOperatorsCount', 'arbitrarySourceOperatorsCount', 'flushAll'];
  }

  public function actionIndex()
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    if ($this->flushAll) {
      $this->stdout("Start flush all data" . "\n", Console::FG_GREEN);
      Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE providers')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE countries')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE operators')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE operator_ips')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landings')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landing_categories_banners')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landing_convert_tests')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landing_forbidden_traffic_types')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landing_operator_pay_types')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landing_operators')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landing_platforms')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE landing_unblock_requests')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE personal_profit')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE sources')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE sources_operator_landings')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE sources_banners')->execute();
      Yii::$app->db->createCommand('TRUNCATE TABLE domains')->execute();
      Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    }

    $this->stdout("Start populate DB with demo data" . "\n", Console::FG_GREEN);
    $this->loadProvider();
    $provider = Provider::findOne(['code' => 'KP']);
    $handler = new KP($provider);
    $handler->auth();


    $this->stdout("Countries..." . "\n");
    try {
      $this->syncCountries();
      $this->stdout("Countries loaded" . "\n");
    } catch (\Exception $e) {
      $this->stdout("Countries not loaded" . "\n");
    }

    $this->stdout("Operators..." . "\n");
    try {
      $this->syncOperators();
      $this->stdout("Operator loaded" . "\n");
    } catch (\Exception $e) {
      $this->stdout("Operator not loaded" . "\n");
    }

    $this->stdout("Landings..." . "\n");
    try {
      $this->loadLandings($handler);
      $this->stdout("Landings loaded" . "\n");
    } catch (\Exception $e) {
      $this->stdout("Landings not loaded" . "\n");
      throw $e;
    }

    $this->loadPersonalProfits();
    $this->loadSources();

    $this->loadInvestorSources();

    $this->stdout("Demo data successfully loaded" . "\n", Console::FG_GREEN);

  }

  private function syncCountries()
  {
    if (!self::LOAD_COUNTRIES) {
      $this->stdout("Countries skipped" . PHP_EOL);

      return ;
    }

    $endpoint = new Countries($this->geoServiceConfiguration, Yii::createObject(Client::class));
    $countries = $endpoint->get();
    if ($countries === null || is_array($countries) && !count($countries)) {
      $this->stdout('Empty response' . PHP_EOL);

      return ;
    }

    foreach ($countries as $country) {
      $countryModel = Country::findOne($country->id) ?: new Country();

      if ($countryModel->isNewRecord) {
        $countryModel->currency = 'eur';
        $countryModel->local_currency = 'eur';
      }

      if (!$countryModel->isNewRecord && (int) $countryModel->sync_updated_at >= $country->updatedAt) {
        $actualIds[] = $country['id'];
        continue;
      }

      $countryModel->setAttributes([
        'name' => $country->name,
        'code' => $country->code2l,
        'status' => $countryModel->isNewRecord ? Country::STATUS_ACTIVE : $countryModel->status,
        'sync_updated_at' => $country->updatedAt
      ]);
      $countryModel->id = $country->id;
      if (!$countryModel->save()) {
        $this->error('Country save failed. Model:' . print_r($countryModel, true));
        return;
      }
    }

    $this->stdout("Countries loaded" . "\n");
  }

  private function syncOperators()
  {
    if (!self::LOAD_OPERATORS) {
      $this->stdout("Operators skipped" . "\n");
      return;
    }
    $endpoint = new Operators($this->geoServiceConfiguration, Yii::createObject(Client::class));

    $operators = $endpoint->get();
    if ($operators === null) {
      $this->stdout("Empty response\n");
      return ;
    }
    if (!count($operators)) {
      $this->stdout("Empty response\n");
      return ;
    }

    foreach ($operators as $operator) {
      $operatorModel = Operator::findOne($operator->id) ?: new Operator();
      $operatorModel->setAttributes([
        'name' => $operator->name,
        'country_id' => $operator->countryId,
        'created_by' => User::find()->one()->id,
        'status' => $operatorModel->isNewRecord ? Operator::STATUS_ACTIVE : $operatorModel->status,
        'sync_updated_at' => $operator->updatedAt,
        'is_3g' => 0
      ]);
      $operatorModel->id = $operator->id;
      if (!$operatorModel->save()) throw new Exception('operator save fail');
    }

    $this->stdout("Operators loaded" . "\n");
  }

  private function loadOperators($handler)
  {
    if (!self::LOAD_OPERATORS) {
      $this->stdout("Operators skipped" . "\n");
      return;
    }

    $operatorsStr = $this->sendPost(
      $handler->apiUrl . '/partners/api/get-operators/',
      ['id' => $handler->mobleadersUserId]
    );
    $operators = json_decode($operatorsStr, true);

    if (!is_array($operators) || empty($operators)) {
      $this->stdout("Sync operators failed\n");
    }


    foreach ($operators as $operator) {

      if ($operator['id'] == 0) continue;
      $operatorModel = Operator::findOne($operator['id']) ?: new Operator();

      $operatorModel->setAttributes([
        'name' => $operator['name'],
        'country_id' => $operator['country_id'],
        'created_by' => User::find()->one()->id,
        'status' => $operatorModel->isNewRecord ? Operator::STATUS_ACTIVE : $operatorModel->status,
        'sync_updated_at' => (int)ArrayHelper::getValue($operator, 'updated_at'),
        'is_3g' => (int)ArrayHelper::getValue($operator, 'is_3g')
      ]);
      $operatorModel->id = $operator["id"];
      if (!$operatorModel->save()) throw new Exception('operator save fail');

      for ($i = 1; $i <= $this->operatorIpsCount; $i++) {
        (new OperatorIp([
          'operator_id' => $operator['id'],
          'from_ip' => mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255),
          'to_ip' => mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255),
          'mask' => rand(0, 32)
        ]))->save();
      }

    }
    $this->stdout("Operators loaded" . "\n");

  }

  private function loadProvider()
  {
    if (!self::LOAD_PROVIDERS) {
      $this->stdout("Provider skipped" . "\n");
      return false;
    }

    $provider = new Provider([
      'created_by' => User::find()->one()->id,
      'name' => 'KP',
      'code' => 'KP',
      'url' => 'http://url.com',
      'status' => Provider::STATUS_ACTIVE,
      'handler_class_name' => 'KP',
      'settings' => '{"api_url":"http://uz.wap.group","hash":"350b72de7d9e3d70784bc4e317ca3549","email":"wapclick@playfon.ru","language":"ru","instanceId":"","providerId":"1","streamId":"4"}',
    ]);

    $provider->save();

    $this->stdout("Provider loaded" . "\n");
  }

  /**
   * @param KP $handler
   * @throws Exception
   * @throws \yii\base\Exception
   */
  private function loadLandings($handler)
  {
    if (!self::LOAD_LANDINGS) {
      $this->stdout("Landings skipped" . "\n");
      return;
    }

    $handler->syncLandings(0);

    $this->stdout("Landings operators loaded" . "\n");
  }

  private function loadPersonalProfits()
  {
    if (!self::LOAD_PERSONAL_PROFITS) {
      $this->stdout("Personal profits skipped" . "\n");
      return;
    }

    for ($i = 1; $i <= $this->personalProfitsCount; $i++) {
      (new PersonalProfit([
        'created_by' => User::find()->one()->id,
        'user_id' => self::getRandomModel(User::class)->id,
        'rebill_percent' => rand(0, 30),
        'buyout_percent' => rand(0, 200),
      ]))->save();
      $this->stdout($i . ' personal profits of ' . $this->personalProfitsCount . ' was added' . "\n");
    }

    $this->stdout("Personal profits loaded" . "\n");
  }

  private function loadInvestorSources()
  {

    if (!self::LOAD_INVESTOR_SOURCES) {
      $this->stdout("Investor sources skipped" . "\n");
      return;
    }

    $investors = User::find()->joinWith(['roles'])->where(['item_name' => 'investor'])->all();
    foreach ($investors as $user) {

      for ($i = 1; $i <= ($this->investorSourcesCount); $i++) {
        // investor source
        $domain = self::getOrCreateModel($user, Domain::class);
        $stream = self::getOrCreateModel($user, Stream::class);

        $status = rand(0, 2);
        $source = new Source([
          'name' => 'source ' . $i,
          'user_id' => $user->id,
          'hash' => Yii::$app->security->generateRandomString(10),
          'status' => $status,
          'source_type' => Source::SOURCE_TYPE_LINK,
          'stream_id' => $stream->id,
          'domain_id' => $domain->id,
          'postback_url' => 'http://postback_' . $i . '.com',
          'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC,
          'label1' => 'label1_' . $i,
          'label2' => 'label2_' . $i,
          'reject_reason' => $status == 0 ? 'reject reason' : null,
          'landing_set_autosync' => rand(0, 1),
        ]);
        $saved = $source->save();
      }

      $this->stdout("Investor $user->id - $user->username sources count = $this->investorSourcesCount was added\n");
    }

    $this->stdout("Investor sources loaded" . "\n");
  }

  private function loadSources()
  {
    if (!self::LOAD_SOURCES) {
      $this->stdout("Sources skipped" . "\n");
      return;
    }

    for ($i = 1; $i <= ($this->sourcesCount + $this->linksCount); $i++) {

      $user = User::find()->joinWith(['roles'])->where(['item_name' => 'partner'])->orderBy('rand()')->one();

      $isWebmaster = $i > $this->linksCount;

      $source = null;

      if ($isWebmaster) {

        // webmaster source
        $url = 'http://url_' . Yii::$app->security->generateRandomString(7) . '.demo';
        $landingCategory = self::getRandomModel(LandingCategory::class);
        $status = rand(0, 2);
        $source = new Source([
          'user_id' => $user->id,
          'hash' => Yii::$app->security->generateRandomString(10),
          'status' => $status,
          'default_profit_type' => $i % 2 ? SourceOperatorLanding::PROFIT_TYPE_BUYOUT : SourceOperatorLanding::PROFIT_TYPE_REBILL,
          'url' => $url,
          'name' => $url,
          'ads_type' => rand(1, 4),
          'source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE,
          'category_id' => $status == 1 ? $landingCategory->id : null,
          'reject_reason' => $status == 0 ? 'reject reason' : null,
          'landing_set_autosync' => rand(0, 1),
        ]);

        $saved = $source->save();

      } else {

        // arbitrary source
        $domain = self::getOrCreateModel($user, Domain::class);
        $stream = self::getOrCreateModel($user, Stream::class);

        $status = rand(0, 2);
        $source = new Source([
          'name' => 'source ' . $i,
          'user_id' => $user->id,
          'hash' => Yii::$app->security->generateRandomString(10),
          'status' => $status,
          'source_type' => Source::SOURCE_TYPE_LINK,
          'stream_id' => $stream->id,
          'domain_id' => $domain->id,
          'postback_url' => 'http://postback_' . $i . '.com',
          'trafficback_type' => Source::TRAFFICBACK_TYPE_DYNAMIC,
          'label1' => 'label1_' . $i,
          'label2' => 'label2_' . $i,
          'reject_reason' => $status == 0 ? 'reject reason' : null,
          'is_notify_subscribe' => rand(0, 99) < 5 ? 1 : 0,
          'is_notify_rebill' => rand(0, 99) < 5 ? 1 : 0,
          'is_notify_unsubscribe' => rand(0, 99) < 5 ? 1 : 0,
          'is_notify_cpa' => rand(0, 99) < 5 ? 1 : 0,
          'landing_set_autosync' => rand(0, 1),
        ]);
        $saved = $source->save();

      }

      $this->stdout($i . ' sources of ' . ($this->linksCount + $this->sourcesCount) . ' was added' . "\n");

      if (!$saved) continue;

      $sourcesOperatorCount = $isWebmaster ? $this->webmasterSourceOperatorsCount : $this->arbitrarySourceOperatorsCount;

      for ($j = 1; $j <= $sourcesOperatorCount; $j++) {
        $landing = self::getRandomModel(Landing::class);
        /* @var Landing $landing */
        if (!$landing->operator) continue;
        $sourceOperatorLanding = new SourceOperatorLanding([
          'source_id' => $source->id,
          'profit_type' => $j % 2 ? SourceOperatorLanding::PROFIT_TYPE_BUYOUT : SourceOperatorLanding::PROFIT_TYPE_REBILL,
          'operator_id' => $landing->operator[0]->id,
          'is_changed' => SourceOperatorLanding::IS_NOT_CHANGED,
          'landing_choose_type' => $isWebmaster ? SourceOperatorLanding::LANDING_CHOOSE_TYPE_AUTO : SourceOperatorLanding::LANDING_CHOOSE_TYPE_MANUAL,
          'landing_id' => $landing->id
        ]);
        $sourceOperatorLanding->save();
        $this->stdout($i . ' source operator landing of ' . $sourcesOperatorCount . ' was added' . "\n");
      }
    }
    $this->stdout("Sources loaded" . "\n");
  }

  static protected function getRandomModel($className)
  {
    $max = $className::find()->count();
    $offset = rand(0, $max - 1);
    return $className::find()->offset($offset)->one();
  }

  static protected function getOrCreateModel($user, $className)
  {
    $create = rand(0, 1);
    $found = false;

    if (!$create) {
      $found = $className::find()->where(['user_id' => $user->id])->one();
    }

    if (!$create && $found) return $found;

    $model = new $className;

    if ($model instanceof Domain) {
      $model->setAttributes([
        'created_by' => $user->id,
        'user_id' => $user->id,
        'status' => Domain::STATUS_ACTIVE,
        'type' => rand(1, 2),
        'url' => 'http://domain_' . substr(md5(microtime()), 0, 9) . '.com',
      ]);
    } else if ($model instanceof Stream) {
      $model->setAttributes([
        'user_id' => $user->id,
        'name' => 'STREAM' . substr(md5(microtime()), 0, 9),
        'status' => Stream::STATUS_ACTIVE
      ]);
    }
    if ($model->save()) return $model;

    return false;

  }

  /**
   * @param $url
   * @param array $postParams
   * @return mixed
   * @throws Exception
   */
  public function sendPost($url, $postParams = [])
  {
    return (new ProviderSync())->sendPost($url, $postParams);
  }

}
