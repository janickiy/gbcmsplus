<?php

use mcms\pages\models\PartnerCabinetStyle;

return [
  'id' => 'pages',
  'class' => 'mcms\pages\Module',
  'name' => 'app.common.module_pages',
  'menu' => [
    'icon' => 'fa-lg fa-fw icon-nav-close',
    'label' => 'app.common.module_pages',
    'items' => [
      ['label' => 'pages.main.list_of_pages', 'url' => ['/pages/pages/index']],
      ['label' => 'pages.faq.faq', 'url' => ['/pages/faq/index']],
      ['label' => 'pages.faq.faq_categories', 'url' => ['/pages/faq-categories/index']],
      ['label' => PartnerCabinetStyle::LANG_PREFIX . 'styles_menu', 'url' => ['/pages/partner-cabinet-styles/index']],
    ]
  ],
  'events' => [
    \mcms\pages\components\events\PageCreateEvent::class,
    \mcms\pages\components\events\PageUpdateEvent::class,
    \mcms\pages\components\events\PageDeleteEvent::class,
    \mcms\pages\components\events\FaqCreateEvent::class,
    \mcms\pages\components\events\FaqUpdateEvent::class,
  ],
  'messages' => '@mcms/pages/messages',
  'apiClasses' => [
    'pages' => \mcms\pages\components\api\PagesList::class, // Yii::$app->getModule('pages')->api('pages')->getResult();
    'GetCachedVisibleFaqList' => \mcms\pages\components\api\GetCachedVisibleFaqList::class, // Yii::$app->getModule('pages')->api('GetCachedVisibleFaqList')->getResult();
    'pagesWidget' => \mcms\pages\components\api\PagesWidget::class,
    'partnerCabinetStyle' => \mcms\pages\components\api\PartnerCabinetStyleApi::class,
  ],
  'fixtures' => require(__DIR__ . '/fixtures.php')
];