<?php

namespace admin\modules\credits\controllers;

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\credit\CreditApprove;
use admin\modules\credits\models\credit\CreditDecline;
use admin\modules\credits\models\form\CreditPaymentForm;
use admin\modules\credits\models\form\SettingsForm;
use rgk\utils\actions\CreateModalAction;
use rgk\utils\actions\UpdateModalAction;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CreditPaymentsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        $successMessage = function (CreditPaymentForm $payment) {
            /** @var Credit $credit Кредит намеренно полуается методом, а не свойством, что бы получить актуальные данные */
            $credit = $payment->getCredit()->one();
            return $credit->status == Credit::STATUS_DONE ? 'Loan debt is closed' : null;
        };

        return [
            'create-modal' => [
                'class' => CreateModalAction::class,
                'modelClass' => CreditPaymentForm::class,
                'beforeRender' => function (CreditPaymentForm $transaction, $params) {
                    // Определение кредита
                    $credit = Credit::findOne((int)Yii::$app->request->get('creditId'));
                    if (!$credit) {
                        throw new NotFoundHttpException('Кредит не найден');
                    }

                    // Сумма по умолчанию
                    $resellerBalance = $credit->getBalance();
                    $transaction->amount = $credit->getDebt();
                    if ($transaction->amount > $resellerBalance) {
                        $transaction->amount = $resellerBalance;
                    }

                    // Дополнение параметров
                    $params['credit'] = $credit;
                    $params['balance'] = $resellerBalance;

                    return $params;
                },
                'successMessage' => $successMessage,
            ],
            'update-modal' => [
                'class' => UpdateModalAction::class,
                'modelClass' => CreditPaymentForm::class,
                'beforeRender' => function (CreditPaymentForm $transaction, $params) {
                    /** @var Credit $credit */
                    $credit = $transaction->credit;
                    // Дополнение параметров
                    $params['credit'] = $credit;
                    $params['balance'] = $credit->getBalance();

                    return $params;
                },
                'successMessage' => $successMessage,
            ],
        ];
    }
}
