<?php

namespace mcms\user\commands;

use Exception;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserBalancesGroupedByDay;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\promo\models\Domain;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\PersonalProfit;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\promo\models\VisibleLandingPartner;
use mcms\user\models\User;
use mcms\user\Module;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\console\ExitCode;

/**
 * Delete user by user id.
 * @package mcms\user\commands
 */
class DeleteUserController extends Controller
{
    /**
     * Delete user by user id.
     * @param $userId
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionIndex($userId)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** @var User $user */
            $user = User::findOne(['id' => $userId]);
            if (!$user) {
                $this->stdout("User not founded, wrong user id?" . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $userRole = ArrayHelper::getColumn($user->getRoles()->all(), 'name');
            if (in_array(Module::ADMIN_ROLE, $userRole) || in_array(Module::ROOT_ROLE, $userRole)) {
                $this->stdout("This user cannot be deleted." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (Stream::find()->where(['user_id' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in streams table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if ((new Query())->select(['id'])->from('hits_day_group')->where(['user_id' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in hits_day_group table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if ((new Query())->select(['id'])->from('hits_day_hour_group')->where(['user_id' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in hits_day_hour_group table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (Domain::find()->where(['created_by' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in " . Domain::tableName() . " table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (Source::find()->where(['user_id' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in " . Source::tableName() . " table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (UserBalanceInvoice::find()->where(['user_id' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in " . UserBalanceInvoice::tableName() . " table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (UserBalancesGroupedByDay::find()->where(['user_id' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in " . UserBalancesGroupedByDay::tableName() . " table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (UserPayment::find()->where(['user_id' => $userId])->exists()) {
                $this->stdout("This user cannot be deleted because of current user has records in " . UserPayment::tableName() . " table." . PHP_EOL, Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->stdout("User name: {$user->username}\t\tUser email: {$user->email}\t\tUser status: {$user->getNamedStatus()}" . PHP_EOL);
            if (!$this->confirm("Are you sure of delete current user?")) {
                $this->stdout("User is not deleted." . PHP_EOL, Console::FG_GREEN);
                return ExitCode::OK;
            }

            LandingUnblockRequest::deleteAll(['user_id' => $userId]);
            $this->stdout("Delete from table " . LandingUnblockRequest::tableName() . "." . PHP_EOL);

            PersonalProfit::deleteAll(['or', ['user_id' => $userId], ['created_by' => $userId]]);
            $this->stdout("Delete from table " . PersonalProfit::tableName() . "." . PHP_EOL);

            VisibleLandingPartner::deleteAll(['or', ['created_by' => $userId], ['user_id' => $userId]]);
            $this->stdout("Delete from table " . VisibleLandingPartner::tableName() . "." . PHP_EOL);

            UserPaymentSetting::deleteAll(['user_id' => $userId]);
            $this->stdout("Delete from table " . UserPaymentSetting::tableName() . "." . PHP_EOL);

            if (!$user->delete()) {
                throw new Exception("Can't delete user.");
            }
            $this->stdout("Delete user from table " . User::tableName() . "." . PHP_EOL);

            Yii::$app->db->createCommand()->delete('auth_assignment', ['user_id' => $userId])->execute();
            $this->stdout("Delete user from table auth_assignment." . PHP_EOL);

            $transaction->commit();
            $this->stdout("User successfully deleted." . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->stdout("Delete user failed. All deleted relations if rolled back." . PHP_EOL . Console::FG_RED);
            $this->stdout("Exception:" . PHP_EOL . $e);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}