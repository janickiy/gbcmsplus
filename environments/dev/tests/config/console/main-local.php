<?php
return [
    'components' => [
        'db' => require(__DIR__ . '/../db-local.php')
    ],
    'controllerMap' => [
        'statistic-tests' => [
            'class' => \mcms\statistic\tests\commands\GenerateCsvController::class
        ]
    ],
];
