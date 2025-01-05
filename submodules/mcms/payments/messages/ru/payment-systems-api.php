<?php

return [
  'available-recipients' => 'Поддерживаемые получатели',
  'require-settings' => 'Платежная система для выплат не настроена',
  'system-is-configured' => 'Система настроена',
  'paysystems-api-not-available-for-currency' => 'Автоматические выплаты не доступны для этой валюты',

  'attribute-id' => 'ID',
  'attribute-name' => 'Название',
  'attribute-code' => 'Код',
  'attribute-currency' => 'Валюта',
  'attribute-balance' => 'Баланс',

  'attribute-pursesrc' => 'Номер кошелька отправителя',
  'attribute-WMKwmFile' => 'Файл ключей WM Keeper',
  'attribute-WMKwmFilePassword' => 'Пароль для файла ключей WM Keeper',
  'attribute-WMCapitallerId' => 'WM Capitaller WMID (для получения баланса кошельков)',

  'attribute-card' => 'Номер карты отправителя',
  'attribute-exp_date' => 'Срок окончания действия карты',

  'attribute-certificateFile' => 'Файл сертификата',
  'attribute-certificateKey' => 'Ключ сертификата',
  'attribute-certificatePassword' => 'Пароль ключа',

  'get-credentials-paypal' => 'Инструкция для получения параметров подключения к API {link}',
  'get-api-credentials-paypal' => 'Инструкция для получения параметров API для получения баланса {link}',
  'get-credentials-paxum' => 'Для получения параметров подключения к API нужно:<br/>
   - зарегистрироваться на paxum https://www.paxum.com/payment/register.php?view=views/register.xsl<br>
   - включить доступ к API (нажать "Enable API") https://www.paxum.com/payment/apiSettings.php?view=views/apiSettings.xsl<br>
   - на email придет код для подтверждения<br>
   - скопировать код из email и ввести его на странице, где нажали "Enable API" и нажимаем "Confirm enable"<br>
   - на этой странице нажимаем "Generate New Shared Secret" -> "Continue"<br>
   - на email придет секретный ключ<br>
   - укажите email и полученный секретный ключ в форме выше<br>
   - на странице, где находится кнопка "Generate New Shared Secret" есть поле "Available IPs"; в него нужно ввести IP-адрес сервера<br>
   Готово!',
  'attribute-yandex-money-wallet' => 'Номер кошелька',
  'attribute-yandex-money-client-id' => 'Идентификатор приложения',
  'attribute-yandex-money-client-secret' => 'OAuth2 client_secret',
  'attribute-yandex-money-access-token' => 'Access token',
  'attribute-yandex-money-scope' => 'Список запрашиваемых прав',
  'attribute-yandex-money-redirect-uri' => 'Redirect URI',

  'get-access-token' => 'Получить access token',
  'fill-and-save-settings' => 'Заполните и сохраните настройки',

  'download-manual' => 'Скачать инструкцию',

  'settings-apply-to-group' => 'Применить на все валюты API',
  'settings-apply-to-group_hint' => 'Если включено, то настройки всех остальных валют будут перезатёрты настройками текущей валюты',
  'settings-apply-to-group_confirm' => 'Настройки всех остальных валют будут перезатёрты настройками текущей валюты. Продолжить?',

  // Epayments
  'attribute-partnerId' => 'ID партнерской записи',
  'attribute-partnerSecret' => 'Секретный ключ доступа к API',
  'attribute-sourcePurse' => 'Кошелек, с которого будет производиться выплата',
  'attribute-payPass' => 'Платежный пароль',

  // Paxum
  'attribute-email' => 'Е-mail на который был зарегистрирован доступ к сервису',
  'attribute-secretCode' => 'Секретный код для доступа к сервису',

  // Paypal
  'attribute-clientId' => 'ID для OAuth авторизации',
  'attribute-clientSecret' => 'Секретный ключ',
  'attribute-userName' => 'API логин',
  'attribute-password' => 'API пароль доступа',
  'attribute-signature' => 'Сигнатура для подключения',
];