<?php

namespace console\components;

use mcms\common\RunnableInterface;
use Yii;
use yii\db\Query;

/**
 * Импортируем инициализирующий дамп
 */
class RestoreInitDump implements RunnableInterface
{

    const DEFAULT_USER_PASSWORD = '1qazxsw2';

    public function run()
    {
        Yii::$app->db->createCommand(file_get_contents(__DIR__ . '/sql/Dump20181205.sql'))->execute();

        $users = (new Query())->from('users')->all();


        foreach ($users as $user) {
            $password = self::DEFAULT_USER_PASSWORD;
            if (!YII_ENV_DEV) {
                $password = Yii::$app->security->generateRandomString(10);
            }
            Yii::$app->db->createCommand()->update(
                'users',
                ['password_hash' => Yii::$app->security->generatePasswordHash($password)],
                ['id' => $user['id']]
            )->execute();

            echo "Create user. Username: {$user['username']} Password: $password" . PHP_EOL;
        }

    }
}
