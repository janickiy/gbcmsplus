<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=mariadb;dbname=modulecms',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'attributes' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET time_zone = \'+03:00\'',
        PDO::MYSQL_ATTR_LOCAL_INFILE => true
    ],
    'enableSchemaCache' => false
];
