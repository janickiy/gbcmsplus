<?php

namespace mcms\user\commands;

use mcms\user\models\User;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class DemoGeneratorController extends Controller
{

    protected $counters = [
        'partner' => 300,
        'investor' => 3,
        'reseller' => 0,
    ];
    protected $defaultPassword = '111111';


    public function actionIndex()
    {
        if (defined('YII_ENV') && YII_ENV === 'prod') {
            $this->stdout('Запрещен запуск на продакшене' . "!\n");
            return;
        }

        $this->stdout("User generator launched!\n");
        $this->stdout('Counters: ' . print_r($this->counters, true) . "'\n\n");

        $authManager = Yii::$app->authManager;

        $addedToRole = 0;

        foreach ($this->counters as $role => $roleCount) {

            $authRole = $authManager->getRole($role);

            for ($i = 1; $i <= $roleCount; $i++) {
                $user = Yii::createObject([
                    'class' => User::class,
                    'scenario' => 'create',
                    'email' => $role . $i . '@test.ru',
                    'status' => User::STATUS_ACTIVE,
                    'username' => $role . $i,
                    'password' => $this->defaultPassword,
                ]);
                if (!$user->save()) continue;
                if ($authManager->assign($authRole, $user->id)) $addedToRole++;

                $this->stdout("$i | $user->email - saved!\n");
            }

            $this->stdout($addedToRole . ' users with role=' . $role . ' has been created' . "!\n", Console::FG_GREEN);
            $addedToRole = 0;
        }

        $this->stdout('User generator finish' . "\n");
    }
}