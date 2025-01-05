# mcms-pages
Модуль Pages для ModuleCMS


# Установка

Добавить сабмодуль через гит
`git submodule add https://github.com/RGKgroup/mcms-pages submodules/mcms/pages`

и затем `git submodule update`


Выполнить миграции

`php yii migrate/up --migrationPath="@mcms/pages/migrations"`
