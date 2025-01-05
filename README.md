# gbplus

Используется фреймворк Yii2 [Документация](https://github.com/yiisoft/yii2/blob/master/docs/guide-ru/README.md)

##### Необходимые зависимости
* docker
* docker-compose
* [git](https://help.github.com/articles/set-up-git/)

---

## Установка

```


### Заворачивание доменов на локалхост
При работе с локальной копией необходимо добавить в hosts-файл системы домены, через которые будет осуществляться взаимодействие:
```
127.0.0.1 modulecms.lc modulecms-test.lc mcms-api-handler.lc mcms-ml-handler.lc
```


### Запуск контейнеров
Далее запускаем `docker-compose.yml` из корня основного проекта командой:
```
docker-compose up -d
```

В случае успеха листинг `docker ps` выведет такой список контейнеров:
```
CONTAINER ID   IMAGE                          COMMAND                  CREATED       STATUS                   PORTS                            NAMES
965bd1af8e83   dockerhub.wapdev.org/mcms       "/bin/bash /entrypoi…"   2 hours ago   Up 2 hours               0.0.0.0:80->80/tcp, :::80->80/tcp                            mcms-main
52c5a089ae96   yandex/clickhouse-server       "/entrypoint.sh"         2 hours ago   Up 2 hours               0.0.0.0:8123->8123/tcp, :::8123->8123/tcp, 9000/tcp, 0.0.0.0:9004->9004/tcp, :::9004->9004/tcp, 9009/tcp                            clickhouse
8b9f749bf765   dockerhub.wapdev.org/rabbitmq   "docker-entrypoint.s…"   2 hours ago   Up 2 hours               4369/tcp, 0.0.0.0:5672->5672/tcp, :::5672->5672/tcp, 0.0.0.0:5692->5692/tcp, :::5692->5692/tcp, 5671/tcp, 15691-15692/tcp, 25672/tcp, 0.0.0.0:15672->15672/tcp, :::15672->15672/tcp   rabbitmq
b21e998ead29   memcached                      "docker-entrypoint.s…"   2 hours ago   Up 2 hours               0.0.0.0:11211->11211/tcp, :::11211->11211/tcp                             memcache
c8cea4142e1f   mariadb:10.7                   "docker-entrypoint.s…"   2 hours ago   Up 2 hours               0.0.0.0:3306->3306/tcp, :::3306->3306/tcp                             mysql
```

В случае возникновения ошибок необходимо проверить, что необходимые порты не заняты другими сервисами (к примеру, другими инстансами MySQL-сервера/Nginx/Apache/etc):
```
80
3306
8123
9004
5672
5692
11211
15672
```

### Инициализация проекта
Заходим для дальнейших операций внутрь

```
docker exec -it mcms-main bash
```

Подтягивание библиотек composer'а и инициализация (с выбором Development среды, вариант ответа `0`):
```
composer install
php init
cd mcms-api-handler
composer install
```

Поднимаем вновь в основную папку и выполняем миграции:
```
mysql -h db -u root -proot -e "CREATE DATABASE modulecms"
php yii migrate --all --interactive=0
```

### Доступы к проекту
Интерфейс доступен по адресу [http://modulecms.lc/users/site/login/](http://modulecms.lc/users/site/login/), для входа используем админ-учетку из списка заготовленных:
```
admin 1qazxsw2
root  1qazxsw2
partner 1qazxsw2
reseller 1qazxsw2
```

Далее лишь остается на странице [http://modulecms.lc/admin/modmanager/modules/available-list/](http://modulecms.lc/admin/modmanager/modules/available-list/) включить необходимые модули

---

# Порядок работы над проектом
В проекте весь код разделен на сабмодули. Описание разработки модулей находится ниже.

Весь новый функционал должен пушиться в бранчи и должны создаваться пул-реквесты. После этого будет проводится ревью кода. Названия бранчей должно начинаться в "wip-" т.е. work in progress. После апрупа пул-реквеста старая ветка должна удаляться.

В каждом репозитарии будут 2 основные ветки, а именно master и develop. Пул реквесты с новым функционалом нужно делать в develop.

Хотфиксы нужно делать через git flow.

Код будет разворачиваться через CI. CI будет прогонять тесты и автоматически деплоить код.

При коммите проекта вместе коммитятся указания на коммиты сабмодулей. Чтобы не было проблем, первоначально необходимо коммитить сабмодули, а потом только сам проект.
Командой submodule update сабмодули переходят в то состояние, которое было указано в коммите проекта. Т.е. при переключении веток в проекте достаточно набирать submodule update, чтобы модули были актуальными для текущей ветки.
Если при разработке в своей wip-* ветке вам необходимо переключиться на другую ветку в проекте, то сперва нужно все изменения сабмодулей закоммитить, иначе submodule update затрет ваши изменения без предупреждения.

TODO: дополнить

# Разработка модулей
Для того, чтобы создать структуру модуля, нужно использовать gii.
Неймспейс модулей будет следующим: mcms\<имя модуля>.

Сабмодули должны быть развернуты в папку `sabmodules/mcms/<название модуля>`


Сабмодули должны расширять `\mcms\common\module\Module`

Если вы хотите запушить новый код, то достаточно перейти в папку с модулем и выполнить `git pull`

##### Миграции
Для того, чтобы создать миграцию нужно выполнить `php yii migrate/create <название миграции> --sm="<модуль>"` или
`php yii migrate/create <название миграции> --migrationPath="@mcms/<модуль>/migrations"`

Примеры накатывания и откатывания миграций:

`php yii migrate/up` или `php yii migrate` - поиск новых миграций из папки по-умолчанию ('@common/migrations')

`php yii migrate/down` - откат одной миграции из папки по-умолчанию ('@common/migrations')

`php yii migrate/up --migrationPath="@mcms/<модуль>/migrations"` - поиск  и выполнение новых миграций модуля <модуль>

`php yii migrate/up --all` - поиск новых миграций всех модулей, включая папку миграций по-умолчанию

`php yii migrate/down --migrationPath="@mcms/<модуль>/migrations"` - откатит последнюю миграцию модуля <модуль>

`php yii migrate/down --all` - откатит одну последнюю миграцию из всех модулей, включая папку миграций по-умолчанию

Флаг `--all` нужен для того, чтобы осуществлять рекурсивный поиск миграций по следующим местам:

* папки активных модулей
* папки из настройки `Yii::$app->params['migrationLookup']`
* папка migrationPath по-умолчанию ('@common/migrations')

Настройка `Yii::$app->params['migrationLookup']` нужна для указания миграций для расширений. 
Например для работы rbac нужны миграции из '@yii/rbac/migrations', но при выполнении команды `php yii migrate/up --all` данных миграций там не будет.
Поэтому в массив Yii::$app->params надо добавить путь '@yii/rbac/migrations'. 

##### Создание консольных комманд в модуле
Для начала нужно включить ваш модуль в `console` в конфиге.
Далее нужно создать папку `commands` в модуле, и там разместить контроллеры, которые будут выполняться в cli.

При инициализации модуля указать controllerNamespace:
```php
public function init()
  {
    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\<module>\commands';
    }
  }
```

Далее надо руководстоваться [документацией](https://github.com/yiisoft/yii2/blob/master/docs/guide-ru/tutorial-console.md)

##### Взаимодействие между модулями (api модулей)
Например, чтобы реализовать получение операторов из модуля промо, необходимо сделать следующее:
1) В конфиге модуля promo указать название операции и путь к классу:
```php
'apiClasses' => [
    'operators' => '\mcms\promo\components\api\OperatorList',
  ]
```
2) В самом классе OperatorList:
```php
class OperatorList extends ApiResult
{
  public function init($params)
  {
    $this->prepareDataProvider(new OperatorSearch(), $params);
  }
}
```
класс OperatorSearch - стандартный класс yii, который можно сгенерить через gii. В классах типа *Search метод `search()` принимает на входе параметры фильтрации и возвращает объект `ActiveDataProvider`. На вход в метод `api()` можно отправлять в массиве параметров данные параметры для фильтрации моделей.

Удобно, что такой класс генерится обычным gii (для работы фильтра для Grid) и в штатном режиме будет работать из коробки.

3) Теперь чтобы получить операторов из модуля promo, можно использовать следующую команду:

```php
$data = Module::api('operators', [
      'conditions' => ['country_id' => 5],
      'pagination' => ['pageSize'=>3, 'page' => 2],
    ]);
$result = $data->getResult();
```
Module здесь - инстанция модуля promo. Переменная $data содержит объект OperatorList.

В случае ошибок `$data->getErrors();`

Перед выводом `getResult` можно определить тип вывода результата: `$data->setResultTypeDataProvider();`

Если нужна будет своя реализация вывода результатов (не через модель Search), то можно переопределить нужные методы, в том числе getResult().




# DynamicActiveRecord
Для того, чтобы прозрачно работать с дополнительными полями необходимо следовать следующими правилами:

- Создать миграцию, которая создаст таблицу с раширенными полями
- Обновить основную модель, заэкстендить ее от `mcms\common\DynamicActiveRecord`
- Реализоват метод `getModule`, который должен выозвращать инстанс `\mcms\common\module\Module`
- Создать связь

        `
            public function getParams()
            {
                return $this->hasOne(UserParam::class, ['user_id' => 'id']);
            }
        `

- Определить protected свойство `$additionalFieldsRelationName` со значением, которое вы указали в связи, в нашем случае это `params`

##### Пример
    `
        $model = new User();
        $model->username = 'davdimasssq';
        $model->email = 'testq@test.com';
        $model->phone = 'eierwre';
        $model->scenario = 'phone';
        $model->save();
    `
`phone` хранится в user_params

После того как мы сохраняем модеть юзеров, UserParams автоматически сохраняется

Получить это поле можно таким образом: `$model->phone`

##### Формы
Для удобства работой с формами используется [kartik form builder](https://github.com/kartik-v/yii2-builder) 

Для того, чтобы сгенерировать атрибуты формы для kartic, нужно следовать следующими шагами

* В модели определить свойство formAttributes. Он должен хранить массив, ключем которого является название поля, значением массив с параметрами для kartic


    `
     $this->formAttributes = [
         'email' => ['type' => Form::INPUT_TEXT],
         'username' => ['type' => Form::INPUT_TEXT],
         'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $statuses],
         'password' => ['type' => Form::INPUT_PASSWORD],
     ];
    `

* Для того, чтобы показывать определенные поля в разных сценариях, надо определить сами сценарии. Например, нам надо показывать username, email, status, language в одной форме, а username, email, status, language, password в другой. Для этого определим сценарии в модели
    
    
    `
        public function scenarios()
        {
            return array_merge(parent::scenarios(), [
                'create' => ['username', 'email', 'password', 'status', 'language'],
                'edit' => ['username', 'email', 'status', 'language'],
                'view' => ['username', 'email', 'status', 'language']
            ]);
        }
    `

* Затем надо задать сценарий. Делается это таким образом: `$model->scenario = 'create';`
* После этого надо заюзать trait `mcms\common\traits\model\FormAttributes` в модели. Это можно сделать следующим образом:


    `
        use common\components\traits\ModelFormAttributes;
        class User extents Model 
        {
            use ModelFormAttributes;
            
            ...
        }
    `
    
* Далее, нам надо вывести форму, пример:


    `
    {% set form = kartikActiveFormBegin({
       'id' : 'create-user-form',
       'options' : {'class' : 'form-horizontal col-xs-12'},
    }) %}
    
    
    {{ kartikFormWidget({'model': model, 'form': form, 'attributes': model.getFormAttributes()})|raw }}
    
    <div class="form-group">
      <input type="submit" value="Сохранить" class="btn btn-primary"/>
    </div>
    
    
    {{ void(kartikActiveFormEnd()) }}
    `



# Настройки модулей
Для того, чтобы определить настройки модудля, которые можно редактировать из админки, нужно использовать класс `common\components\module\settings\Repository`


##### Пример конфигурационного файла
    `
        [
            'settings' => (new \common\components\module\settings\Repository())
                ->set(
                    (new \common\components\module\settings\Integer())
                        ->setName('Количество попыток входа в систему, после которого будет показываться каптча')
                        ->setValue(0)
                        ->setKey(\mcms\user\Module::SETTINGS_CAPTCHA_SHOW_AFTER)
                )
                ->set(
                    (new \common\components\module\settings\Boolean())
                        ->setName('Вкл/Выкл логирования заходов пользователей в систему')
                        ->setValue(false)
                        ->setKey(\mcms\user\Module::SETTINGS_AUTH_LOG)
                )
        ]
    `
    
Для `mcms\common\module\settings\FileUpload` нужно передать в конструктор айди модуля (строка), для которого будет загружаться файл

##### Получение значения из настроек модуля
```
Yii::$app->getModule('some_module')->settings->getValueByKey(Module::SETTINGS_SOME_SETTING),
```
Рекомендуется всегда оборачивать получение значений из настроек модуля в методы класса Module.
Например:
```
class Module {
  /**
   * Получить список ролей соответствующих менеджерам.
   * @return array
   */
  public function getManagerRoles()
  {
    return explode(',', Yii::$app->getModule('users')->settings->getValueByKey(static::SETTINGS_MANAGER_ROLES));
  }
}
```

# Добавление файлы сообщений модуля
#### Инициализация сообщений в модуле
```
'messages' => '@mcms/user/messages'
```
Пример структуры файлов сообщений в модуле
```
messages
    ru
        auth.php
        registration.php
    es
        auth.php
```
В ключах следует использовать текст в нижнем регистре без пробелов.
Для избежания ошибок чтения сообщений нельзя использовать в ключах массива точку.

Правильно
```
<?php
 return array (
  'hello,_{username}' => 'Приветствую, {username}.'
);
```
Неправильно
```
<?php
 return array (
  'hello._{username}' => 'Приветствую. {username}'
);
```
Использование в шаблонизаторе twig.
```
{{ "auth.hello,_{username}!" | t }}
```
Внутри модуля сообщения доступны по ключу <файл сообщения>.<сообщение>.
Также это сообщение может быть доступно по <идентификатор модуля>.<файл сообщения>.<сообщение> из любого модуля.
```
{{ "users.auth.hello,_{username}!" | t }}
```

# Мультиязычность
Мультиязычность использует текущий массив элементов форм. Чтобы элемент формы стал мультиязычным нужно добавить `multilang => true`. Напрмимер:
    
    `
        'header' => [
            'type' => Form::INPUT_TEXT,
            'multilang' => true,
        ],
        'template' => [
            'type' => Form::INPUT_TEXTAREA,
            'multilang' => true
        ],
        'notification_type' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => [
                1 => \Yii::t('notification_types', 'Browser'),
                2 => \Yii::t('notification_types', 'Email'),
                3 => \Yii::t('notification_types', 'SMS'),
            ]
        ]
    `
    
Так же, нужно помнить о добавлении полей формы в сценарии


    `
      public function scenarios()
      {
        return array_merge(parent::scenarios(), [
          ...
          self::SCENARIO_EDIT_TEMPLATE => [
            'template',
            'header',
            'type',
            'notification_type',
            'to',
            'from'
    
          ],
          self::SCENARIO_VIEW_TEMPLATE => [
            'template',
            'header',
            'type',
            'notification_type',
            'to',
            'from'
          ],
        ]);
      }
    `
    
и определить сценарий в модели `$notificationModel->scenario = $notificationModel::SCENARIO_EDIT_TEMPLATE;`


##### Как построить мультиязычные формы
* В модели надо заюзать трейт `mcms\common\traits\model\MultiLang`
* Передать во вьюху модель 


    `
        return $this->render('template.twig', [
            'model' => $notificationModel
        ]);
    `


* Во вьюхе заюзать виджет `multilangFormWidget`


    `
        {{ multilangFormWidget({
            'model' : model
        })|raw }}
    `
    
* Хендлер формы находится в самом виджете, т.е. нет необходимости писать хендлер мультиязычной формы

##### Как построить мультиязычные формы НОВЫЙ ВАРИАНТ
* Extends модели от `submodules/mcms/common/multilang/MultiLangModel.php`
* Указать мультиязычные аттрибуты формы:

```
 public function getMultilangAttributes()
 {
   return [
     'name', 'text', 'seo_title', 'seo_keywords', 'seo_description'
   ];
 }
```

* Указать правила валидации мультиязычных полей (в базу пишется как serialize массив, поэтому свои правила валидации):

```
 public function rules()
  {
    return [
      [['name'], 'validateArrayRequired'],
      [['seo_title', 'seo_keywords'], 'validateArrayString'],
      [['name', 'text', 'seo_description'], 'validateArrayString'],
    ];
  }
```

* Во вьюхе заюзать виджет `submodules/mcms/common/multilang/widgets/multilangform/MultiLangForm.php`
или в twig `newMultilangFormWidget` (указываем снова необходимые поля для мультиязычности и располагаем их в любом месте формы)

```
    MultiLangForm::widget([
      'model' => $model,
      'form' => $form,
      'attributes' => [
        'name' => ['type' => Form::INPUT_TEXT],
        'text' => ['type' => Form::INPUT_WIDGET,
          'widgetClass' => 'vova07\imperavi\Widget', 'options' => [
            'settings' => [
              'minHeight' => '300px',
              'imageUpload' => Url::toRoute(['pages/image-upload/']),
              'imageManagerJson' => Url::toRoute(['pages/images-get/']),
              'plugins' => ['imagemanager', 'fullscreen']
            ]
          ]
        ],
        'seo_title' => ['type' => Form::INPUT_TEXT],
        'seo_keywords' => ['type' => Form::INPUT_TEXT],
        'seo_description' => ['type' => Form::INPUT_TEXTAREA, 'options' => ['rows' => 6]]
      ]
    ]);
```

* При выборке данных автоматически выбирается тот язык на котором просматриваем то есть `$model->name` - вернет просто текст на русском если у вас язык приложения русский

# Добавление подключаемых моделей в bootstrap
Это может пригодится для установки хендлера событий и других нужд, когда требуется проинициализировать данные перед выполнением
Чтобы проинициализировать подключаемый модуль нужно добвть в его конфиг `preload => true`.

# AJAX форма
Пример вызова:

```twig

{{ use('mcms/common/form/AjaxActiveForm') }}

{% set form = ajax_active_form_begin({
  'id' : 'personalProfitForm',
  'action' : {'0':'/promo/personal-profits/update-modal', 'id' : model.id},
  'ajaxSuccess' : jsExpression('function(response){alert("custom Success")}'),
  'ajaxError' : jsExpression('function(response){alert("custom Error")}')
}) %}

  {{ form.errorSummary(model, {'class' : 'alert alert-danger'}) | raw }}

  {{ form.field(model, 'rebill_percent') | raw }}
  {{ form.field(model, 'buyout_percent') | raw }}

  {{ html.button('app.common.Close' | t, {'class' : 'btn btn-default', 'data-dismiss' : 'modal'}) | raw }}

  {{ html.submitButton(
    'app.common.Save' | t,
    {'class' : 'btn ' ~ (model.isNewRecord ? 'btn-success' : 'btn-primary')}
  ) | raw }}

{{ ajax_active_form_end() }}

```

Если не определить свойства ajaxSuccess и ajaxError, будут применены функции по-умолчанию в виджете.

Пример контроллера:

```php
public function actionUpdateModal($id)
  {
    $model = $this->findModel($id);

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        $model->save();
        return true;
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form_modal.twig', [
      'model' => $model,
      'select2InitValues' => $this->getSelect2InitValues($model)
    ]);
  }
```

# Кнопки {update-modal} {view-modal} для GridView:
Форму update лучше юзать эту: mcms/common/form/AjaxActiveForm (читай описание выше)

Пример использования:
```twig
{
      'class': 'mcms\\common\\grid\\ActionColumn',
      'template' : '{update-modal} {update-modal} {delete}',
      'controller' : 'promo/personal-profits'
    },
```

В контроллере:

```php

public function actionUpdateModal($id)
  {
    $model = $this->findModel($id);

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        $model->save();
        return ['data' => TRUE];
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form_modal.twig', [
      'model' => $model
    ]);

  }
```

#### Модальные окна
Создаем модальное окно
```php
<?php Modal::begin([
  'id' => 'modal',
  'options' => ['class' => 'clean-with-header']
]) ?>
<div class="well">
<?= Spinner::widget(['preset' => 'large']) ?>
</div>
<?php Modal::end() ?>
```
class=clean //весь контент для модалки загрузить по ajax
class=clean-with-header //контент (body, footer), кроме заголовка, будет прогружаться с ajax
```

```
Ссылка для запуска окна
```
class=showModalButton //основной класс
href //адрес для запроса
title //заголовок окна
data-modal-content //контент отобразится из modal-body, ajax не используется
```

# Ajax кнопки для GridView
Если подключен класс `submodules/mcms/common/grid/ActionColumn.php`, то кнопки `{enable} {disable} {delete}` уже работают в режиме ajax.
Если необходимо свою кнопку сделать, то надо прописать ссылке аттрибут `ActionColumnAsset::AJAX_ATTRIBUTE => 1` и опционально `data-confirm-text`

В результате операции будет js-уведомление об успехе или ошибке операции.

Если ответ успешный, то будет попытка обновить pjax контейнер, который по DOM дереву расположен выше кнопки.
 
Если такой контейнер не найден, будет обновлена страница. 

Ответ на запрос должен быть в формате как описано ниже.

# Ответы на Ajax запросы
Используем `submodules/mcms/common/web/AjaxResponse.php`.
Например:

```php

public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->setDisabled();
    return AjaxResponse::set($model->save());
  }
```

более детальный вид:

```php
public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->setDisabled();

    if (!$model->save()) {
        return AjaxResponse::error('Кастомный текст об ошибке');
    }

    return AjaxResponse::success();
  }
```

# Проверка прав
Мы используем перегруженный класс View, который находится в `mcms\common\web\View`.

##### Вывод блоков html с проверкой прав доступа
Для этого нужно использовать специальняй блок, который называется `mcms\common\widget\BlockAccessVerifier`

Во вьюхах он используюется следующим образом:
    
```php

    $this->beginBlockAccessVerifier(...)
    
    $this->endBlockAccessVerifier()
    
```

`beginBlockAccessVerifier` принимает следующие параметры:

* название блока
* массив с разрешениями
* renderInPlace, по дефолту true, подробнее [тут](http://www.yiiframework.com/doc-2.0/yii-base-view.html#beginBlock()-detail)

Пример массива с разрешениями:

```php

    ['SupportTicketOpen' => $supportModel]
    //для проверки на разрешения к определенной сущности
    
    или 
    
    ['SupportTicketList']
    //для обычной проверки
    
```

Можно указывать сразу несколько разрешений.

Полный пример:

```php

    $this->beginBlockAccessVerifier('test', ['ShowTestData' => $model, 'TestCase']);
      echo 'test';
    $this->endBlockAccessVerifier();

```

##### Вывод ссылок с учетом проверки на разрешение
Для этого надо использовать перегруженный хелпер Html, который находится в `mcms\common\helpers\Html`

Последним параметром надо передать массив с разрешениями. Определени массива с разрешениями такие же, как в выводе блоков html с проверкой(смотри выше)


##### Инициализация разрешений
Инициализация разрешений пишется в миграции. В файле мигарций надо использовать trait `mcms\common\traits\PermissionMigration`

в функции `init` в миграции надо определить authManager, moduleName и permission. Пример:
```php
    
    public function init()
      {
        parent::init();
    
        $this->authManager = Yii::$app->authManager;
        $this->moduleName = 'Notifications';
        $this->permissions = [
          'Settings' => [
            ['list', 'Can view modules', ['admin', 'root']],
            ['view', 'Can view modules event catchers', ['admin', 'root']],
            ['add', 'Can add event catcher', ['admin', 'root']],
            ['edit', 'Can edit event catcher', ['admin', 'root']],
            ['delete', 'Can delete event catcher', ['admin', 'root']],
            ['template', 'Can edit event template', ['admin', 'root']],
            ['disable', 'Can disable event catching', ['admin', 'root']],
            ['enable', 'Can enable event catching', ['admin', 'root']],
          ],
          'Default' => [
            ['index', 'Can view index page', ['admin', 'root']]
          ]
        ];
      }
      
    ... 
    //другой пример
    public function init()
      {
        parent::init();
        $this->moduleName = 'Support';
        $this->authManager = Yii::$app->getAuthManager();
        $this->rules = [];
        $this->roles = [];
    
        $this->permissions = [
          'Categories' => [
            ['list', 'Can view categories List', ['admin', 'root']],
            ['update', 'Can update category', ['admin', 'root']],
            ['create', 'Can create category', ['admin', 'root']],
            ['enable', 'Can enable category', ['admin', 'root']],
            ['disable', 'Can disabled category', ['admin', 'root']],
          ],
          'Tickets' => [
            ['findUser', 'Can find user in ticket', ['admin', 'root']],
            ['list', 'Can list tickets', ['admin', 'root']],
            ['edit', 'Can edit ticket', ['admin', 'root']],
            ['create', 'Can create ticket', ['admin', 'root', 'partner']],
            ['view', 'Can view ticket', ['admin', 'root', 'partner']],
            ['close', 'Can close ticket', ['admin', 'root'],
              [
                'SupportOwnTicketRule' => \mcms\support\components\rbac\OwnTicketRule::class,
                'SupportDelegatedTicketRule' => \mcms\support\components\rbac\DelegatedTicketRule::class
              ]
            ],
            ['open', 'Can open ticket', ['admin', 'root'], [
              'SupportDelegatedTicketRule' => \mcms\support\components\rbac\DelegatedTicketRule::class
            ]],
            ['delegate', 'Can delegate ticket', ['admin', 'root']],
          ]
        ];
      }

```

Правило оформления массива 
Ключ это название контроллера, значение - массив с экшенами

Экшены определяются следующим образом:

1 эллемент - название экшене

2 - Описание разрешения

3 - массив ролей

4 - массив, в котором ключ - название рули, значение, полный путь до рули

# Join Api
Первое что необходимо сделать, это определить метод в апи, который будет соединять модель текушего модуля с другими моделями.
 
```php
    
    public function join(ActiveQuery $activeQuery, JoinCondition $joinCondition, array $fields = [])
      {
        $joinCondition
          ->setRightTable('users')
          ->setRightTableColumn('id')
        ;
        return JoinApi::join($activeQuery, $joinCondition, $fields);
      }
    
```

Параметры функции:
* ActiveQuery это то, к чему надо приджоинить 

```php
    
    $query = Support::find()
          ->select('count(users.id) as cnt')
          ->where(['id' => 1])
          ->having(['=', 'cnt', 4])
          ->groupBy(['id'])
        ;
```

* JoinCondition класс, в котором опреляется как связать сущности

```php
    
    // 1 параметр - название нашей таблицы
    // 2 - как свяываем данные и на какую поле в нашей таблцы будет происходить связываение
    // 3 - алиас
    $join = new JoinCondition(Support::tableName(), ['=', 'created_by'], 'users');
    
```

* массив полей из связываемой таблцы для вывода ее в результат

Далее надо надо выхвать наш метод апи `join`

```php 
    
    $userApi->join($query, $join, ['id', 'email'])

```

`join` вернет ActiveQuery

# Дополнительные валидаторы

Хранятся в submodules/mcms/common/validators

##### AlphanumericalValidator

Используется для проверки, состоит ли строка только из латинских символов и цифр
В качестве параметра `pattern` можно использовать встроенные константы:

* BOTH_REGISTERS - значение по умолчанию, цифры и символы обоих регистров
* ONLY_LOWER - только цифры и символы нижнего регистра
* ONLY_UPPER - только цифры и симыолы верхнего регистра

Примеры:

```php

  public function rules()
  {
    return [
      [['code'], AlphanumericalValidator::class],
    ];
  }

```

```php

  public function rules()
  {
    return [
      [['code'], AlphanumericalValidator::class, 'pattern' => AlphanumericalValidator::ONLY_LOWER],
    ];
  }

```

# Команды

##### Тестирование прокси

По-умолчанию берет прокси из конфигов
```bash
php yii curl/test ya.ru [proxy:port]
```

##### Инициализация лендинга

```bash
php yii pages/init-lp/index wapcash
```
В common/config/params-local.php добавить параметр `'landing' => 'wapcash'` 