<?php

namespace admin\modules\credits\models\form;

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\credit\CreditApprove;
use rgk\utils\exceptions\ModelNotSavedException;
use rgk\utils\traits\ModelPerFormTrait;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Форма создания/изменения кредита
 */
class CreditForm extends Credit
{
    use ModelPerFormTrait;

    /**
     * @inheritdoc
     */
    public function scenarioAttributes()
    {
        return ['amount', 'currency', 'status'];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            ['amount', 'checkAmount']
        ]);
    }

    /**
     * @return bool
     * @throws ModelNotSavedException
     */
    public function checkAmount()
    {
        if (!$this->currency) {
            return true;
        }

        $settings = self::getSettings();

        if (!$settings) {
            throw new ModelNotSavedException('Настройки процентов не получены');
        }

        $this->percent = $settings['percent' . ucfirst($this->currency)];

        $limit = $settings['limit' . ucfirst($this->currency)];

        if ($this->amount > $limit) {
            $this->addError('amount', Yii::_t('credits.credit.limit-error'));
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $this->status = self::STATUS_REQUESTED;
        return parent::beforeValidate();
    }
}
