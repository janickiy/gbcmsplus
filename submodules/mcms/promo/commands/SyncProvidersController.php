<?php


namespace mcms\promo\commands;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\events\LandingCreatedReseller;
use mcms\promo\components\events\LandingListCreatedReseller;
use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use mcms\promo\components\provider_instances_sync\ProviderInstancesSync;
use mcms\promo\components\ProviderSyncInterface;
use mcms\promo\components\SourceLandingSetsSync;
use mcms\promo\components\WebmasterNewLandsHandler;
use mcms\promo\models\Provider;
use mcms\promo\models\Landing;
use mcms\promo\components\events\LandingCreated;
use mcms\promo\components\events\LandingListCreated;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Console;
use Yii;

/**
 * Class SyncProvidersController
 * @package mcms\promo\commands
 */
class SyncProvidersController extends Controller
{
  private $clearCacheKeys = ['BestLandingIdByOperatorCategory'];
  private $checkUpdateTime;
  private $deleteInsteadDeactivate;

  public $landingListId = [];

  /**
   * @param string $providerCode
   * @param int $checkUpdateTime игнорировать проверку update_time с вендора
   * @param int $deleteInsteadDeactivate флаг удалять или деактивировать модели, которых не было в списке от вендора
   */
  public function actionIndex($providerCode = null, $checkUpdateTime = 1, $deleteInsteadDeactivate = 0)
  {
    $startTime = microtime(true);
    $this->checkUpdateTime = (bool)$checkUpdateTime;
    $this->deleteInsteadDeactivate = (bool)$deleteInsteadDeactivate; // TODO: пока только для операторов и стран

    // Этот класс более не нужен, так как был сделан функционал создания провайдеров кпшных через форму добавления
    // В которую были добавлены селекты с инстанцами, провайдерами и потоками
    // MCMS-2593
//    if (!$providerCode) {
//      (new ProviderInstancesSync)->run();
//    }

    //синкаем только активные провайдеры, принадлежащие RGK
    $params = ['status' => Provider::STATUS_ACTIVE, 'is_rgk' => true];
    if ($providerCode) {
      $params = ArrayHelper::merge($params, ['code' => $providerCode]);
    }

    /** @var Provider[] $providers */
    //TRICKY первым синхронизруется mobleaders, дальше все остальный инстансы kpru,... для того чтобы рейтинг КП лендов
    //был приоритетным при совпадении связок категория-оператор на провайдерах
    $providers = Provider::find()
      ->where($params)
      ->orderBy(new Expression('FIELD(code, :code) DESC', [':code' => Provider::MOBLEADERS]))
      ->all();

    foreach ($providers as $provider) {
      $handlerClass = 'mcms\promo\components\handlers\\' . $provider->handler_class_name;

      if (!$provider->handler_class_name || !class_exists($handlerClass)) {
        $this->stdout("Error: Provider handler class name not found! $handlerClass\n", Console::FG_RED);
        Yii::error("Error: Provider handler class name not found! $handlerClass\n");
        continue;
      }

      /* @var $handler ProviderSyncInterface */
      $handler = new $handlerClass($provider);

      $this->stdout('Sync provider ' . $provider->name . "\n", Console::FG_GREEN);

      /*
       * AUTH
       */
      $this->stdout('Auth...' . "\n");
      $authResult = $handler->auth();

      if (!$authResult) {
        continue;
      }

      $this->stdout('Auth successful' . "\n");

      /*
       * SYNC COUNTRIES
       */
      $this->stdout('Countries...' . "\n");
      $handler->syncCountries($checkUpdateTime, $deleteInsteadDeactivate);
      $this->stdout('Countries synchronized' . "\n");

      /*
       * SYNC OPERATORS
       */
      $this->stdout('Operators...' . "\n");
      $handler->syncOperators($checkUpdateTime, $deleteInsteadDeactivate);
      $this->stdout('Operators synchronized' . "\n");

      /*
       * SYNC LANDINGS
       */
      $this->stdout('Landings...' . "\n");
      $landingListId = $handler->syncLandings($checkUpdateTime);
      $this->landingListId = ArrayHelper::merge($this->landingListId, $landingListId);
      $this->stdout('Landings synchronized' . "\n");

      /*
       * SYNC RATING
       */
      $this->stdout('Landings rating...' . "\n");
      $handler->syncRating();
      $this->stdout('Landings rating synchronized' . "\n");

      /*
      * SYNC External providers
      */
      $this->stdout('External providers...' . "\n");
      $handler->syncExternalProviders();
      $this->stdout('External providers synchronized' . "\n");

      /*
       * SYNC SERVICES
       */
      $this->stdout('SERVICES...' . "\n");
      $handler->syncServices();
      $this->stdout('SERVICES synchronized' . "\n");

      /*
       * SYNC CAP
       */
      $this->stdout('CAP...' . "\n");
      $handler->syncCap();
      $this->stdout('CAP synchronized' . "\n");
    }

    /*
     * SEND NOTIFICATIONS
     */
    $activeLandingList = [];
    foreach ($this->landingListId as $providerCodeNotify => $landingIds) {
      $landingList = Landing::find()
        ->joinWith('provider')
        ->where([
          Provider::tableName() . '.code' => $providerCodeNotify,
          'send_id' => $landingIds,
          Landing::tableName() . '.status' => Landing::STATUS_ACTIVE,
          'access_type' => Landing::ACCESS_TYPE_NORMAL,
        ])
        ->all();

      $activeLandingList = ArrayHelper::merge($activeLandingList, $landingList);
    }

    if (!empty($activeLandingList)) {
      $landingIdList = array_map(function (Landing $landing) {
        return $landing->id;
      }, $activeLandingList);

      if (count($landingIdList) === 1) {
        (new LandingCreated(reset($activeLandingList)))->trigger();
        (new LandingCreatedReseller(reset($activeLandingList)))->trigger();
        $this->stdout('!! Event LandingCreated triggered landingId: ' . implode(', ', $landingIdList) . PHP_EOL);
      } elseif (count($landingIdList) > 1) {
        (new LandingListCreated($activeLandingList))->trigger();
        (new LandingListCreatedReseller($activeLandingList))->trigger();
        $this->stdout('!! Event LandingListCreated triggered landingIdList: ' . implode(', ', $landingIdList) . PHP_EOL);
      }
    }

    $this->stdout('Sync successfully finished' . "\n", Console::FG_GREEN);

    $this->stdout('Disabled landings redirect...' . "\n");
    (new DisabledLandingsReplaceController('disabled-landings-replace-controller', $this->module))->actionIndex();
    $this->stdout('Disabled landings redirect finished' . "\n");

    $this->stdout('Webmaster new lands handler...' . PHP_EOL);
    (new WebmasterNewLandsHandler())->run();
    $this->stdout('Webmaster new lands handler finished' . PHP_EOL);

    $this->stdout('Landing sets new lands handler...' . PHP_EOL);
    (new LandingSetsLandsUpdater())->run();
    $this->stdout('Landing sets new lands handler finished' . PHP_EOL);

    $this->stdout('Synchronizing landings from sources...' . PHP_EOL);
    (new SourceLandingSetsSync())->run();
    $this->stdout('Synchronizing landings from sources finished' . PHP_EOL);

    $this->stdout('Cache flush...' . "\n");
    if (!empty($this->clearCacheKeys)) {
      ApiHandlersHelper::clearCache(array_unique($this->clearCacheKeys));
    }
    $this->stdout('Cache flushed' . "\n");

    $timeDiff = (microtime(true) - $startTime) / 60;
    $this->stdout('Completed in ' . $timeDiff . ' min' . PHP_EOL);
  }
}
