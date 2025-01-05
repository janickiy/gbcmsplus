<?php

use mcms\statistic\Module;

return [
  'id' => 'statistic',
  'class' => 'mcms\statistic\Module',
  'name' => 'app.common.module_statistic',
  'menu' => [
    'icon' => 'fa-lg fa-fw icon-statistic',
    'label' => 'statistic.main.statistic',
    'items' => [
      ['label' => 'statistic.main.statistic', 'url' => ['/statistic/default/index'], 'isActionCheck' => 1],
      ['label' => 'statistic.main.detail', 'url' => ['/statistic/detail/subscriptions']],
      ['label' => 'statistic.main.graphical', 'url' => ['/statistic/default/graphical'], 'isActionCheck' => 1],
      ['label' => 'statistic.main.analytics', 'url' => ['/statistic/analytics/index']],
      ['label' => 'statistic.main.by_banners', 'url' => ['/statistic/banners/index']],
      ['label' => 'statistic.main.by_referrals', 'url' => ['/statistic/referrals/index']],
      ['label' => 'statistic.main.postbacks', 'url' => ['/statistic/postback/index']],
    ]
  ],
  'messages' => '@mcms/statistic/messages',
  'apiClasses' => [
    'mainStatistic' => \mcms\statistic\components\api\MainStatistic::class,
    'detailStatistic' => \mcms\statistic\components\api\DetailStatistic::class,
    'detailStatisticInfo' => \mcms\statistic\components\api\DetailStatisticInfo::class,
    'sourcesHitsCount' => \mcms\statistic\components\api\SourcesHitsCount::class,
    'sourcesLandingsConvert' => \mcms\statistic\components\api\SourcesLandingsConvert::class,
    'userDayGroupStatistic' => \mcms\statistic\components\api\UserDayGroupStatistic::class,
    'subscriptionsCount' => \mcms\statistic\components\api\SubscriptionsCount::class,
    'tbStatistic' => \mcms\statistic\components\api\TBStatistic::class,
    'activeReferrals' => \mcms\statistic\components\api\ActiveReferrals::class,
    'activePartners' => \mcms\statistic\components\api\ActivePartners::class,
    'soldSubscriptions' => \mcms\statistic\components\api\SoldSubscriptions::class,
    'labelStatisticEnable' => \mcms\statistic\components\api\LabelStatisticEnable::class,
    'moduleSettings' => \mcms\statistic\components\api\ModuleSettings::class,
    'analytics' => \mcms\statistic\components\api\AnalyticsApi::class,
    'referrals' => \mcms\statistic\components\api\ReferralsApi::class,
    'partnerReferrals' => \mcms\statistic\components\api\PartnerReferrals::class,
    'predictedStatToday' => \mcms\statistic\components\api\predict\PredictedStatTodayApi::class,
    'statFilters' => \mcms\statistic\components\api\StatFilters::class,
    'dashboard' => \mcms\statistic\components\api\Dashboard::class,
    'postbacks' => \mcms\statistic\components\api\PostbacksList::class,
    'complainsStatistic' => \mcms\statistic\components\api\ComplainsStatistic::class,
  ],
  'events' => [
    \mcms\statistic\components\events\PostbackEvent::class,
  ],
  'fixtures' => require(__DIR__ . '/fixtures.php')
];
