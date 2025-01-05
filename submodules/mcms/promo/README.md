# mcms-promo
Модуль промо

Схема БД: http://dbdesigner.net/designer/schema/13851

### API модуля:

#### Получение списка операторов
`Yii::$app->getModule('promo')->api('operators')->getResult();`

#### Получение списка стран
`Yii::$app->getModule('promo')->api('countries')->getResult();`

#### Получение списка городов
`Yii::$app->getModule('promo')->api('cities')->getResult();`

#### Получение списка регионов
`Yii::$app->getModule('promo')->api('regions')->getResult();`

#### Получение типов форматов рекламы
`Yii::$app->getModule('promo')->api('adsTypes')->getResult();`

#### Получение параметров форматов рекламы
`Yii::$app->getModule('promo')->api('adsTypeParams')->getResult();`

#### Получение списка потоков
`Yii::$app->getModule('promo')->api('streams')->getResult();`

#### Получение списка доменов
`Yii::$app->getModule('promo')->api('domains')->getResult();`

#### Получение списка лендингов
`Yii::$app->getModule('promo')->api('landings')->getResult();`

#### Получение списка категорий лендингов
`Yii::$app->getModule('promo')->api('landingCategories')->getResult();`

#### Получение списка источников
`Yii::$app->getModule('promo')->api('sources')->getResult();`

#### Получение списка типов источников
`Yii::$app->getModule('promo')->api('sourcesTypes')->getResult();`

#### Удаление источника
```php
Yii::$app->getModule('promo')->api('sourceDelete', [
      'user_id' => 1, // обязательный
      'source_id' => 1, // обязательный
    ])->getResult();
```
#### Добавление нового источника

```php
Yii::$app->getModule('promo')->api('sourceCreate',[
      'user_id' => 1, // обязательный
      'post_data' => $form->getFormAttributes(), // обязательный
    ])->getResult();
```

#### Получение источника

```php
Yii::$app->getModule('promo')->api('getSource', [
     'user_id' => 1, // обязательный
     'source_id' => 1, // обязательный
    ])->getResult();
```

#### Редактирование источника

```php
Yii::$app->getModule('promo')->api('editSource', [
     'user_id' => 1, // обязательный
     'source' => 1, // обязательный
     'adstype' => 1, // обязательный
    ])->getResult();
```

#### Получение способов получения прибыли
`Yii::$app->getModule('promo')->api('profitTypes')->getResult();`

#### Получение списка IP операторов
`Yii::$app->getModule('promo')->api('operatorIps', $params)->getResult();`

Результат кэшируется:

* ключ кэша `operator_ips__SERIALIZED`
* теги кэша:
    * `operator_ips__operatoridOO`
    
> SERIALIZED - это параметры запроса, т.е. `serialize($params)`.

> OO - id оператора, если он был в параметрах запроса. Если не было, то OO = ''. 
 
    
#### Получение доходности для пользователя

```php
Yii::$app->getModule('promo')->api('personalProfit',[
      'userId' =>1, // обязательный
      'landingId' =>1, // необязательный
      'operatorId' =>1 // необязательный
    ])->getResult();
```

Получение происходит по приоритетности. Если не найден профит по связке (user_id, landing_id, operator_id) 
идет поиск по (user_id, landing_id), далее по (user_id, operator_id), далее по user_id, далее берутся значения 
по-умолчанию из настроек модуля.

Результат кэшируется:

* ключ кэша `personal_profit__userUU-landingLL-operatorOO`
* теги кэша:
    * `personal_profit__useridUU`
    * `personal_profit__module_percents`

> UU - это id юзера

> LL - это id лендинга

> OO - это id оператора

#### Получение списка основных валют

```php
Yii::$app->getModule('promo')
    ->api('mainCurrencies')
    ->getResult();
```

Вернёт массив валют. В качестве dataProvider используется ArrayDataProvider.


#### Виджет для работы с персональными процентами доходности

```php
Yii::$app->getModule('promo')
    ->api('personalProfitForm', ['userId' => $user->id])
    ->getResult();
```

userId - обязательный параметр

#### Получение информации об источнике по его хэшу

```php
Yii::$app->getModule('promo')
    ->api('source', ['hash' => 'sasdasfasfasf'])
    ->getResult();
```

Вернёт массив со всеми полями модели + relations.

Результат кэшируется:

* ключ кэша `source__SSSSS`

> SSSSS - это hash источника.

#### Получение ID методов оплаты для лендингов, сгруппированных по операторам и странам
```php
    $object = Yii::$app->getModule('promo')
    ->api('cachedLandingPayTypes', []);

    $object->getResult(); // Получение данных из кеша
    $object->getCountryPayTypes(); // Методы оплаты для стран
    $object->getOperatorPayTypes(); // Методы оплаты для операторов
    $object->invalidateCache(); // Очищение кеша
```

Результат кэшируется:

* ключ кэша `mcms.partners.promo.paytypes`

# Генератор демо данных, cli скрипт:

```
php yii promo/demo-data
```

# Синхронизация с моблидерс
Необходимо создать провайдера моблидерс вручную в админке - указать путь
url = `ссылка_потока_с_моблидерс&land_id={send_id}&p1={hit_id}&p2=source_id:{source_id}`

запустить команду
`php yii promo/sync-mobleaders userId`
для синхронизации данных с КП http://mobleaders.com

