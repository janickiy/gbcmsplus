<?php
return [
    'class' => \mcms\payments\components\paysystem_icons\PaysystemIcons::class,
    'iconWidgets' => [
        \mcms\payments\models\wallet\Card::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\Card::class,
            'defaultIcon' => '<span class="card-addition__icon icon-card1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
            <span class="path7"></span>
            <span class="path8"></span>
            <span class="path9"></span>
            <span class="path10"></span>
          </span>',
            'iconVisa' => '<span class="card-addition__icon icon-carts_visa">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
            <span class="path7"></span>
            <span class="path8"></span>
            <span class="path9"></span>
            <span class="path10"></span>
          </span>',
            'iconMc' => '<span class="card-addition__icon icon-carts_master">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
            <span class="path7"></span>
            <span class="path8"></span>
            <span class="path9"></span>
            <span class="path10"></span>
            <span class="path11"></span>
            <span class="path12"></span>
            <span class="path13"></span>
            <span class="path14"></span>
            <span class="path15"></span>
            <span class="path16"></span>
            <span class="path17"></span>
            <span class="path18"></span>
            <span class="path19"></span>
          </span>',
            'defaultIconSrc' => 'carts.svg',
            'iconVisaSrc' => 'carts_visa.svg',
            'iconMcSrc' => 'carts_master.svg',
        ],
        \mcms\payments\models\wallet\Epayments::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\Card::class,
            'defaultIcon' => '<i class="card-addition__icon icon-epayments12"></i>',
            'defaultIconSrc' => 'epayments.svg',
        ],
        \mcms\payments\models\wallet\JuridicalPerson::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-le1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
          </span>',
            'defaultIconSrc' => 'urlico.svg',
        ],
        \mcms\payments\models\wallet\Paxum::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-paxum1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
          </span>',
            'defaultIconSrc' => 'paxum.svg',
        ],
        \mcms\payments\models\wallet\PayPal::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-paypal1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
          </span>',
            'defaultIconSrc' => 'paypal.svg',
        ],
        \mcms\payments\models\wallet\PrivatePerson::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-ie1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
          </span>',
            'defaultIconSrc' => 'ip.svg',
        ],
        \mcms\payments\models\wallet\Qiwi::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<i class="card-addition__icon icon-qiwi1"></i>',
            'defaultIconSrc' => 'qiwi.svg',
        ],
        \mcms\payments\models\wallet\WebMoney::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<i class="card-addition__icon icon-webmoney1"></i>',
            'defaultIconSrc' => 'webmoney.svg',
        ],
        \mcms\payments\models\wallet\Yandex::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-yandex1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
          </span>',
            'defaultIconSrc' => 'yandex.svg',
        ],
        \mcms\payments\models\wallet\wire\iban\Wire::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-wire1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
            <span class="path7"></span>
            <span class="path8"></span>
            <span class="path9"></span>
            <span class="path10"></span>
            <span class="path11"></span>
            <span class="path12"></span>
            <span class="path13"></span>
          </span>',
            'defaultIconSrc' => 'wire.svg',
        ],
        \mcms\payments\models\wallet\Capitalist::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-capitalist1">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
            <span class="path6"></span>
            </span>',
            'defaultIconSrc' => 'capitalist.svg',
        ],
        \mcms\payments\models\wallet\Usdt::class => [
            'class' => \mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon::class,
            'defaultIcon' => '<span class="card-addition__icon icon-usdt1">
            <span class="path1"></span>
            <span class="path2"></span>
            </span>',
            'defaultIconSrc' => 'usdt.svg',
        ],
    ],
];