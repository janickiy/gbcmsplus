# mcms-users
Модуль Users для ModuleCMS


# Установка

Добавить сабмодуль через гит
`git submodule add https://github.com/RGKgroup/mcms-users submodules/mcms/user`

и затем `git submodule update`

Включить модуль в admin и console

```
    'users' => [
      'class' => 'mcms\user\Module',
    ]
```

Выолнить миграции

`php yii migrate/up --migrationPath="@mcms/user/migrations"`

Создать пользователя 

`php yii users/manage/create email username password`


Активировать пользователя

`php yii users/manage/activate username`

Удалить пользователя

`php yii users/delete-user userId`

Создать роли

`php yii users/rbac/init`

Обновить разрешения

`php yii users/rbac/update-permission`

Разрешения указываются в аннотациях в формате:

`use mcms\user\components\annotations\Role;`


`@Roles({"role1", "role2"})`


Админка yii-admin доступна по адресу users/admin

# Генератор демо данных, cli скрипт:

```
php yii users/demo-generator
```