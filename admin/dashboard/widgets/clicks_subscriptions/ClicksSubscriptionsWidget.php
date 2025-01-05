<?php

namespace admin\dashboard\widgets\clicks_subscriptions;

use admin\dashboard\models\AbstractFiltersHandler;
use admin\dashboard\widgets\base\BaseWidget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;

class ClicksSubscriptionsWidget extends BaseWidget
{
    use Translate;

    const LANG_PREFIX = 'app.dashboard.clicks_subscriptions-';
    const CACHE_KEY = 'DashboardClicksSubscriptionsWidget';

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
        $data = $this->getDataFromCache(false);
        $data = $this->prepareTimeLine($data);

        $cpaSubs = $rsSubs = $totalSubs = $clicks = [];

        foreach ($data as $date => $item) {
            $cpaSubs[] = [
                'x' => strtotime($date),
                'y' => (int)ArrayHelper::getValue($item, 'count_onetime', 0) +
                    (int)ArrayHelper::getValue($item, 'count_sold', 0),
            ];
            $rsSubs[] = [
                'x' => strtotime($date),
                'y' => (int)ArrayHelper::getValue($item, 'revshare_ons', 0),
            ];
            $totalSubs[] = [
                'x' => strtotime($date),
                'y' => (int)ArrayHelper::getValue($item, 'count_onetime', 0) +
                    (int)ArrayHelper::getValue($item, 'count_sold', 0) +
                    (int)ArrayHelper::getValue($item, 'revshare_ons', 0),
            ];
            $clicks[] = [
                'x' => strtotime($date),
                'y' => (int)ArrayHelper::getValue($item, 'clicks', 0),
            ];
        }

        $isCpaSubsEmpty = $isRsSubsEmpty = $isTotalSubsEmpty = $isClicksEmpty = true;
        foreach ($cpaSubs as $val) {
            if ($val['y'] !== 0) {
                $isCpaSubsEmpty = false;
                break;
            }
        }
        foreach ($rsSubs as $val) {
            if ($val['y'] !== 0) {
                $isRsSubsEmpty = false;
                break;
            }
        }
        foreach ($totalSubs as $val) {
            if ($val['y'] !== 0) {
                $isTotalSubsEmpty = false;
                break;
            }
        }
        foreach ($clicks as $val) {
            if ($val['y'] !== 0) {
                $isClicksEmpty = false;
                break;
            }
        }
        $isDataEmpty = $isCpaSubsEmpty && $isRsSubsEmpty && $isTotalSubsEmpty && $isClicksEmpty;

        $timezoneSecondsOffset = date('Z');

        $result = [];

        $result[] = [
            'label' => Yii::_t('app.dashboard.clicks_subscriptions-total-subs'),
            'data' => $totalSubs,
        ];

        $result[] = [
            'label' => Yii::_t('app.dashboard.clicks_subscriptions-rs-subs'),
            'data' => $rsSubs,
        ];

        $result[] = [
            'label' => Yii::_t('app.dashboard.clicks_subscriptions-cpa-subs'),
            'data' => $cpaSubs,
        ];

        $result[] = [
            'label' => Yii::_t('app.dashboard.clicks_subscriptions-clicks'),
            'data' => $clicks
        ];

        return [
            'result' => $result,
            'isDataEmpty' => $isDataEmpty,
            'timezoneSecondsOffset' => $timezoneSecondsOffset,
        ];
    }

    public function getData()
    {
        $statData = $this->getApi()->getStatByDates();
        $this->usePredictedToday && $statData[date('Y-m-d')] = $this->getPredictedStatToday($statData);

        return $statData;
    }

    protected function getContent()
    {
        $data = $this->getFrontData();
        return $this->render('subscriptions', [
            'url' => $this->getUrl(),
            'data' => $data,
        ]);
    }

    public function getTitle()
    {
        return static::translate('title');
    }

    public function getBlockClass()
    {
        return 'subscriptions';
    }

    public function getPermission()
    {
        return 'AppBackendWidgetClicksSubscriptions';
    }

    public function getUrl()
    {
        return Url::to(['/widget/clicks-subscriptions/']);
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