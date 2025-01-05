<?php

namespace admin\modules\credits\models\transaction;

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\CreditTransaction;
use admin\modules\payments\models\Invoice;
use rgk\utils\exceptions\ModelNotSavedException;
use rgk\utils\traits\ModelPerActionTrait;
use yii\base\Model;

/**
 * @property Credit $model
 */
abstract class AbstractCreditTransactionInvoice extends Model
{
    use ModelPerActionTrait;

    /**
     * Создать транзакцию
     * @param $transactionAmount
     * @param $transactionType
     * @param null $feeDate
     * @return bool
     * @throws ModelNotSavedException
     */
    public function createTransaction($transactionAmount, $transactionType, $feeDate = null)
    {
        $creditTransaction = new CreditTransaction;
        $creditTransaction->credit_id = $this->model->id;
        $creditTransaction->amount = $transactionAmount;
        $creditTransaction->type = $transactionType;
        if ($feeDate) {
            $creditTransaction->fee_date = $feeDate;
        }
        if (!$creditTransaction->save()) {
            throw new ModelNotSavedException('Не удалось создать транзакцию');
        }

        return true;
    }
}
