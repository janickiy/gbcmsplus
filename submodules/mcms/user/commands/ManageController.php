<?php

namespace mcms\user\commands;

use mcms\user\models\User;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Manages users
 * @package mcms\user\commands
 */
class ManageController extends Controller
{
    const USERS_IDS_DELIMITER = ',';

    /**
     * Creates a new user
     * @param string $email
     * @param string $username
     * @param string $password
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate(string $email, string $username, string $password)
    {
        if (User::find()->where(['email' => $email])->orWhere(['username' => $username])->one() !== null) {
            $this->stdout('Email or username already registered!' . "\n", Console::FG_RED);
            exit();
        }
        /** @var User $user */
        $user = Yii::createObject([
            'class' => User::class,
            'scenario' => 'create',
            'email' => $email,
            'status' => User::STATUS_ACTIVE,
            'username' => $username,
            'password' => $password,
        ]);

        if ($user->save()) {
            $this->stdout('User has been created' . "!\n", Console::FG_GREEN);
        } else {
            $this->stdout('Please fix following errors:' . "\n", Console::FG_RED);
            foreach ($user->errors as $errors) {
                foreach ($errors as $error) {
                    $this->stdout(' - ' . $error . "\n", Console::FG_RED);
                }
            }
        }
    }

    /**
     * Activate user by username
     * @param $username
     */
    public function actionActivate(string $username)
    {
        /** @var User $user */
        $user = User::findByUsername($username);

        if (!$user) {
            $this->stdout('User not found' . "\n", Console::FG_RED);
            exit();
        }
        $user->scenario = 'activate';

        $user->status = User::STATUS_ACTIVE;
        if ($user->save()) {
            $this->stdout('User has been activated' . "!\n", Console::FG_GREEN);
        } else {
            $this->stdout('User activation failed' . "!\n", Console::FG_RED);
        }
    }

    /**
     * Сгенерировать пароли
     * @param int|string $userIds Идентификаторы пользователей через запятую без пробелов
     */
    public function actionResetPasswords(int $userIds)
    {
        $userIds = explode(static::USERS_IDS_DELIMITER, $userIds);

        $userModule = Yii::$app->getModule('users');
        foreach ($userIds as $userId) {
            $user = User::findOne(['id' => $userId]);
            if (!$user) {
                $this->stdout('User #' . $userId . ' not found' . "!\n", Console::FG_RED);
                continue;
            }

            $password = User::generateNewPassword();
            if ($userModule->api('changeUserPassword', [
                'user_id' => $userId,
                'password' => $password,
            ])->getResult()
            ) {
                $this->stdout('#' . $user->id . ' ' . $user->username . ' - ' . $password . "\n", Console::FG_GREEN);
            } else {
                $this->stdout('User "#' . $user->id . ' ' . $user->username . '" not updated' . "!\n", Console::FG_RED);
            }
        }
    }

    /**
     * Разлогинивание пользовател
     * @param int $id id пользователя, которого нужно разлогинить
     * @return bool
     */
    public function actionLogout($id)
    {
        $this->logout($id);
    }

    /**
     * Разлогинить всех пользователей
     * @return bool
     */
    public function actionLogoutAll()
    {
        $this->logout();
    }

    /**
     * @param int|null $id пользователя, которого нужно разлогинить
     * @return bool
     */
    private function logout(?int $id = null)
    {
        if ($id == null && !$this->confirm("Are you sure want to unlogin all users?")) {
            return false;
        }

        $users = $id ? User::find()->where(['id' => $id]) : User::find();
        if ($users->count() == 0) {
            $this->stdout("The user does not exist\n");
            return false;
        }

        foreach ($users->each() as $user) {
            $this->stdout("The user {$user->username} was unlogined\n");
            $user->generateAuthKey();
            $user->save();
        }
        return true;
    }
}