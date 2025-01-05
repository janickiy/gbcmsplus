<?php

namespace admin\dashboard\gadgets\rs_subs;

use admin\dashboard\gadgets\base\BaseGadget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class GadgetRsSubs extends BaseGadget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.gadget_rs_subs-';
    const CACHE_KEY = 'DashboardGadgetRsSubs';

    /**
     * @inheritdoc
     */
    protected function getValue()
    {
        $values = $this->getValues();
        $currentValue = $this->useFilters ? array_sum($values) : end($values);
        return Html::tag('span', Yii::$app->formatter->asInteger($currentValue), ['class' => 'gadget-value']);
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        return $this->prepareTimeLine(
            ArrayHelper::getColumn($this->getApi($this->useFilters)->getStatByDates(), 'revshare_ons')
        );
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return static::translate('title');
    }

    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return 'AppBackendGadgetRsSubs';
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return Url::to(['/gadget/rs-subs/']);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'rsSubs';
    }

    public function getCacheKey()
    {
        return self::CACHE_KEY . ':' . $this->getHashKey();
    }
}