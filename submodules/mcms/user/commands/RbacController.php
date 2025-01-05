<?php

namespace mcms\user\commands;

use mcms\user\models\User;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;


class RbacController extends Controller
{
    const ROLE_ROOT = 'root';

    /**
     * Create roles
     */
    public function actionInit()
    {

        $authManager = \Yii::$app->authManager;

        $this->stdout('Creating roles' . PHP_EOL);

        $guest = $authManager->createRole('guest');
        $this->stdout(sprintf("    %s role created \n", "quest"), Console::FG_GREEN);
        $root = $authManager->createRole("root");
        $this->stdout(sprintf("    %s role created \n", "root"), Console::FG_GREEN);
        $admin = $authManager->createRole("admin");
        $this->stdout(sprintf("    %s role created \n", "admin"), Console::FG_GREEN);
        $manager = $authManager->createRole("manager");
        $this->stdout(sprintf("    %s role created \n", "manager"), Console::FG_GREEN);
        $reseller = $authManager->createRole("reseller");
        $this->stdout(sprintf("    %s role created \n", "reseller"), Console::FG_GREEN);
        $investor = $authManager->createRole("investor");
        $this->stdout(sprintf("    %s role created \n", "investor"), Console::FG_GREEN);
        $partner = $authManager->createRole("partner");
        $this->stdout(sprintf("    %s role created \n", "partner"), Console::FG_GREEN);
        $this->stdout('Creating roles done' . PHP_EOL, Console::FG_GREEN);
        try {
            $this->stdout('Adding roles' . PHP_EOL);
            $authManager->add($guest);
            $this->stdout(sprintf("   %s role added \n", "quest"), Console::FG_GREEN);
            $authManager->add($root);
            $this->stdout(sprintf("   %s role added \n", "root"), Console::FG_GREEN);
            $authManager->add($admin);
            $this->stdout(sprintf("   %s role added \n", "admin"), Console::FG_GREEN);
            $authManager->add($manager);
            $this->stdout(sprintf("   %s role added \n", "manager"), Console::FG_GREEN);
            $authManager->add($reseller);
            $this->stdout(sprintf("   %s role added \n", "reseller"), Console::FG_GREEN);
            $authManager->add($investor);
            $this->stdout(sprintf("   %s role added \n", "investor"), Console::FG_GREEN);
            $authManager->add($partner);
            $this->stdout(sprintf("   %s role added \n", "partner"), Console::FG_GREEN);
            $this->stdout('Adding roles done' . PHP_EOL, Console::FG_GREEN);
        } catch (\yii\db\IntegrityException $e) {
            $this->stdout('Roles already exists' . PHP_EOL, Console::FG_RED);
        }
    }

    /**
     * Setting root role for given username
     * @param string $username
     * @return void
     */
    public function actionSetRootRole(string $username)
    {
        $this->stdout(sprintf("Setting root role to %s \n", $username));
        $user = User::findByUsername($username);
        if ($user === null) {
            $this->stdout('User not found', Console::FG_RED);
            exit();
        }

        $authManager = Yii::$app->authManager;
        $rootRole = $authManager->getRole(self::ROLE_ROOT);
        if ($rootRole === null) {
            $this->stdout(sprintf('Role %s not found', self::ROLE_ROOT), Console::FG_RED);
            exit();
        }

        try {
            $authManager->assign($rootRole, $user->id);
        } catch (\Exception $e) {
            $this->stdout(
                sprintf(
                    "Role %s has already been assigned to the user \n", self::ROLE_ROOT),
                Console::FG_RED
            );
            exit();
        }

        $this->stdout(sprintf("Role %s has been assigned\n", self::ROLE_ROOT), Console::FG_GREEN);
    }
}