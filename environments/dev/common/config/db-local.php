<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=db;dbname=modulecms',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'attributes' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET time_zone = \'Europe/Moscow\'',
        PDO::MYSQL_ATTR_LOCAL_INFILE => true
    ],
    'enableSchemaCache' => false
];
