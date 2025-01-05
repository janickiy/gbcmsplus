<?php
use mcms\payments\models\wallet\Wallet;
use mcms\payments\Module;

return [
  Module::PARAM_WALLETS => [
    Wallet::WALLET_TYPE_WEBMONEY => mcms\payments\models\wallet\WebMoney::class,
    Wallet::WALLET_WIRE_IBAN => \mcms\payments\models\wallet\wire\iban\Wire::class,
    Wallet::WALLET_TYPE_YANDEX => mcms\payments\models\wallet\Yandex::class,
    Wallet::WALLET_TYPE_EPAYMENTS => mcms\payments\models\wallet\Epayments::class,
    Wallet::WALLET_TYPE_PAYPAL => mcms\payments\models\wallet\PayPal::class,
    Wallet::WALLET_TYPE_PAXUM => mcms\payments\models\wallet\Paxum::class,
    Wallet::WALLET_TYPE_CARD => \mcms\payments\models\wallet\Card::class,
    Wallet::WALLET_TYPE_PRIVATE_PERSON => \mcms\payments\models\wallet\PrivatePerson::class,
    Wallet::WALLET_TYPE_JURIDICAL_PERSON => \mcms\payments\models\wallet\JuridicalPerson::class,
    Wallet::WALLET_TYPE_QIWI => \mcms\payments\models\wallet\Qiwi::class,
    Wallet::WALLET_TYPE_CAPITALIST => \mcms\payments\models\wallet\Capitalist::class,
    Wallet::WALLET_TYPE_USDT => \mcms\payments\models\wallet\Usdt::class,
  ],
  // TRICKY Устарело
  Module::PARAM_AUTO_PAYMENT_WALLETS => [
  ]
];