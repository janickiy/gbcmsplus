<?php

use mcms\promo\Module;

return [
  'id' => 'promo',
  'class' => Module::class,
  'name' => 'promo.main.module_name',
  'menu' => [
    'icon' => 'fa-lg fa-fw icon-promo',
    'label' => 'promo.menu.main',
    'events' => [
      \mcms\promo\components\events\SourceCreatedModeration::class,
      \mcms\promo\components\events\LinkCreated::class,
      \mcms\promo\components\events\LinkCreatedModeration::class,
      \mcms\promo\components\events\LandingUnblockRequestCreated::class,
    ],
    'items' => [
      ['label' => 'promo.menu.landings', 'url' => ['/promo/landings/index']],
      ['label' => 'promo.menu.landing_sets', 'url' => ['/promo/landing-sets/index']],
      [
        'label' => 'promo.menu.landing_unblock_requests',
        'url' => ['/promo/landing-unblock-requests/index'],
        'events' => [
          \mcms\promo\components\events\LandingUnblockRequestCreated::class,
        ],
      ],
      ['label' => 'promo.menu.domains', 'url' => ['/promo/domains/index']],
      [
        'label' => 'promo.webmaster_sources.main',
        'url' => ['/promo/webmaster-sources/index'],
        'events' => [
          \mcms\promo\components\events\SourceCreatedModeration::class,
        ],
      ],
      [
        'label' => 'promo.arbitrary_sources.main',
        'url' => ['/promo/arbitrary-sources/index'],
        'events' => [
          \mcms\promo\components\events\LinkCreatedModeration::class,
        ],
      ],
      ['label' => 'promo.streams.main', 'url' => ['/promo/streams/index']],
      ['label' => 'promo.menu.partner_programs', 'url' => ['/promo/partner-programs/index']],
      ['label' => 'promo.personal-profits.main', 'url' => ['/promo/personal-profits/index']],
      ['label' => 'promo.rebill-conditions.main', 'url' => ['/promo/rebill-conditions/index']],
      ['label' => 'promo.preland-defaults.main', 'url' => ['/promo/preland-defaults/index']],
      ['label' => 'promo.menu.references', 'items' => [
        ['label' => 'promo.menu.currencies', 'url' => ['/promo/currencies/index']],
        ['label' => 'promo.menu.countries', 'url' => ['/promo/countries/index']],
        ['label' => 'promo.menu.operators', 'url' => ['/promo/operators/index']],
        ['label' => 'promo.menu.providers', 'url' => ['/promo/providers/index']],
        ['label' => 'promo.menu.trafficback_providers', 'url' => ['/promo/trafficback-providers/index'], 'visible' => function() {
          return Yii::$app->user->identity->canManageTbProviders();
        }],
        ['label' => 'promo.menu.offer_categories', 'url' => ['/promo/offer-categories/index']],
        ['label' => 'promo.menu.landing_categories', 'url' => ['/promo/landing-categories/index']],
        ['label' => 'promo.platforms.main_short', 'url' => ['/promo/platforms/index']],
        ['label' => 'promo.traffic-types.main_short', 'url' => ['/promo/traffic-types/index']],
        ['label' => 'promo.landing-pay-types.main_short', 'url' => ['/promo/landing-pay-types/index']],
        ['label' => 'promo.landing-subscription-types.main_short', 'url' => ['/promo/landing-subscription-types/index']],
        ['label' => 'promo.ads-networks.main_short', 'url' => ['/promo/ads-networks/index']],
        ['label' => \mcms\promo\models\AdsType::LANG_PREFIX . 'main', 'url' => ['/promo/ads-types/index']],
      ]],
      ['label' => 'promo.menu.banners', 'url' => ['/promo/banners/index']],
    ]
  ],
  'events' => [
    \mcms\promo\components\events\CountryCreated::class,
    \mcms\promo\components\events\CountryUpdated::class,
    \mcms\promo\components\events\DisabledLandingsReplace::class,
    \mcms\promo\components\events\DomainAdded::class,
    \mcms\promo\components\events\DomainChanged::class,
    \mcms\promo\components\events\DomainBanned::class,
    \mcms\promo\components\events\SystemDomainAdded::class,
    \mcms\promo\components\events\SystemDomainBanned::class,
    \mcms\promo\components\events\LandingCategoryCreated::class,
    \mcms\promo\components\events\LandingCategoryUpdated::class,
    \mcms\promo\components\events\LandingCreated::class,
    \mcms\promo\components\events\LandingListCreated::class,
    \mcms\promo\components\events\LandingDisabled::class,
    \mcms\promo\components\events\LandingUnlocked::class,
    \mcms\promo\components\events\LandingUnblockRequestCreated::class,
    \mcms\promo\components\events\LandingUpdated::class,
    \mcms\promo\components\events\OperatorCreated::class,
    \mcms\promo\components\events\OperatorUpdated::class,
    \mcms\promo\components\events\ProviderRedirected::class,
    \mcms\promo\components\events\ProviderUpdated::class,
    \mcms\promo\components\events\SourceCreated::class,
    \mcms\promo\components\events\SourceCreatedModeration::class,
    \mcms\promo\components\events\LinkCreated::class,
    \mcms\promo\components\events\LinkCreatedModeration::class,
    \mcms\promo\components\events\LinkActivated::class,
    \mcms\promo\components\events\LinkRejected::class,
    \mcms\promo\components\events\SourceActivated::class,
    \mcms\promo\components\events\SourceRejected::class,
    \mcms\promo\components\events\SourceStatusChanged::class,
    \mcms\promo\components\events\StreamChanged::class,
    \mcms\promo\components\events\personal_profit\PartnerPersonalChanged::class,
    \mcms\promo\components\events\personal_profit\PartnerOperatorChanged::class,
    \mcms\promo\components\events\personal_profit\PartnerLandingChanged::class,
    \mcms\promo\components\events\personal_profit\ResellerPersonalChanged::class,
    \mcms\promo\components\events\module_settings\PartnerRebillPercentChanged::class,
    \mcms\promo\components\events\module_settings\PartnerBuyoutPercentChanged::class,
    \mcms\promo\components\events\ads_networks\AdsNetworkCreated::class,
    \mcms\promo\components\events\ads_networks\AdsNetworkUpdated::class,
    \mcms\promo\components\events\ads_networks\AdsNetworkDeleted::class,
    \mcms\promo\components\events\DisabledLandingsReplace::class,
    \mcms\promo\components\events\TrafficbackProviderCreated::class,
    \mcms\promo\components\events\TrafficbackProviderUpdated::class,
    \mcms\promo\components\events\DisabledLandingsReseller::class,
    \mcms\promo\components\events\DisabledLandingsListReseller::class,
    \mcms\promo\components\events\landing_sets\LandingsAddedToSet::class,
    \mcms\promo\components\events\landing_sets\LandingsRemovedFromSet::class,
  ],
  'messages' => '@mcms/promo/messages',
  'apiClasses' => [
    'operators' => \mcms\promo\components\api\OperatorList::class, // Yii::$app->getModule('promo')->api('operators')->getResult();
    'countries' => \mcms\promo\components\api\CountryList::class, // Yii::$app->getModule('promo')->api('countries')->getResult();
    'cachedCountries' => \mcms\promo\components\api\GetCachedCountries::class, // Yii::$app->getModule('promo')->api('cachedCountries')->getResult();
    'operatorIps' => \mcms\promo\components\api\OperatorIpList::class, // Yii::$app->getModule('promo')->api('operatorIps')->getResult();
    'personalProfit' => \mcms\promo\components\api\GetPersonalProfit::class, // Yii::$app->getModule('promo')->api('personalProfit')->getResult();
    'personalProfitForm' => \mcms\promo\components\api\PersonalProfitForm::class, // Yii::$app->getModule('promo')->api('personalProfitForm')->getResult();
    'trafficBlockForm' => \mcms\promo\components\api\TrafficBlockForm::class, // Yii::$app->getModule('promo')->api('trafficBlockForm')->getResult();
    'adsTypes' => \mcms\promo\components\api\GetAdsTypes::class, // Yii::$app->getModule('promo')->api('adsTypes')->getResult();
    'sources' => \mcms\promo\components\api\SourceList::class, // Yii::$app->getModule('promo')->api('sources')->getResult();
    'sourcesTypes' => \mcms\promo\components\api\GetSourceTypes::class, // Yii::$app->getModule('promo')->api('sourcesTypes')->getResult();
    'sourceStatuses' => \mcms\promo\components\api\GetSourceStatuses::class, // Yii::$app->getModule('promo')->api('sourceStatuses')->getResult();
    'profitTypes' => \mcms\promo\components\api\GetProfitTypes::class, // Yii::$app->getModule('promo')->api('profitTypes')->getResult();
    'sourceCreate' => \mcms\promo\components\api\SourceCreate::class, // Yii::$app->getModule('promo')->api('sourceCreate')->getResult();
    'sourceDelete' => \mcms\promo\components\api\SourceDelete::class, // Yii::$app->getModule('promo')->api('sourceDelete')->getResult();
    'getSource' => \mcms\promo\components\api\GetSource::class, // Yii::$app->getModule('promo')->api('getSource')->getResult();
    'editSource' => \mcms\promo\components\api\EditSource::class, // Yii::$app->getModule('promo')->api('editSource')->getResult();
    'mainCurrencies' => \mcms\promo\components\api\MainCurrencies::class, // Yii::$app->getModule('promo')->api('mainCurrencies')->getResult();
    'resellerCurrencies' => \mcms\promo\components\api\ResellerCurrencies::class, // Yii::$app->getModule('promo')->api('resellerCurrencies')->getResult();
    'source' => \mcms\promo\components\api\Source::class, // Yii::$app->getModule('promo')->api('source', ['hash' => 'sasdasfasfasf'])->getResult();
    'streams' => \mcms\promo\components\api\StreamList::class, // Yii::$app->getModule('promo')->api('streams')->getResult();
    'streamCreate' => \mcms\promo\components\api\StreamCreate::class, // Yii::$app->getModule('promo')->api('streams', ['name' => 'streamName', 'userId' => 1])->getResult();
    'domains' => \mcms\promo\components\api\DomainList::class, // Yii::$app->getModule('promo')->api('domains')->getResult();
    'domainCreate' => \mcms\promo\components\api\DomainCreate::class, // Yii::$app->getModule('promo')->api('domainCreate')->getResult();
    'domainTypes' => \mcms\promo\components\api\GetDomainTypes::class, // Yii::$app->getModule('promo')->api('domainTypes')->getResult();
    'linkCreate' => \mcms\promo\components\api\LinkCreate::class, // Yii::$app->getModule('promo')->api('linkCreate')->getResult();
    'landings' => \mcms\promo\components\api\LandingList::class, // Yii::$app->getModule('promo')->api('landings')->getResult();
    'landingCategories' => \mcms\promo\components\api\LandingCategoryList::class, // Yii::$app->getModule('promo')->api('landingCategories')->getResult();
    'cachedLandingCategories' => \mcms\promo\components\api\GetCachedLandingCategories::class, // Yii::$app->getModule('promo')->api('cachedLandingCategories')->getResult();
    'cachedOfferCategories' => \mcms\promo\components\api\GetCachedOfferCategories::class, // Yii::$app->getModule('promo')->api('cachedOfferCategories')->getResult();
    'trafficbackTypes' => \mcms\promo\components\api\GetTrafficbackTypes::class, // Yii::$app->getModule('promo')->api('trafficbackTypes')->getResult();
    'landingOperators' => \mcms\promo\components\api\LandingOperatorList::class, // Yii::$app->getModule('promo')->api('landingOperators')->getResult();
    'providers' => \mcms\promo\components\api\ProviderList::class, // Yii::$app->getModule('promo')->api('providers')->getResult();
    'platforms' => \mcms\promo\components\api\PlatformList::class,
    'payTypes' => \mcms\promo\components\api\LandingPayTypeList::class,
    'cachedLandingPayTypes' => \mcms\promo\components\api\GetCachedLandingPayTypes::class,
    'landingAccessTypes' => \mcms\promo\components\api\GetLandingAccessTypes::class,
    'landingUnblockRequestStatuses' => \mcms\promo\components\api\GetLandingUnblockRequestStatuses::class,
    'landingUnblockRequest' => \mcms\promo\components\api\GetLandingUnblockRequest::class,
    'landingUnblockRequestCreate' => \mcms\promo\components\api\LandingUnblockRequestCreate::class,
    'stream' => \mcms\promo\components\api\GetStream::class,
    'sourceById' => \mcms\promo\components\api\GetSourceById::class,
    'countryById' => \mcms\promo\components\api\GetCountryById::class,
    'providerById' => \mcms\promo\components\api\GetProviderById::class,
    'platformId' => \mcms\promo\components\api\GetPlatformById::class,
    'landingById' => \mcms\promo\components\api\GetLandingById::class,
    'landingPayTypeById' => \mcms\promo\components\api\GetLandingPayTypeById::class,
    'landingOperatorById' => \mcms\promo\components\api\GetLandingOperatorById::class,
    'url' => \mcms\promo\components\api\GetUrl::class,
    'landingsDropdown' => \mcms\promo\components\api\LandingsDropdownWidget::class,
    'ajaxLandingsDropdown' => \mcms\promo\components\api\AjaxLandingsDropdownWidget::class,
    'operatorsDropdown' => \mcms\promo\components\api\OperatorsDropdownWidget::class,
    'bannersDropdown' => \mcms\promo\components\api\BannersDropdownWidget::class,
    'streamsDropdown' => \mcms\promo\components\api\StreamsDropdownWidget::class,
    'sourcesDropdown' => \mcms\promo\components\api\SourcesDropdownWidget::class,
    'getLandingsByCategory' => \mcms\promo\components\api\GetLandingsByCategory::class,
    'resellerCanHidePromo' => \mcms\promo\components\api\ResellerCanHidePromo::class,
    'partnerCanViewPromo' => \mcms\promo\components\api\PartnerCanViewPromo::class,
    'trafficTypes' => \mcms\promo\components\api\TrafficTypes::class,
    'adsNetworks' => \mcms\promo\components\api\AdsNetworks::class,
    'ipList' => \mcms\promo\components\api\IpList::class,
    'mainCurrenciesWidget' => \mcms\promo\components\api\MainCurrenciesWidget::class,
    'rebillConditionsForm' => \mcms\promo\components\api\RebillConditionsForm::class,
    'rebillCorrectConditions' => \mcms\promo\components\api\RebillCorrectConditions::class,
    'banners' => \mcms\promo\components\api\Banners::class,
    'badgeCounters' => \mcms\promo\components\api\BadgeCounters::class,
    'fakeRevshareSettings' => \mcms\promo\components\api\FakeRevshareSettings::class,
    'tbEnabled' => \mcms\promo\components\api\TbEnabled::class,
    'userPromoSettings' => \mcms\promo\components\api\UserPromoSettings::class,
    'settings' => \mcms\promo\components\api\Settings::class,
    'sourceCopy' => \mcms\promo\components\api\SourceCopy::class,
    'trialOperators' => \mcms\promo\components\api\TrialOperators::class,
  ],
  'fixtures' => require(__DIR__ . '/fixtures.php'),
  'acceptedDomainZones' => ['com', 'net'],
];