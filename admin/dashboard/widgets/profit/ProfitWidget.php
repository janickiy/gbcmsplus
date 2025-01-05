<?php

namespace admin\dashboard\widgets\profit;

use admin\dashboard\models\AbstractFiltersHandler;
use admin\dashboard\widgets\base\BaseWidget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;

class ProfitWidget extends BaseWidget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.profit-';
    const CACHE_KEY = 'DashboardProfitWidget';

    public $showForecast;

    protected $_profitParams;

    public $usePredictedToday;

    public function init()
    {
        parent::init();

        if ($this->usePredictedToday === null) {
            $this->usePredictedToday = BaseWidget::getFiltersHandler($this->userId)->getValue(AbstractFiltersHandler::FILTER_FORECAST_NAME, true);
        }
    }

    public static function getInstance($params = [])
    {
        return (new static([
            'userId' => Yii::$app->user->id,
            'countries' => ArrayHelper::getValue($params, 'countries'),
            'period' => ArrayHelper::getValue($params, 'period'),
            'usePredictedToday' => Json::decode(ArrayHelper::getValue($params, 'forecast')),
        ]));
    }

    public function getFrontData()
    {
        $data = $this->getDataFromCache();
        $isDataEmpty = !$data;

        $revshareParam = null;
        $cpaParam = null;
        // TRICKY Подменяем данные последнего дня для того, чтобы совпадали с гаджетом валового дохода
        // Если включено предсказание или фильтр по странам, подменять нет смысла
        // @see https://rgkdev.atlassian.net/wiki/spaces/MD/pages/25493508/Dashboard
        $changeTodaysData = !$this->usePredictedToday && !$this->countries;
        // tricky: Если видит профит (админский или ресовский), заполняем суммы
        if ($this->getPermissionChecker()->canViewAdminProfit() || $this->getPermissionChecker()->canViewResellerProfit()) {
            $revshareParam = 'res_profit_' . $this->getUserCurrency();
            $cpaParam = 'res_cpa_profit_' . $this->getUserCurrency();
        }

        $revshare = $this->prepareTimeLine(ArrayHelper::getColumn($data, $revshareParam));
        $cpa = $this->prepareTimeLine(ArrayHelper::getColumn($data, $cpaParam));

        if ($changeTodaysData && ($valuesByCurrency = $this->prepareValuesByCurrency(self::TYPE_GROSS, false))) {
            $revshare[date('Y-m-d')] = round(ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'rub'), 'convertedRS') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'usd'), 'convertedRS') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'eur'), 'convertedRS'), 2);
            $cpa[date('Y-m-d')] = round(ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'rub'), 'convertedCPA') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'usd'), 'convertedCPA') +
                ArrayHelper::getValue(ArrayHelper::getValue($valuesByCurrency, 'eur'), 'convertedCPA'), 2);
        }

        $datasets = [
            [
                'label' => Yii::_t('app.dashboard.profit-rs-profit'),
                'data' => array_values($revshare),
                'backgroundColor' => '#C3133E',
            ],
        ];
        $datasets[] = [
            'label' => Yii::_t('app.dashboard.profit-cpa-profit'),
            'data' => array_values($cpa),
            'backgroundColor' => '#EA0F46',
        ];

        $result = [
            'labels' => array_map(function ($item) {
                return date('d.m', strtotime($item));
            }, array_keys($revshare)),
            'datasets' => $datasets,
            'isDataEmpty' => $isDataEmpty,
        ];

        $result['currencySymbol'] = $this->getCurrencySymbol();

        return [
            'result' => $result,
        ];
    }

    protected function getData()
    {
        $statData = $this->getApi()->getStatByDates();
        $this->usePredictedToday && $statData[date('Y-m-d')] = $this->getPredictedStatToday($statData);

        return $statData;
    }

    protected function getContent()
    {
        $data = $this->getFrontData();
        return $this->render('profit', [
            'data' => $data,
            'url' => $this->getUrl(),
        ]);
    }

    public function getTitle()
    {
        return static::translate('title');
    }

    public function getBlockClass()
    {
        return 'profit';
    }

    public function getPermission()
    {
        return 'AppBackendWidgetProfit';
    }

    public function getUrl()
    {
        return Url::to(['/widget/profit/']);
    }

    public function getCacheKey()
    {
        return self::CACHE_KEY . '-' . $this->userId . '-' .
            implode('-', [
                str_replace(' ', '', $this->period),
                implode('-', (array)$this->countries),
                (int)$this->usePredictedToday,
            ]);
    }
}