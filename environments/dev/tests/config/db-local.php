<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=db;dbname=modulecms_test',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'attributes' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET time_zone = \'Europe/Moscow\''
    ],
    'enableSchemaCache' => false
];
