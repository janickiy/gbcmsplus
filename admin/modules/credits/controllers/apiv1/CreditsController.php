<?php

namespace admin\modules\credits\controllers\apiv1;

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\CreditTransaction;
use mcms\common\mgmp\ApiController;
use yii\web\Response;
use Yii;

class CreditsController extends ApiController
{
    /**
     * @param int|null $creditsDateFrom
     * @param int|null $transactionsDateFrom
     * @return array
     */
    public function actionIndex($creditsDateFrom, $transactionsDateFrom)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'credits' => $this->getCredits($creditsDateFrom),
            'transactions' => $this->getTransactions($transactionsDateFrom),
        ];
    }

    private function getCredits($dateFrom)
    {
        $credits = Credit::find()
            // TRICKY Должно быть именно >=, иначе не будет синхронизироваться external_id
            // Например создали кредит на мп, импортнулся на мц, теперь этот же кредит должен импортнуться обратно на мп, что бы установить external_id
            ->andFilterWhere(['>=', 'updated_at', $dateFrom])
            ->asArray()
            ->all();

        foreach ($credits as &$credit) {
            $credit['mcms_id'] = $credit['id'];
            $credit['mgmp_id'] = $credit['external_id'];
            unset($credit['id']);
            unset($credit['external_id']);
        }

        return $credits;
    }

    private function getTransactions($dateFrom)
    {
        $transactions = CreditTransaction::find()
            // TRICKY Должно быть именно >=, иначе не будет синхронизироваться external_id
            // Например создали кредит на мп, импортнулся на мц, теперь этот же кредит должен импортнуться обратно на мп, что бы установить external_id
            ->andFilterWhere(['>=', 'updated_at', $dateFrom])
            ->each();

        $transactionsData = [];
        /** @var CreditTransaction $transaction */
        foreach ($transactions as $transaction) {
            $transactionData = $transaction->toArray();
            $transactionData['mcms_id'] = $transaction->id;
            $transactionData['mgmp_id'] = $transaction->external_id;
            $transactionData['mcms_credit_id'] = $transaction->credit_id;
            $transactionData['mgmp_credit_id'] = $transaction->credit->external_id;
            unset($transactionData['id']);
            unset($transactionData['external_id']);
            unset($transactionData['credit_id']);
            $transactionsData[] = $transactionData;
        }

        return $transactionsData;
    }
}
