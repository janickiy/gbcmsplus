<?php

namespace admin\dashboard\gadgets\cpa_subs;

use admin\dashboard\gadgets\base\BaseGadget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class GadgetCpaSubs extends BaseGadget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.gadget_cpa_subs-';
    const CACHE_KEY = 'DashboardGadgetCpaSubs';

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
            ArrayHelper::getColumn($this->getApi($this->useFilters)->getStatByDates(), 'cpa_ons')
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
        return 'AppBackendGadgetCpaSubs';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/gadget/cpa-subs/']);
    }

    public function getName()
    {
        return 'cpaSubs';
    }

    /**
     * @inheritdoc
     */
    public function getCacheKey()
    {
        return self::CACHE_KEY . ':' . $this->getHashKey();
    }
}