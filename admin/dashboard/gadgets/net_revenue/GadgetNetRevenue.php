<?php

namespace admin\dashboard\gadgets\net_revenue;

use admin\dashboard\gadgets\base\BaseGadget;
use mcms\common\helpers\Html;
use mcms\common\traits\Translate;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class GadgetNetRevenue extends BaseGadget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.gadget_net_revenue-';
    const CACHE_KEY = 'DashboardGadgetNetRevenue';

    protected $useCurrencySymbol = true;

    /**
     * @inheritdoc
     */
    protected function getValue()
    {
        $data = $this->getValues();

        $currencySymbol = $this->getCurrencySymbol($this->getUserCurrency());

        $currentValue = $this->useFilters ? array_sum($data) : end($data);

        return Html::tag('span', $this->formatter->asDecimal($currentValue, 2), ['class' => 'gadget-value']) . ' ' . $currencySymbol;
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        $profitParam = null;
        // TRICKY Подменяем данные последнего дня для того, чтобы данные нижнего гаджета совпадали с верхними (на один фильтры накладываются, а на другой - нет)
        // Если включен фильтр по странам (может быть только у нижнего гаджета), подменять нет смысла
        // @see https://rgkdev.atlassian.net/wiki/spaces/MD/pages/25493508/Dashboard
        $changeTodaysData = !$this->countries;
        // tricky: Если видит профит (админский или ресовский), заполняем сумму
        if ($this->getPermissionChecker()->canViewAdminProfit() || $this->getPermissionChecker()->canViewResellerProfit()) {
            $profitParam = 'res_net_revenue_' . $this->getUserCurrency();
        }
        $statistic = $this->getApi($this->useFilters)->getStatByDates();

        $result = $this->prepareTimeLine(ArrayHelper::getColumn($statistic, $profitParam));
        if ($changeTodaysData && $this->getPermissionChecker()->canFilterByCurrency() && ($valuesByCurrency = $this->prepareValuesByCurrency(self::TYPE_NET, false))) {
            $result[date('Y-m-d')] = round(ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'rub'), 'converted') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'usd'), 'converted') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'eur'), 'converted'), 2);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getValuesByCurrency($formatting = true)
    {
        return $this->useFilters ? [] : $this->prepareValuesByCurrency(self::TYPE_NET, $formatting);
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
        return 'AppBackendGadgetNetRevenue';
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return Url::to(['/gadget/net-revenue/']);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'netRevenue';
    }

    public function getCacheKey()
    {
        return self::CACHE_KEY . ':' . $this->getHashKey('date', $this->getUserCurrency());
    }
}