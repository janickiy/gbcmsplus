<?php

namespace admin\dashboard\gadgets\active_partners;

use admin\dashboard\gadgets\base\BaseGadget;
use mcms\common\helpers\Html;
use mcms\common\traits\Translate;
use yii\helpers\Url;

/**
 * Гаджет активных партнеров
 */
class GadgetActivePartners extends BaseGadget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.gadget_active_partners-';
    const CACHE_KEY = 'DashboardGadgetActivePartners';

    /**
     * @return string
     */
    protected function getValue(): string
    {
        $values = $this->getValues();
        $currentValue = end($values);

        return Html::tag('span', $currentValue, ['class' => 'gadget-value']);
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        return $this->prepareTimeLine($this->getApi($this->useFilters)->getActivePartners());
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return static::translate('title');
    }

    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return 'AppBackendGadgetActivePartners';
    }

    /**
     * @return false|string
     */
    public function getUrl(): string
    {
        return Url::to(['/gadget/active-partners/']);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'activePartners';
    }

    public function getCacheKey()
    {
        return self::CACHE_KEY . ':' . $this->getHashKey();
    }
}