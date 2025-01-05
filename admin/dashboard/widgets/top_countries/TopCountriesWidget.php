<?php

namespace admin\dashboard\widgets\top_countries;

use admin\dashboard\models\AbstractFiltersHandler;
use admin\dashboard\widgets\base\BaseWidget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\payments\components\api\ExchangerPartnerCourses;
use Yii;
use yii\helpers\Url;
class TopCountriesWidget extends BaseWidget
{
    use Translate;
    const LANG_PREFIX = 'app.dashboard.top_countries-';
    const CACHE_KEY = 'DashboardTopCountriesWidget';
    const TYPE_TRAFFIC = 'traffic';
    const TYPE_SUBSCRIBERS = 'subscribers';
    const TYPE_REVENUE = 'revenue';

    public $type;

    protected $_profitParams;

    /**
     * @var ExchangerPartnerCourses
     */
    protected $_exchanger;

    public function init()
    {
        parent::init();

        if ($this->type === null) {
            $this->type = BaseWidget::getFiltersHandler($this->userId)->getValue(AbstractFiltersHandler::FILTER_COUNTRY_TYPE_NAME, self::TYPE_TRAFFIC);
        }
    }

    public static function getInstance($params = [])
    {
        return (new static([
            'userId' => Yii::$app->user->id,
            'countries' => ArrayHelper::getValue($params, 'countries'),
            'period' => ArrayHelper::getValue($params, 'period'),
            'type' => ArrayHelper::getValue($params, 'filter'),
        ]));
    }

    public function getTooltip()
    {
        $tooltip = [
            self::TYPE_TRAFFIC => Yii::_t('app.dashboard.widget_filter-tooltip-traffic'),
            self::TYPE_SUBSCRIBERS => Yii::_t('app.dashboard.widget_filter-tooltip-subscribers'),
            self::TYPE_REVENUE => Yii::_t('app.dashboard.widget_filter-tooltip-revenue'),
        ];

        return $tooltip[$this->type];
    }

    public function getFrontData()
    {
        $result = [];
        foreach ($this->getDataFromCache() as $label => $value) {
            $result[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        $data = [
            'result' => $result,
            'tooltip' => $this->getTooltip(),
            'isDataEmpty' => empty($result),
            'currencySymbol' => ($this->type === self::TYPE_REVENUE) ? $this->getCurrencySymbol() : '',
        ];

        return $data;
    }

    protected function getData()
    {
        $data = $this->getDataColumn();
        arsort($data);
        return array_slice($data, 0, 10);
    }

    protected function getDataColumn()
    {
        if ($this->type == self::TYPE_REVENUE && !$this->canViewRevenue()) $this->type = null;
        switch ($this->type) {
            case self::TYPE_SUBSCRIBERS:
                return $this->getColumnTypeSubscribers();
                break;
            case self::TYPE_REVENUE;
                return $this->getColumnTypeRevenue();
                break;
            default:
                return $this->getColumnTypeTraffic();
                break;
        }
    }

    /**
     * @return array
     */
    protected function getColumnTypeTraffic()
    {
        $result = [];
        foreach ($this->getApi()->getStatByCountries() as $item) {
            if (isset($item['countryname']) && $value = ArrayHelper::getValue($item, 'clicks', 0)) {
                $result[$item['countryname']] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getColumnTypeSubscribers()
    {
        $result = [];
        foreach ($this->getApi()->getStatByCountries() as $item) {
            if (isset($item['countryname']) && $value = ArrayHelper::getValue($item, 'all_ons', 0)) {
                $result[$item['countryname']] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getColumnTypeRevenue()
    {
        $profitParam = null;
        // tricky: Если видит профит (админский или ресовский), заполняем сумму
        if ($this->getPermissionChecker()->canViewAdminProfit() || $this->getPermissionChecker()->canViewResellerProfit()) {
            $profitParam = 'res_gross_revenue_' . $this->getUserCurrency();
        }

        $result = [];
        foreach ($this->getApi()->getStatByCountries() as $item) {
            if (isset($item['countryname']) && $value = ArrayHelper::getValue($item, $profitParam, 0)) {
                $result[$item['countryname']] = $value;
            }
        }

        return $result;
    }

    protected function getContent()
    {
        $result = ArrayHelper::getValue($this->getFrontData(), 'result');
        return $this->render('top_countries', [
            'data' => $this->getFrontData(),
            'tooltip' => $this->tooltip,
            'url' => $this->getUrl(),
            'hideWidget' => (int)(is_array($result) && count($result) < 2),
        ]);
    }

    public function getTitle()
    {
        return static::translate('title');
    }

    public function getBlockClass()
    {
        return 'top_countries';
    }

    public function getPermission()
    {
        return 'AppBackendWidgetTopCountries';
    }

    public function getToolbarContent()
    {
        return $this->render('toolbar', [
            'url' => $this->getUrl(),
            'type' => $this->type,
            'canViewRevenue' => $this->canViewRevenue()
        ]);
    }

    public function getUrl()
    {
        return Url::to(['/widget/top-countries/']);
    }

    public function getCacheKey()
    {
        return self::CACHE_KEY . '-' . $this->userId . '-' .
            implode('-', [
                $this->type,
                $this->getUserCurrency(),
                str_replace(' ', '', $this->period),
                implode('-', (array)$this->countries),
            ]);
    }
}