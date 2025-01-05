<?php


namespace mcms\promo\commands;

use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\events\DisabledLandingsListReplace;
use mcms\promo\components\events\DisabledLandingsListReplaceFail;
use mcms\promo\components\events\DisabledLandingsListReseller;
use mcms\promo\components\events\DisabledLandingsReplaceFail;
use mcms\promo\components\events\DisabledLandingsReseller;
use mcms\promo\models\Country;
use mcms\promo\models\LandingCategory;
use mcms\promo\models\LandingSubscriptionType;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\Operator;
use mcms\promo\models\Provider;
use Yii;
use mcms\promo\components\events\DisabledLandingsReplace;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;
use yii\console\Controller;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class DisabledLandingsReplaceController
 * @package mcms\promo\commands
 */
class DisabledLandingsReplaceController extends Controller
{
  /**
   * массив [user_id, source_id, land_id, operator_id, category_id], которые были отключены у активных источников.
   * Т.е. либо у ленда поменялся статус, либо у него был удален нужный оператор в связке.
   *
   * @var array
   */
  private $data = [];

  /**
   * Для событий
   * @var array
   */
  private $forEvents = [];
  private $deletedLandings = [];
  private $userModule;
  private $landings = [];
  /**
   * @var int кэш, в котором храним айди типа подписки.
   * @see DisabledLandingsReplaceController::getSubscriptionTypeId()
   */
  private $subTypeId;

  /**
   * Категории лендов
   * @var array
   */
  private $categories = [];

  /**
   * @var array
   */
  private $users = [];

  /**
   * @var array
   */
  private $alterCategories = [];

  /**
   * @var int
   */
  private $syncUpdatedFromMinutes = 20;

  public function actionIndex()
  {
    $this->stdout("Begin DisabledLandingsReplaceController\n", Console::FG_YELLOW);

    $this->userModule = \Yii::$app->getModule('users');

    $ts = microtime(true);
    $this->initData();
    $this->stdout('initData completed in ' . (microtime(true) - $ts) . ' sec' . PHP_EOL);

    $ts = microtime(true);
    $this->replaceInUnblockRequests();
    $this->stdout('replaceInUnblockRequests completed in ' . (microtime(true) - $ts) . ' sec' . PHP_EOL);

    $ts = microtime(true);
    $this->initCategories();
    $this->stdout('initCategories completed in ' . (microtime(true) - $ts) . ' sec' . PHP_EOL);

    $ts = microtime(true);
    $this->replaceInSources();
    $this->stdout('replaceInSources completed in ' . (microtime(true) - $ts) . ' sec' . PHP_EOL);

    $ts = microtime(true);
    $this->sendEvents();
    $this->stdout('sendEvents completed in ' . (microtime(true) - $ts) . ' sec' . PHP_EOL);

    $ts = microtime(true);
    $this->sendDeletedLandingsEvents();
    $this->stdout('sendDeletedLandingsEvents completed in ' . (microtime(true) - $ts) . ' sec' . PHP_EOL);
    if (count($this->landings) > 0) {
      //отправляем для реселлера
      $landingOperator = current(reset($this->landings));
      $event = count($this->landings) > 1
        ? new DisabledLandingsListReseller($this->landings)
        : new DisabledLandingsReseller($landingOperator->landing, reset($this->landings))
      ;
      $event->trigger();
    }

    $this->stdout("Finished DisabledLandingsReplaceController\n", Console::FG_GREEN);
  }

  /**
   * Получаем данные из БД
   */
  private function initData()
  {
    $this->data = (new Query())
      ->select(['s.user_id', 'source_id', 's.source_type', 's.status as source_status', 'sol.landing_id', 'sol.operator_id', 'l.category_id', 's.hash', 'sol.profit_type', 'sol.is_disable_handled', 'l.to_landing_id', 'r.status as unblock_request_status'])
      ->from(SourceOperatorLanding::tableName() . ' sol')
      ->innerJoin(Landing::tableName() . ' l', 'l.id = sol.landing_id')
      ->innerJoin(Source::tableName() . ' s', 's.id = sol.source_id')
      ->leftJoin(LandingOperator::tableName() . ' lo', 'lo.landing_id = sol.landing_id AND lo.operator_id = sol.operator_id')
      ->leftJoin(Operator::tableName() . ' o', 'o.id = sol.operator_id')
      ->leftJoin(Country::tableName() . ' c', 'c.id = o.country_id')
      ->leftJoin(LandingUnblockRequest::tableName() . ' r', 'r.landing_id = l.id')
      ->where([
        'and',
          's.status = ' . Source::STATUS_APPROVED . ' or s.status = ' . Source::STATUS_MODERATION,
          'l.sync_updated_at >= ' . (time() - $this->syncUpdatedFromMinutes * 60),
        [
          'or',
          'l.status = ' . Landing::STATUS_INACTIVE . ' or (l.access_type IN (' . implode(',', [Landing::ACCESS_TYPE_HIDDEN, Landing::ACCESS_TYPE_BY_REQUEST]) .') AND r.id IS NULL)',
          'lo.landing_id IS NULL',
          'o.status = ' . Operator::STATUS_INACTIVE,
          'c.status = ' . Country::STATUS_INACTIVE
        ]
      ])
      ->groupBy(['s.user_id', 'source_id', 'sol.landing_id', 'sol.operator_id'])
      ->all()
      ;
  }


  private function replaceInSources()
  {
    $subscriptionTypeId = $this->getSubscriptionTypeId();

    foreach ($this->data as $row) {

      // Берём все существующие лендинги у источника с нужным оператором
      $existed = (new Query())
        ->select('landing_id')
        ->from(SourceOperatorLanding::tableName() . ' sol')
        ->where([
          'source_id' => $row['source_id'],
          'operator_id' => $row['operator_id']
        ])
        ->column();
      // Подбираем ленд
      $alterCategories = $this->getAlterCategoryIds($row['category_id']);
      $newLandingsQuery = (new Query())
        ->select(['category_id', 'landing_id'])
        ->from(LandingOperator::tableName() . ' lo')
        ->innerJoin(Landing::tableName() . ' l', 'l.id = lo.landing_id')
        ->leftJoin(Operator::tableName() . ' o', 'o.id = lo.operator_id')
        ->leftJoin(Country::tableName() . ' c', 'c.id = o.country_id')
        ->where([
          'l.status' => Landing::STATUS_ACTIVE,
          'l.access_type' => Landing::ACCESS_TYPE_NORMAL,
          'l.category_id' => $alterCategories, // та же категория или замена на другие категории из alter_categories
          'c.status' => Country::STATUS_ACTIVE,
          'o.status' => Operator::STATUS_ACTIVE,
        ])
        ->andWhere(['not in', 'l.id', $existed]) // Исключаем уже привязанные ленды
        ->andWhere(['lo.operator_id' => $row['operator_id']]) // нужны только те ленды, которые есть у такого же оператора
        ->orderBy(['rating' => SORT_DESC])
        ->groupBy('landing_id');


      if ((int)ArrayHelper::getValue($row, 'profit_type') === SourceOperatorLanding::PROFIT_TYPE_REBILL) {
        // Если связка на ребиллы, то нужны ленды только ребилльные (не ИК)
        $newLandingsQuery->andWhere(['lo.subscription_type_id' => $subscriptionTypeId]);
      }

      if ((int)ArrayHelper::getValue($row, 'profit_type') === SourceOperatorLanding::PROFIT_TYPE_BUYOUT) {
        // Связка либо выкуп, либо ИК.
        // Следовательно если тип нового ленда ПДП, то проверяем что есть цены на выкуп.
        // Либо если тип ленда ONETIME, то берём просто так.
        $newLandingsQuery->andWhere([
          'or',
          [
            'and',
            'lo.subscription_type_id' => $subscriptionTypeId,
            [
              'or',
              'lo.buyout_price_rub > 0',
              'lo.buyout_price_eur > 0',
              'lo.buyout_price_usd > 0'
            ]
          ],
          [
            '<>', 'lo.subscription_type_id', $subscriptionTypeId,
            // tricky наличие цен на ребилл не проверяем, т.к. они все равно придут от провайдера в постбеке ребилла.
            // в отличие от цен за выкуп без которых мы не сможем выкупить ПДП у партнера
          ]
        ]);
      }

      $toLandingId = ArrayHelper::getValue($row, 'to_landing_id');

      if ($toLandingId) {
        $newLandingsQuery->orderBy(new Expression('FIELD(l.id, :to_landing_id) DESC, rating DESC', ['to_landing_id' => $toLandingId]));
      }

      echo $newLandingsQuery->createCommand()->rawSql . PHP_EOL;

      $newLanding = $this->getLandingByCategory($newLandingsQuery->all(), $row['category_id']);

      $conditions = [
        'landing_id' => $row['landing_id'],
        'operator_id' => $row['operator_id'],
        'source_id' => $row['source_id'],
      ];

      if (!$newLanding) {
        // удаляем этот ленд у источника если источник вебмастера
        if ($row['source_type'] == Source::SOURCE_TYPE_WEBMASTER_SITE) {
          SourceOperatorLanding::deleteAll($conditions);
        } else {
          // иначе пользователь сам отредактирует, и выберет ленды
          if (!$row['is_disable_handled']) {

            SourceOperatorLanding::updateAll([
              'is_disable_handled' => 1
            ], $conditions);

            if ($row['source_status'] == Source::STATUS_APPROVED) {
              $this->deletedLandings[$row['user_id']][$row['source_id']][$row['landing_id']][] = $row['operator_id'];
            }
          }
        }
      } else {
        // Обновляем ленд источника
        SourceOperatorLanding::updateAll([
          'landing_id' => $newLanding['landing_id'],
          'is_disable_handled' => 0,
        ], $conditions);

        if (in_array($row['source_status'], [Source::STATUS_APPROVED, Source::STATUS_MODERATION])) {
          $this->forEvents[$row['user_id']][$row['landing_id']][] = $row['operator_id'];
          $this->forEvents[$row['user_id']] = array_unique($this->forEvents[$row['user_id']]);
        }
      }

      ApiHandlersHelper::bufferedClearCache('SourceData' . $row['hash']);
      ApiHandlersHelper::bufferedClearCache('SourceLandingIdsGroupByOperator' . $row['source_id']);
      ApiHandlersHelper::bufferedClearCache('SourceDataById' . $row['source_id']);
    }
    ApiHandlersHelper::bufferedClearCache(null);
  }

  private function sendDeletedLandingsEvents()
  {
    if (!count($this->deletedLandings)) return ;

    foreach ($this->deletedLandings as $userId => $sources) {

      if (!array_key_exists($userId, $this->users)) {
        $user = $this->userModule->api('getOneUser', ['user_id' => $userId])->getResult();
        $this->users[$userId] = $user;
      }

      $user = $this->users[$userId];

      foreach ($sources as $sourceId => $landings) {

        $landingsModels = [];
        foreach ($landings as $landingId => $operators) {
          $landingsOperatorModels = LandingOperator::find()->where(['landing_id' => $landingId, 'operator_id' => array_values($operators)])->indexBy('operator_id')->all();
          foreach ($operators as $operatorId) {
            if (!array_key_exists($operatorId, $landingsOperatorModels)) continue;
            $landingsModels[$landingId][$operatorId] = $landingsOperatorModels[$operatorId];
            $this->landings[$landingId][$operatorId] = $landingsOperatorModels[$operatorId];
          }
        }

        if (count($landingsModels) == 0) continue;

        $source = Source::findOne($sourceId);

        if (count($landingsModels) > 1) {
          (new DisabledLandingsListReplaceFail($landingsModels, $source, $user))->trigger();

          continue;
        }

        $landingOperator = current(reset($landingsModels)); /* @var $landingOperator LandingOperator */
        (new DisabledLandingsReplaceFail(
          $landingOperator->landing,
          $source,
          $user,
          reset($landingsModels)
        ))->trigger();
      }
    }
  }

  private function sendEvents()
  {
    foreach ($this->forEvents as $userId => $landings) {
      if (!array_key_exists($userId, $this->users)) {
        $user = $this->userModule->api('getOneUser', ['user_id' => $userId])->getResult();
        $this->users[$userId] = $user;
      }

      $user = $this->users[$userId];

      foreach ($landings as $landingId => $operators) {
        $landingsOperatorModels = LandingOperator::find()->where(['landing_id' => $landingId, 'operator_id' => array_values($operators)])->indexBy('operator_id')->all();
        foreach ($operators as $operatorId) {
          if (!array_key_exists($operatorId, $landingsOperatorModels)) continue;
          $landingsModels[$landingId][$operatorId] = $landingsOperatorModels[$operatorId];
          $this->landings[$landingId][$operatorId] = $landingsOperatorModels[$operatorId];
        }
      }

      if (count($landingsModels) == 0) continue;

      if (count($landingsModels) > 1) {
        (new DisabledLandingsListReplace($landingsModels, $user))->trigger();
        continue;
      }
      $landingOperator = current(reset($landingsModels));
      (new DisabledLandingsReplace($landingOperator->landing, $user, reset($landingsModels)))->trigger();
    }
  }


  private function initCategories()
  {
    $this->categories = ArrayHelper::toArray(LandingCategory::find()
      ->where(['status' => LandingCategory::STATUS_ACTIVE])
      ->indexBy('code')
      ->all()
    );
  }

  protected function getAlterCategoryIds($categoryId)
  {
    if (array_key_exists($categoryId, $this->alterCategories)) {
      return $this->alterCategories[$categoryId];
    }

    $result = [$categoryId];
    $category = $this->getCategoryById($categoryId);
    if (!$category) {
      $this->alterCategories[$categoryId] = $result;
      return $result;
    }

    $altersArr = ArrayHelper::getValue($category, 'alter_categories');

    if (empty($altersArr)) {
      $this->alterCategories[$categoryId] = $result;
      return $result;
    }

    $altersIds = array_map(function($categoryCode){
      $alterCat = ArrayHelper::getValue($this->categories, $categoryCode);
      return $alterCat ? $alterCat['id'] : false;
    }, $altersArr);

    $this->alterCategories[$categoryId] = array_merge(array_filter($altersIds), $result);
    return $this->alterCategories[$categoryId];
  }

  protected function getCategoryById($categoryId)
  {
    foreach ($this->categories as $category) {
      if (ArrayHelper::getValue($category, 'id') == $categoryId) return $category;
    }
    return false;
  }

  protected function getLandingByCategory($lands, $origCategoryId)
  {
    if (!is_array($lands) || empty($lands)) return false;

    // Сначала пытаемся взять самый верхний ленд оригинальной категории
    foreach ($lands as $land) {
      if (ArrayHelper::getValue($land, 'category_id') == $origCategoryId) return $land;
    }

    // Берём альтернативную категорию, т.е. первую в списке.
    return reset($lands);
  }

  /**
   * @param string $string
   * @return bool|int
   */
  public function stdout($string)
  {
    if (YII_ENV_TEST) return '';
    return parent::stdout($string);
  }

  /**
   * В этом методе реализован механизм из пункта 1 задачи MCMS-2069:
   * Если в синхронизации с контент провайдером у скрытого ленда будет указан статус неактивный и указан
   * редирект на другой скрытый ленд, то:
   * 1 - во всех разрешениях доступа к неактивному лендингу менять ID старого ленда на новый (на который редиректит)
   * 2 - во всех ссылках менять ID лендов со старого на новый
   * P.S.: Пункт 2 реализован в методе @see DisabledLandingsReplaceController::replaceInSources()
   *
   * Обновление сделано через модели, чтобы сохранился лог моделей
   */
  private function replaceInUnblockRequests()
  {
    foreach ($this->data as $row) {
      $requestStatus = ArrayHelper::getValue($row, 'unblock_request_status');
      $toLandingId = ArrayHelper::getValue($row, 'to_landing_id');
      $userId = ArrayHelper::getValue($row, 'user_id');
      $landingId = ArrayHelper::getValue($row, 'landing_id');

      if ($requestStatus === null || $toLandingId === null) {
        continue;
      }

      /** @var LandingUnblockRequest[] $requests */
      $requests = LandingUnblockRequest::find()->andWhere(['user_id' => $userId, 'landing_id' => $landingId])->all();

      foreach ($requests as $request) {
        $request->scenario = LandingUnblockRequest::SCENARIO_REPLACE_BY_SYNC;
        $request->landing_id = $toLandingId;

        if (!$request->save()) {
          Yii::error('Land Unblock Request save failed: ' . print_r($request, true), __METHOD__);
        }
      }
    }
  }

  /**
   * Есть 2 способа списания средств с абонента: это единоразовое списание, либо подписка на услугу.
   * Данный метод вернет ID типа "подписка на услугу"
   * @return int
   */
  private function getSubscriptionTypeId()
  {
    if ($this->subTypeId !== null) {
      return $this->subTypeId;
    }

    $this->subTypeId = (int) (new Query())
      ->select('id')
      ->from(LandingSubscriptionType::tableName())
      ->where(['code' => LandingSubscriptionType::CODE_SUBSCRIPTION])
      ->scalar();

    return $this->subTypeId;
  }
}
