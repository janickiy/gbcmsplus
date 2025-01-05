<?php

return [
  'subs_day_group' => \mcms\statistic\tests\fixtures\SubsDayGroup::class,
  'hits_day_group' => \mcms\statistic\tests\fixtures\HitsDayGroup::class,
  'sold_subscriptions' => \mcms\statistic\tests\fixtures\SoldSubscriptions::class,
  'hits' => \mcms\statistic\tests\fixtures\Hits::class,
  'hit_params' => \mcms\statistic\tests\fixtures\HitParams::class,
  'search_subscriptions' => \mcms\statistic\tests\fixtures\SearchSubscriptions::class,
  'subscriptions' => \mcms\statistic\tests\fixtures\Subscriptions::class,
  'subscription_rebills' => \mcms\statistic\tests\fixtures\SubscriptionRebills::class,
  'onetime_subscriptions' => \mcms\statistic\tests\fixtures\OnetimeSubscriptions::class,
  // Statistic data by hours
  'statistic_data_by_hours_hits' => \mcms\statistic\tests\fixtures\statisticDataByHours\Hits::class,
  'statistic_data_by_hours_subscriptions' => \mcms\statistic\tests\fixtures\statisticDataByHours\Subscriptions::class,
  'statistic_data_by_hours_subscription_offs' => \mcms\statistic\tests\fixtures\statisticDataByHours\SubscriptionOffs::class,
  'statistic_data_by_hours_subscription_rebills' => \mcms\statistic\tests\fixtures\statisticDataByHours\SubscriptionRebills::class,
];