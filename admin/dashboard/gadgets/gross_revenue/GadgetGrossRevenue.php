<?php

namespace admin\dashboard\gadgets\gross_revenue;

use admin\dashboard\gadgets\base\BaseGadget;
use mcms\common\helpers\Html;
use mcms\common\traits\Translate;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class GadgetGrossRevenue extends BaseGadget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.gadget_gross_revenue-';
    const CACHE_KEY = 'DashboardGadgetGrossRevenue';

    protected $_profitParams;
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

    protected function getData()
    {
        $statistic = $this->getApi($this->useFilters)->getStatByDates();

        $profitParam = null;
        // TRICKY Подменяем данные последнего дня для того, чтобы данные нижнего гаджета совпадали с верхними (на один фильтры накладываются, а на другой - нет)
        // Если включен фильтр по странам (может быть только у нижнего гаджета), подменять нет смысла
        // @see https://rgkdev.atlassian.net/wiki/spaces/MD/pages/25493508/Dashboard
        $changeTodaysData = !$this->countries;
        // tricky: Если видит профит (админский или ресовский), заполняем сумму
        if ($this->getPermissionChecker()->canViewAdminProfit() || $this->getPermissionChecker()->canViewResellerProfit()) {
            $profitParam = 'res_gross_revenue_' . $this->getUserCurrency();
        }
        $result = $this->prepareTimeLine(ArrayHelper::getColumn($statistic, $profitParam));
        if ($changeTodaysData && $this->getPermissionChecker()->canFilterByCurrency() && ($valuesByCurrency = $this->prepareValuesByCurrency(self::TYPE_GROSS, false))) {
            $result[date('Y-m-d')] = round(ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'rub'), 'converted') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'usd'), 'converted') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'eur'), 'converted'), 2);
        }
        return $result;
    }

    /**
     * @param bool $formatting
     * @return array
     */
    protected function getValuesByCurrency(bool $formatting = true): array
    {
        return $this->useFilters ? [] : $this->prepareValuesByCurrency(self::TYPE_GROSS, $formatting);
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
        return 'AppBackendGadgetGrossRevenue';
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return Url::to(['/gadget/gross-revenue/']);
    }

    public function getName()
    {
        return 'grossRevenue';
    }

    /**
     * @inheritdoc
     */
    public function getCacheKey()
    {
        return self::CACHE_KEY . ':' . $this->getHashKey('date', $this->getUserCurrency());
    }
}