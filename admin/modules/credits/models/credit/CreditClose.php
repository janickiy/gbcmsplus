<?php

namespace admin\modules\credits\models\credit;

use admin\modules\credits\models\Credit;
use rgk\utils\traits\ModelPerActionTrait;
use yii\base\Model;

/**
 * Закрыть кредит
 * @property Credit $model
 */
class CreditClose extends Model
{
    use ModelPerActionTrait;

    /**
     * @inheritdoc
     */
    protected function isAvailableInternal()
    {
        return $this->model->status === Credit::STATUS_ACTIVE && $this->model->getDebt() == 0;
    }

    /**
     * @inheritdoc
     */
    protected function executeInternal()
    {
        $this->model->status = Credit::STATUS_DONE;
        $this->model->closed_at = time();
        return $this->model->save();
    }

    /**
     * @inheritdoc
     */
    protected function getModelClass()
    {
        return Credit::class;
    }
}
