<?php

use mcms\partners\Module;

return [
  'id' => 'partners',
  'class' => 'mcms\partners\Module',
  'name' => 'partners.main.module_partners',
  'messages' => '@mcms/partners/messages',
  'apiClasses' => [
    'getLogoImage' => \mcms\partners\components\api\LogoImage::class,
    'getAdminPanelLogoImage' => \mcms\partners\components\api\AdminPanelLogoImage::class,
    'getLogoEmailImage' => \mcms\partners\components\api\LogoEmailImage::class,
    'getFavicon' => \mcms\partners\components\api\Favicon::class,
    'publication' => \mcms\partners\components\api\Publication::class,
    'getEmailTemplate'  => \mcms\partners\components\api\EmailTemplate::class,
    'getProjectName'  => \mcms\partners\components\api\GetProjectName::class,
    'getCountOfTickets' => \mcms\partners\components\api\CountOfTickets::class,
  ]
];