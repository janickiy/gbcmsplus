<?php

namespace mcms\statistic\commands;

use mcms\common\controller\ConsoleController;
use mcms\common\helpers\ArrayHelper;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\promo\components\LandingOperatorPrices;
use mcms\promo\models\LandingOperator;
use mcms\statistic\components\queue\postbacks\Payload;
use mcms\statistic\components\queue\postbacks\Worker;
use mcms\statistic\models\Complain;
use mcms\statistic\Module;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class BuyoutController
 * @package mcms\statistic\commands
 */
class BuyoutController extends ConsoleController
{
  // выкупать за последние N дней
  const BUYOUT_DAYS_FROM = 3;
  // выкупать уникальные номера за последние N часов
  const BUYOUT_UNIQUE_PHONE_HOURS = 24;

  /**
   * @var bool Не коммитить транзакции выкупа, что позволит отлаживаться, не передавая подписки инвестору
   */
  public $isRollback;

  /**
   * @var bool включен ли профайлер
   */
  public $isProfilerEnabled;

  /**
   * Подсчет времени относительно константы DIFF_CALC_DAYS_COUNT
   * @var int
   */
  protected $diffCalcFromTime;
  /**
   * Yii::$app->formatter->asDate($this->diffCalcFromTime, 'php:Y-m-d');
   * @var
   */
  protected $diffCalcFromDate;

  protected $minutes;

  protected $_partnerCurrenciesProvider;

  /**
   * Сохраняем нормер телефонов для проверки уникальности при установленном флаге
   * выкупать только уникальные номера
   * @var array
   */
  protected $_operatorPhones = [];

  /** @var Module */
  protected $statisticModule;
  protected $usersModule;
  protected $paymentsModule;
  protected $promoModule;
  protected $buyoutTimeFrom;
  protected $uniqueBuyoutHours;

  /** @var  Query */
  protected $query;
  protected $subscriptions;
  protected $batchSize = 100;

  protected $courses;

  protected $userCurrencies = [];

  protected $profTiming = [];
  protected $profNames = [];

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return ['isRollback', 'isProfilerEnabled'];
  }

  public function init()
  {
    $this->profFlag('init');
    $this->statisticModule = Yii::$app->getModule('statistic');
    $this->usersModule = Yii::$app->getModule('users');
    $this->paymentsModule = Yii::$app->getModule('payments');
    $this->promoModule = Yii::$app->getModule('promo');

    $this->diffCalcFromTime = strtotime('- ' . $this->statisticModule->getCpaDiffCalcDays() . 'days');
    $this->diffCalcFromDate = Yii::$app->formatter->asDate($this->diffCalcFromTime, 'php:Y-m-d');

    $this->buyoutTimeFrom = strtotime('- ' . self::BUYOUT_DAYS_FROM . 'days');
    $this->uniqueBuyoutHours = $this->statisticModule->getUniqBuyoutHours() ?: self::BUYOUT_UNIQUE_PHONE_HOURS;

    $this->courses = $this->paymentsModule->api('exchangerPartnerCourses')->getCurrencyCourses();
  }


  /**
   * Выкупаются подписки, которые были подписаны не ранее чем $minutes назад и у которых is_cpa = 1.
   * Так же проверяется не продана ли ещё подписка и не отписана ли ещё.
   *
   * В таблицу sold_subscriptions добавляются записи.
   *
   * @throws \Exception $e
   */
  public function actionIndex()
  {
    $this->setMinutes($this->module->settings->getValueByKey(Module::SETTINGS_BUYOUT_MINUTES));

    if (!$this->minutes) {
      $this->setMinutes(15);
    }

    $this->stdout('BUYOUT BEGIN (' . $this->minutes . ' minutes interval)');

    $this->initSubscriptionsQuery();

    $this->stdout('batchSize=' . $this->batchSize);

    $batchNum = 1;

    foreach ($this->query->batch($this->batchSize) as $this->subscriptions) {
      $this->stdout('Batch ' . $batchNum . ' begin');

      // Выкупаем подписки
      $this->profFlag('buyout subs batchNum=$batchNum');
      $this->buyoutSubscriptions();

      $this->stdout('+1 batch handled');
      $batchNum++;
    }

    return $this->finish();
  }

  /**
   * Инициализация запроса на получения подписок для выкупа
   */
  protected function initSubscriptionsQuery()
  {
    $singleRebillQuery = (new Query())
      ->select('id')
      ->from('subscription_rebills')
      ->where('s.hit_id = hit_id')
      ->limit(1);

    $singleSubDuplicatedPhoneQuery = (new Query())
      ->select('uq_subs.id')
      ->from('subscriptions uq_subs')
      ->innerJoin('sold_subscriptions uq_subs_sold', 'uq_subs.hit_id=uq_subs_sold.hit_id')
      ->where([
        'and',
        'uq_subs.phone <> \'\'',
        'uq_subs.phone = s.phone',
        's.hit_id <> uq_subs.hit_id',
        'uq_subs_sold.hit_id IS NOT NULL', //считаем уникальность по выкупленным подпискам
        'uq_subs.time >= (s.time_on - :uniqueBuyoutHours * 60 * 60)'
      ])
      ->params(['uniqueBuyoutHours' => $this->uniqueBuyoutHours])
      ->limit(1);

    $subQuery = (new Query())
      ->select(['ss.*', 'IFNULL(so.time, 0) as so_time'])
      ->from('search_subscriptions ss')
      ->leftJoin('sold_subscriptions sold', 'sold.hit_id = ss.hit_id')
      ->leftJoin('subscription_offs so', 'so.hit_id = ss.hit_id')
      ->where([
        'and',
        ['>=', 'ss.time_on', $this->buyoutTimeFrom],
        ['ss.is_cpa' => 1], // на продажу
        ['sold.hit_id' => null], // не выкуплена ещё
      ]);

    // Получаем условия выкупа для всех комбинаций юзер+оператор
    $buyoutConditionsQuery = (new Query())
      ->select([
        'user_id',
        'operator_id',
        'landing_id',
        'buyout_minutes' => 'max(buyout_minutes)',
        'is_buyout_only_after_1st_rebill' => 'max(is_buyout_only_after_1st_rebill)',
        'is_buyout_only_unique_phone' => 'max(is_buyout_only_unique_phone)',
        'is_buyout_all' => 'max(is_buyout_all)',
      ])
      ->from('buyout_conditions')
      ->groupBy(['user_id', 'operator_id']);

    // Заполнены юзер, оператор, лендинг
    $buyoutConditionsUserOperatorLandingQuery = clone $buyoutConditionsQuery;
    $buyoutConditionsUserOperatorLandingQuery->where([
      'and',
      ['<>', 'user_id', 0],
      ['<>', 'operator_id', 0],
      ['<>', 'landing_id', 0],
    ]);
    // Заполнены и юзер и лендинг
    $buyoutConditionsUserLandingQuery = clone $buyoutConditionsQuery;
    $buyoutConditionsUserLandingQuery->where([
      'and',
      ['<>', 'user_id', 0],
      ['<>', 'landing_id', 0],
      ['=', 'operator_id', 0]
    ]);
    // Заполнены юзер и оператор
    $buyoutConditionsUserOperatorQuery = clone $buyoutConditionsQuery;
    $buyoutConditionsUserOperatorQuery->where([
      'and',
      ['<>', 'user_id', 0],
      ['<>', 'operator_id', 0],
      ['=', 'landing_id', 0],
    ]);
    // Заполнен только Юзер
    $buyoutConditionsUserQuery = clone $buyoutConditionsQuery;
    $buyoutConditionsUserQuery->where([
      'and',
      ['<>', 'user_id', 0],
      ['=', 'operator_id', 0],
      ['=', 'landing_id', 0],
    ]);
    // Заполнены лендинг и оператор
    $buyoutConditionsLandingOperatorQuery = clone $buyoutConditionsQuery;
    $buyoutConditionsLandingOperatorQuery->where([
      'and',
      ['=', 'user_id', 0],
      ['<>', 'operator_id', 0],
      ['<>', 'landing_id', 0],
    ]);
    // Заполнен только лендинг
    $buyoutConditionsLandingQuery = clone $buyoutConditionsQuery;
    $buyoutConditionsLandingQuery->where([
      'and',
      ['=', 'user_id', 0],
      ['=', 'operator_id', 0],
      ['<>', 'landing_id', 0],
    ]);
    // Заполнен только Оператор
    $buyoutConditionsOperatorQuery = clone $buyoutConditionsQuery;
    $buyoutConditionsOperatorQuery->where([
      'and',
      ['=', 'user_id', 0],
      ['<>', 'operator_id', 0],
      ['=', 'landing_id', 0],
    ]);

    $this->query = (new Query())
      ->select([
        's.hit_id',
        's.user_id',
        's.landing_id',
        's.operator_id',
        's.stream_id',
        's.source_id',
        's.platform_id',
        's.landing_pay_type_id',
        's.country_id',
        's.provider_id',
        's.phone',
        'time_off' => 's.so_time',

        'is_buyout_only_unique_phone' => 'IFNULL(
          bc1.is_buyout_only_unique_phone, IFNULL(
            bc2.is_buyout_only_unique_phone, IFNULL(
              bc3.is_buyout_only_unique_phone, IFNULL(
                bc4.is_buyout_only_unique_phone, IFNULL(
                  bc5.is_buyout_only_unique_phone, IFNULL(
                    bc6.is_buyout_only_unique_phone, IFNULL(
                      bc7.is_buyout_only_unique_phone, 0
                    )
                  )
                )
              )
            )
          )
        )',
        'is_buyout_only_after_1st_rebill' => 'IFNULL(
          bc1.is_buyout_only_after_1st_rebill, IFNULL(
            bc2.is_buyout_only_after_1st_rebill, IFNULL(
              bc3.is_buyout_only_after_1st_rebill, IFNULL(
                bc4.is_buyout_only_after_1st_rebill, IFNULL(
                  bc5.is_buyout_only_after_1st_rebill, IFNULL(
                    bc6.is_buyout_only_after_1st_rebill, IFNULL(
                      bc7.is_buyout_only_after_1st_rebill, 0
                    )
                  )
                )
              )
            )
          )
        )',
        'is_buyout_all' => 'IFNULL(
          bc1.is_buyout_all, IFNULL(
            bc2.is_buyout_all, IFNULL(
              bc3.is_buyout_all, IFNULL(
                bc4.is_buyout_all, IFNULL(
                  bc5.is_buyout_all, IFNULL(
                    bc6.is_buyout_all, IFNULL(
                      bc7.is_buyout_all, 0
                    )
                  )
                )
              )
            )
          )
        )',

        'single_rebill_id' => $singleRebillQuery,
        'single_sub_id_dup_phone' => $singleSubDuplicatedPhoneQuery,
        'source_name' => 'sources.name',
        'source_hash' => 'sources.hash',
        'source_postback_url' => 'sources.postback_url',
        'source_use_global_postback_url' => 'sources.use_global_postback_url',
        'source_is_notify_cpa' => 'sources.is_notify_cpa',
        'global_postback_url' => 'ups.postback_url',
      ])
      ->from(['s' => $subQuery])
      ->leftJoin('user_promo_settings ups', 's.user_id = ups.user_id')
      ->leftJoin('landing_operators lo', 'lo.operator_id = s.operator_id AND lo.landing_id = s.landing_id')
      ->leftJoin(['bc1' => $buyoutConditionsUserOperatorLandingQuery], 'bc1.user_id = s.user_id AND bc1.operator_id = s.operator_id AND bc1.landing_id = s.landing_id')
      ->leftJoin(['bc2' => $buyoutConditionsUserLandingQuery], 'bc2.user_id = s.user_id AND bc2.landing_id = s.landing_id AND bc2.operator_id = 0')
      ->leftJoin(['bc3' => $buyoutConditionsUserOperatorQuery], 'bc3.user_id = s.user_id AND bc3.landing_id = 0 AND bc3.operator_id = s.operator_id')
      ->leftJoin(['bc4' => $buyoutConditionsUserQuery], 'bc4.user_id = s.user_id AND bc4.landing_id = 0 AND bc4.operator_id = 0')
      ->leftJoin(['bc5' => $buyoutConditionsLandingOperatorQuery], 'bc5.user_id = 0 AND bc5.landing_id = s.landing_id AND bc5.operator_id = s.operator_id')
      ->leftJoin(['bc6' => $buyoutConditionsLandingQuery], 'bc6.user_id = 0 AND bc6.landing_id = s.landing_id AND bc6.operator_id = 0')
      ->leftJoin(['bc7' => $buyoutConditionsOperatorQuery], 'bc7.user_id = 0 AND bc7.landing_id = 0 AND bc7.operator_id = s.operator_id')
      ->leftJoin('sources', 's.source_id = sources.id')
      ->where([
        'and',

        // ПРОВЕРКА ВРЕМЕНИ ПОДПИСКИ
        [
          // ВРЕМЯ ПОДПИСКИ <= ВЫЧИСЛЕННОМУ ОТНОСИТЕЛЬНО ЗАДАНОЙ В УСЛОВИИ РЕБИЛЛА ЛИБО В НАСТРОЙКАХ
          '<=', 'time_on', new Expression('UNIX_TIMESTAMP() - 60 * 
          IFNULL(
            bc1.buyout_minutes, IFNULL(
              bc2.buyout_minutes, IFNULL(
                bc3.buyout_minutes, IFNULL(
                  bc4.buyout_minutes, IFNULL(
                    bc5.buyout_minutes, IFNULL(
                      bc6.buyout_minutes, IFNULL(
                        bc7.buyout_minutes, :minutes
                      )
                    )
                  )
                )
              )
            )
          )')
        ],


        ['<>', 's.user_id', 0], // указан юзер
        ['IS NOT', 's.user_id', null], // указан юзер
        [
          'or',
          'lo.buyout_price_rub <> 0',
          'lo.buyout_price_eur <> 0',
          'lo.buyout_price_usd <> 0'
        ] // указана хотя бы одна валюта
      ])
      ->having([
        'and',

        // ПО КОЛИЧЕСТВУ РЕБИЛЛОВ
        $this->statisticModule->isBuyoutAfter1stRebillOnly()
        ? [ // глобально разрешено выкупать ТОЛЬКО после 1го ребилла независимо от того сколько времени прошло
            'and',
            'single_rebill_id IS NOT NULL',
            $this->statisticModule->isAllowBuyoutWithOffs() ? '1=1' : '`s`.`so_time` = 0',
          ]
        : [
            'or',
            [// ЕСЛИ ЗАДАНА НАСТРОЙКА У ОПЕРАТОРА, то выкупаем ТОЛЬКО если кол-во ребиллов больше нуля
              'and',
              'is_buyout_only_after_1st_rebill = 1',
              'single_rebill_id IS NOT NULL'
            ],
            [// ЕСЛИ НЕ ЗАДАНА НАСТРОЙКА то выкупаем если нет отписок или есть ребиллы
              'and',
              'is_buyout_only_after_1st_rebill = 0',
              [
                'or',

                // иногда запрещаем выкупать с отпиской, даже не смотря на то есть ребилл или нет
                $this->statisticModule->isAllowBuyoutWithOffs() ? 'single_rebill_id IS NOT NULL' : '1=0',
                '`s`.`so_time` = 0',

                // если стоит выкупать все - выкупаем даже если есть отписка и нет ребиллов
                'is_buyout_all = 1',
              ]
            ]
          ],

        // ПО КОЛИЧЕСТВУ УНИКАЛЬНЫХ ТЕЛЕФОНОВ
        [
          'or',
          [
            'and',
            'is_buyout_only_unique_phone = 1',
            'single_sub_id_dup_phone IS NULL'
          ],
          'is_buyout_only_unique_phone = 0'
        ]
      ])
      ->params(['minutes' => $this->minutes])
      ->orderBy('s.hit_id');
  }

  /**
   * @param $sub
   * @return mixed
   */
  public function calculateSubscriptionPrice(&$sub)
  {
    $landingOperatorModels = $this->promoModule->api('landingOperators', [
      'conditions' => [
        'operator_id' => $sub['operator_id'],
        'landing_id' => $sub['landing_id'],
      ],
      'onlyActive' => false,
    ])->getResult()->getModels();

    /** @var LandingOperator $landingOperatorModel */
    $landingOperatorModel = reset($landingOperatorModels);
    $landingPrices = LandingOperatorPrices::create($landingOperatorModel, $sub['user_id']);
    $sub['price_rub'] = $landingPrices->getBuyoutPrice('rub');
    $sub['price_usd'] = $landingPrices->getBuyoutPrice('usd');
    $sub['price_eur'] = $landingPrices->getBuyoutPrice('eur');
    $sub['buyoutCurrency'] = $landingOperatorModel->getBuyoutCurrency();
    $sub['fixed_buyout_partner_profit'] = $landingPrices->getBuyoutFixCPA($this->getUserCurrency($sub['user_id']));
    $sub['personal_profit_updated_at'] = ArrayHelper::getValue($landingPrices->getPartnerPercents(), 'updated_at', 0);
    
    return $sub;
  }

  /**
   * @param $subs
   * @param $postbackHitIds
   */
  protected function insertSoldSubscriptions($subs, &$postbackHitIds)
  {
    if (empty($subs)) return;

    $this->stdout('> INSERT TO sold_subscriptions');

    $batchParams = [
      'hit_id',

      'currency_id',
      'real_price_rub',
      'real_price_eur',
      'real_price_usd',
      'reseller_price_rub',
      'reseller_price_eur',
      'reseller_price_usd',
      'price_rub',
      'price_eur',
      'price_usd',

      'profit_rub',
      'profit_eur',
      'profit_usd',
      'is_visible_to_partner',

      'time',
      'date',
      'hour',

      'stream_id',
      'source_id',
      'user_id',

      'landing_id',
      'operator_id',
      'platform_id',
      'landing_pay_type_id',
      'provider_id',
      'country_id'
    ];
    $batchArray = [];
    /* @var array хиты с информацией о подпикам по которым могу быть добавлены жалоы */
    $complainHitIds = [];

    foreach ($subs as $sub) {

      $this->getPartnerProfits($sub);

      $soldSub = [
        $sub['hit_id'],

        $sub['buyoutCurrency']['id'],
        $sub['price_rub'], //TODO price одинковый т.к. учитывается только процент партнера. Лишнии колонки можно удалить
        $sub['price_eur'],
        $sub['price_usd'],
        $sub['price_rub'],
        $sub['price_eur'],
        $sub['price_usd'],
        $sub['price_rub'],
        $sub['price_eur'],
        $sub['price_usd'],

        $sub['partner_profit_rub'],
        $sub['partner_profit_eur'],
        $sub['partner_profit_usd'],
        $sub['is_visible_to_partner'],

        time(),
        date('Y-m-d'),
        date('H'),

        $sub['stream_id'],
        $sub['source_id'],
        $sub['user_id'],

        $sub['landing_id'],
        $sub['operator_id'],
        $sub['platform_id'],
        $sub['landing_pay_type_id'],
        $sub['provider_id'],
        $sub['country_id']
      ];

      $complainHitIds[$sub['hit_id']] = [
        'landing_id' => $sub['landing_id'],
        'source_id' => $sub['source_id'],
        'operator_id' => $sub['operator_id'],
        'platform_id' => $sub['platform_id'],
        'landing_pay_type_id' => $sub['landing_pay_type_id'],
        'provider_id' => $sub['provider_id'],
        'country_id' => $sub['country_id'],
        'stream_id' => $sub['stream_id'],
        'user_id' => $sub['user_id'],
        'phone' => $sub['phone'],
      ];

      if ($sub['is_visible_to_partner'] && (int)$sub['source_is_notify_cpa']) {
        $postbackHitIds[] = $sub['hit_id'];
      }

      if (!empty($sub['fixed_buyout_partner_profit'])) {
        // Если для партнера задан фиксированный прайс для выкупа, то с каждым выкупом мы считаем корректировку.
        // Для этого данную подписку надо сохранять сразу, а не через batchInsert()
        Yii::$app->db->createCommand()
          ->insert(
            'sold_subscriptions',
            array_combine($batchParams, $soldSub))
          ->execute();
      } else {
        $batchArray[] = $soldSub;
      }


    }
    $this->addAutoComplains($complainHitIds);

    if (empty($batchArray)) return;
    Yii::$app->db->createCommand()
      ->batchInsert('sold_subscriptions', $batchParams, $batchArray)
      ->execute();
  }

  protected function buyoutSubscriptions()
  {
    $soldSubs = [];
    $sum = 0;
    $dataGroupedByLandings = [];

    foreach ($this->subscriptions as $sub) {
      $this->calculateSubscriptionPrice($sub);

      //Проверяем выкупилась ли уже подписка по этому телефону если стоит флаг выкупать только уникальные
      if (!empty($sub['phone'])
        && in_array($sub['phone'], ArrayHelper::getValue($this->_operatorPhones, $sub['operator_id'], []))
        && $sub['is_buyout_only_unique_phone'] == 1) continue;
      $this->_operatorPhones[$sub['operator_id']][] = $sub['phone'];

      $currency = $sub['buyoutCurrency']['code'];

      $soldSubs[] = $sub;
      $price = (float)$sub['price_' . $currency];
      $sum += $price;
      if (empty($dataGroupedByLandings[$sub['landing_id']])) {
        $dataGroupedByLandings[$sub['landing_id']] = ['sum' => 0, 'hits' => 0, 'landing_id' => $sub['landing_id']];
      }

      $dataGroupedByLandings[$sub['landing_id']]['sum'] += $price;
      $dataGroupedByLandings[$sub['landing_id']]['hits']++;
    }

    if (empty($soldSubs)) {
      return;
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
      // платные подписки
      if (!empty($soldSubs)) {
        $postbackHitIds = [];
        $this->insertSoldSubscriptions($soldSubs, $postbackHitIds);

        $postbackHitIds && Yii::$app->queue->push(
          Worker::CHANNEL_NAME,
          new Payload([
            'hitIds' => $postbackHitIds,
            'type' => Payload::TYPE_SUBSCRIPTION_SELL,
          ])
        );
      };

      if ($this->isRollback) {
        $transaction->rollBack();
        $this->stdout('+++++ DEBUG MODE ENABLED. TRANSACTION ROLLBACK +++++');
      } else {
        $transaction->commit();
      }
    } catch (\Exception $e) {
      $transaction->rollback();
      $this->stdout(sprintf('%s %s(%s)\n%s\n',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
      ));
    }
  }

  /**
   * @return bool
   */
  protected function finish()
  {
    $this->stdout('BUYOUT SUCCESSFULLY FINISHED');
    if ($this->isProfilerEnabled) {
      $this->profFlag('Done');
      $this->profPrint();
    }
    return true;
  }

  /**
   * @param $sub
   * @return array
   */
  public function getPartnerProfits(&$sub)
  {
    // Если пользователю не указана фиксированная цена за выкуп, то сумма дохода равна сумме сколько заплатил инвестор
    if (empty($fixedPrice = $sub['fixed_buyout_partner_profit'])) {
      $sub['partner_profit_rub'] = $sub['price_rub'];
      $sub['partner_profit_eur'] = $sub['price_eur'];
      $sub['partner_profit_usd'] = $sub['price_usd'];
      $sub['is_visible_to_partner'] = 1;
      $this->stdout(sprintf('   hit_id=%s is visible to partner (fixed price is not specified)', $sub['hit_id']));
      return $sub;
    }

    // валюта партнера
    $currency = $this->getUserCurrency($sub['user_id']);
    $this->stdout(sprintf('   hit_id=%s partner currency is %s', $sub['hit_id'], $currency));

    if ($this->getBuyoutSumDiff($sub, $currency) < 0
    ) {
      // не показываем партнеру
      $sub['partner_profit_rub'] = 0;
      $sub['partner_profit_eur'] = 0;
      $sub['partner_profit_usd'] = 0;
      $sub['is_visible_to_partner'] = 0;
      $this->stdout(sprintf('   hit_id=%s is NOT visible to partner', $sub['hit_id']));
      return $sub;
    }

    // показываем подписку партнеру
    $sub['partner_profit_rub'] = $this->getFixedPrice($fixedPrice, $currency, 'rub');
    $sub['partner_profit_eur'] = $this->getFixedPrice($fixedPrice, $currency, 'eur');
    $sub['partner_profit_usd'] = $this->getFixedPrice($fixedPrice, $currency, 'usd');
    $sub['is_visible_to_partner'] = 1;
    $this->stdout(sprintf('   hit_id=%s is visible to partner with fixed profit', $sub['hit_id']));
    return $sub;
  }


  /**
   * @param $userId
   * @return string
   */
  protected function getUserCurrency($userId)
  {
    if ($userCurrency = ArrayHelper::getValue($this->userCurrencies, $userId)) return $userCurrency;

    return $this->userCurrencies[$userId] = $this->paymentsModule
      ->api('userSettingsData', ['userId' => $userId])
      ->getResult()
      ->getCurrency();
  }

  /**
   * Считаем разницу за N дней между ценой выкупа
   * и профитом партнера (отфильтрованное по лендингу и оператору).
   *
   * @param $sub
   * @param $currency
   * @return float
   */
  protected function getBuyoutSumDiff($sub, $currency)
  {
    $personalProfitUpdatedAt = isset($sub['personal_profit_updated_at'])
      ? (int)$sub['personal_profit_updated_at']
      : 0;

    $fromDate = $personalProfitUpdatedAt >= $this->diffCalcFromTime
      ? Yii::$app->formatter->asDate($personalProfitUpdatedAt, 'php:Y-m-d')
      : $this->diffCalcFromDate;

    $query = (new Query())
      ->select('SUM(price_' . $currency . ') - SUM(profit_' . $currency . ') as diff')
      ->from('sold_subscriptions')
      ->where([
        'user_id' => $sub['user_id'],
        'operator_id' => $sub['operator_id'],
        'landing_id' => $sub['landing_id']
      ])
      ->andWhere(['>=', 'date', $fromDate]);

    $dbDiff = $query->scalar();

    $this->stdout(sprintf('   hit_id=%s [[getBuyoutSumDiff]] personalProfitUpdatedAt=%s diffCalcFromTime=%s fromDate=%s query=%s result=%s',
      $sub['hit_id'],
      $personalProfitUpdatedAt,
      $this->diffCalcFromTime,
      $fromDate,
      $query->createCommand()->getRawSql(),
      $dbDiff
    ));

    return isset($dbDiff) ? $dbDiff : 0;
  }

  /**
   * Является ли выкуп первым или уже есть с таким ленд-оператором
   * @param $sub
   * @return bool
   */
  protected function isFirstBuyout($sub)
  {
    $personalProfitUpdatedAt = isset($sub['personal_profit_updated_at'])
      ? (int)$sub['personal_profit_updated_at']
      : 0;

    $fromDate = $personalProfitUpdatedAt >= $this->diffCalcFromTime
      ? Yii::$app->formatter->asDate($personalProfitUpdatedAt, 'php:Y-m-d')
      : $this->diffCalcFromDate;

    $query = (new Query())
      ->select('id')
      ->from('sold_subscriptions')
      ->where([
        'user_id' => $sub['user_id'],
        'operator_id' => $sub['operator_id'],
        'landing_id' => $sub['landing_id']
      ])
      ->andWhere(['>=', 'date', $fromDate]);

    $id = $query->scalar();
    $this->stdout(sprintf('   hit_id=%s [[isFirstBuyout]] personalProfitUpdatedAt=%s fromTime=%s fromDate=%s query=%s result=%s',
      $sub['hit_id'],
      $personalProfitUpdatedAt,
      $this->diffCalcFromTime,
      $fromDate,
      $query->createCommand()->getRawSql(),
      $id
    ));

    return !$id;
  }

  /**
   * Получить сумму $sum в валюте $toCurrency, с учетом что $sum в валюте $fromCurrency
   *
   * @param $sum
   * @param $fromCurrency
   * @param $toCurrency
   * @return mixed
   */
  protected function getFixedPrice($sum, $fromCurrency, $toCurrency)
  {
    return $sum * $this->getCourse($fromCurrency, $toCurrency);
  }

  /**
   * @param $fromCurrency
   * @param $toCurrency
   * @return float
   */
  protected function getCourse($fromCurrency, $toCurrency)
  {
    if ($fromCurrency === $toCurrency) {
      return 1;
    }

    return $this->getPartnerCurrenciesProvider()->getCurrencies()->getCurrency($fromCurrency)->{'getTo' . $toCurrency}();
  }

  /**
   * @return PartnerCurrenciesProvider
   */
  protected function getPartnerCurrenciesProvider()
  {
    if (!$this->_partnerCurrenciesProvider) {
      $this->_partnerCurrenciesProvider = PartnerCurrenciesProvider::getInstance();
    }
    return $this->_partnerCurrenciesProvider;
  }

  /**
   * @inheritdoc
   */
  public function stdout($string)
  {
    Yii::warning($string, 'buyout'); // искать в console.log по [warning][buyout]
    return parent::stdout($string);
  }

  /**
   * @return Query
   */
  public function getSubscriptionQuery()
  {
    if (empty($this->query)) $this->initSubscriptionsQuery();
    return $this->query;
  }

  /**
   * @param int $minutes
   * @return $this
   */
  public function setMinutes($minutes)
  {
    $this->minutes = $minutes;
    return $this;
  }

  /**
   * Добавление моментальных жалоб
   * @param $complainHitIds array массив с ключами hit_id и информацией по подписке
   * которые нужно добавить по этим хитам, если они существуют
   */
  protected function addAutoComplains($complainHitIds)
  {
    try {
      $complainHits = Complain::getInstantOffsHitIds(array_keys($complainHitIds));
      foreach ($complainHits as $complainHit => $isMoment) {
        $this->stdout('Add auto complain for hit ' . $complainHit);
        Complain::add($complainHit, $complainHitIds[$complainHit], $isMoment);
      }
    } catch (\Exception $e) {
      Yii::error('Ошибка при сохранении авто-жалобы24 или моментальной отписки. complainHits:' . json_encode($complainHitIds) . $e->getMessage());
    }
  }


  /**
   * --------------------PROFILING ----------------------
   * TODO наверно стоит вынести в качестве сервиса Yii
   */

  /**
   * Call this at each point of interest, passing a descriptive string
   * @param $str
   */
  private function profFlag($str)
  {
    if (!$this->isProfilerEnabled) return;
    $this->profTiming[] = microtime(true);
    $this->profNames[] = $str;
  }

  // Call this when you're done and want to see the results
  private function profPrint()
  {
    if (!$this->isProfilerEnabled) return;
    $this->stdout('/---------------PROF-----------------/');
    $size = count($this->profTiming);
    $all_time = 0;
    for($i=0;$i<$size - 1; $i++)
    {
      $this->stdout($this->profNames[$i]);
      $this->stdout(sprintf('   %f', $this->profTiming[$i+1]-$this->profTiming[$i]));
      $all_time += $this->profTiming[$i+1]-$this->profTiming[$i];
    }
    $this->stdout("{$this->profNames[$size-1]} - $all_time");

    $this->stdout('Memory: ' . $this->convert(memory_get_usage(true)));
    $this->stdout('Memory Peak: ' . $this->convert(memory_get_peak_usage(true)) . '\n');
  }

  /**
   * @param $size
   * @return string
   */
  private function convert($size)
  {
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
  }
}