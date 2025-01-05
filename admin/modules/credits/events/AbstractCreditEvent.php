<?php

namespace admin\modules\credits\events;

use admin\modules\credits\models\Credit;
use mcms\common\event\Event;
use Yii;

abstract class AbstractCreditEvent extends Event
{
    /** @var Credit|null */
    public $credit;

    /**
     * @param Credit|null $credit
     */
    public function __construct(Credit $credit = null)
    {
        $this->credit = $credit;
    }

    /**
     * @inheritdoc
     */
    public function getModelId()
    {
        return $this->credit->id;
    }

    /**
     * @inheritdoc
     */
    public function getOwner()
    {
        return $this->credit->user;
    }

    /**
     * @inheritdoc
     */
    public static function getUrl($id = null)
    {
        return $id ? ['/credits/credits/view/', 'id' => $id] : ['/credits/credits/index/'];
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalReplacements()
    {
        return $this->credit ? [
            'credit.id' => $this->credit->id,
            'credit.amount' => Yii::$app->formatter->asPrice($this->credit->amount, $this->credit->currency),
        ] : [];
    }
}
