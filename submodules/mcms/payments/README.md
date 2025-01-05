
### API модуля
#### Настройка пользователя
```php
Yii::$app->getModule('payments')->api('userSettings', ['userId' => $user->id])->getResult();
```

#### Покупка домена
```php
Yii::$app->getModule('payments')->api('buyDomain', [
  'userId' => $user->id, //обязательный
  'paymentAmount' => 200, //обязательный,
  'description' => 'Покупка домена test.dev'
])->getResult();
```

#### Задать пользователю валюту, запись настроек по-умолчанию при регистрации пользователя
```php
Yii::$app->getModule('payments')->api('setUserCurrency', [
  'userId' => 1,
  'currency' => 'rub'
])->getResult();
```

#### Пролучить валюту пользователя
```php
Yii::$app->getModule('payments')->api('getUserCurrency', [
  'userId' => 1,
])->getResult();
```

#### Запрос досрочной выплаты
```php
Yii::$app->getModule('payments')->api('requestEarlyPayment', [
  'userId' => 1,
  'paymentRequests' => [
    ['wallet_type' => 2 (wallet_type из таблицы user_wallets), 'amount' => 1000],
    ['wallet_type' => 1 (wallet_type из таблицы user_wallets), 'amount' => 2000],
])->getResult();
```

#### Баланс пользователя
```php
Yii::$app->getModule('payments')->api('userBalance', [
  'userId' => 1
])->getResult();
```

#### Настройки пользователя
```php
Yii::$app->getModule('payments')->api('userSettingsData', [
  'userId' => 1
])->getResult();
```

#### Выплаты за последную неделю, месяц, всего
```php
Yii::$app->getModule('payments')->api('userPaymentsSummary', [
  'userId' => 1
])->getResult();
```

#### Получение максимально возможной суммы выкупа подписок
Учитывается реинвест, если у инвестора он включен
```php
Yii::$app->getModule('payments')->api('investorBuyout', [
  'userId' => 1, //обязательный
])->getAvailableAmount();
```

#### Выкуп подписок инвестором
$amount - сумма выкупа, обязательный параметр
$description - описание, опциональный параметр
```php
Yii::$app->getModule('payments')->api('investorBuyout', [
  'userId' => 1, //обязательный
])->buyout($amount, $description);
```

#### Получение доходов рефералов
```php
$params = array_merge(Yii::$app->request->queryParams, [
  'userId' => $userId, // Обязательный параметр, для какого пользователя смотрим рефералов
  'currency' => $currency, // Валюта пользователя
]);
$referralsGroupedBalance = Yii::$app->getModule('payments')->api('referralsGroupedBalance', $params);
$dataProvider = $referralsGroupedBalance->setResultTypeDataProvider()->getFullData()->getResult();
$searchModel = $referralsGroupedBalance->getSearchModel();
```

#### Получение доходов рефералов за сегодня
```php
$params = array_merge(Yii::$app->request->queryParams, [
  'userId' => $userId, // Обязательный параметр, для какого пользователя смотрим рефералов
  'currency' => $currency, // Валюта пользователя
]);
$referralsGroupedBalance = Yii::$app->getModule('payments')->api('referralsGroupedBalance', $params);
$dataProvider = $referralsGroupedBalance->setResultTypeDataProvider()->getTodayData()->getResult();
$searchModel = $referralsGroupedBalance->getSearchModel();
```

#### Получение списка рефералов
```php
$params = array_merge(Yii::$app->request->queryParams, [
  'userId' => $userId, // Обязательный параметр, для какого пользователя смотрим рефералов
  'currency' => $currency, // Валюта пользователя
]);
$referralsGroupedBalance = Yii::$app->getModule('payments')->api('referralsGroupedBalance', $params);
$dataProvider = $referralsGroupedBalance->setResultTypeDataProvider()->getUsersData()->getResult();
$searchModel = $referralsGroupedBalance->getSearchModel();
```

### Получение суммированного дохода всех рефералов
```php
$params = array_merge(Yii::$app->request->queryParams, [
  'userId' => $userId, // Обязательный параметр, для какого пользователя смотрим рефералов
  'currency' => $currency, // Валюта пользователя
]);
$totalAmount = Yii::$app->getModule('payments')->api('referralsGroupedBalance', $params)->getTotalAmount();
```

#### Получение процента по рефералам пользователя
```php
Yii::$app->getModule('payments')->api('userSettingsData', [
  'userId' => 1
])->getReferralPercent();
```