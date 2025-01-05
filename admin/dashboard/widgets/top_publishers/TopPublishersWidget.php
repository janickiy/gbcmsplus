<?php

namespace admin\dashboard\widgets\top_publishers;

use admin\dashboard\models\AbstractFiltersHandler;
use admin\dashboard\widgets\base\BaseWidget;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * Class TopPublishersWidget
 * @package admin\dashboard\widgets\top_publishers
 */
class TopPublishersWidget extends BaseWidget
{
    use Translate;
    const TYPE_TRAFFIC = 'traffic';
    const TYPE_SUBSCRIBERS = 'subscribers';
    const TYPE_REVENUE = 'revenue';
    const LANG_PREFIX = 'app.dashboard.top_publishers-';
    const CACHE_KEY = 'DashBoardTopPublishersWidget';

    private $colors = [
        '#E90D46',
        '#609DFB',
        '#42BAAE',
        '#41B463',
        '#82BF5A',
        '#F5B751',
        '#F3974D',
        '#E56445',
        '#D44450',
        '#D56FA9',
    ];

    public $type;

    protected $_profitParams;

    public function init()
    {
        parent::init();
        // tricky: Менеджеру отобразятся только его партнеры
        $this->users = User::getManageUsersByUserId($this->userId) ?: $this->users;

        if ($this->type === null) {
            $this->type = BaseWidget::getFiltersHandler($this->userId)->getValue(AbstractFiltersHandler::FILTER_PUBLISHER_TYPE_NAME, self::TYPE_TRAFFIC);
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
        $i = 0;

        foreach ($this->getDataFromCache() as $label => $value) {
            $result[] = [
                'label' => $label,
                'value' => $value,
                'color' => ArrayHelper::getValue($this->colors, $i),
            ];
            if ($i++ > 9) {
                break;
            }
        };

        $data = [
            'result' => $result,
            'caption' => $this->getTooltip(),
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
     * Возвращает инвесторскую или обычную статистику
     * @return array
     */
    private function getStatistic()
    {
        return $this->getApi()->getStatByUsers();
    }

    /**
     * @return array
     */
    protected function getColumnTypeTraffic()
    {
        $result = [];
        foreach ($this->getStatistic() as $item) {
            $value = ArrayHelper::getValue($item, 'clicks', 0);
            if (isset($item['username']) && $value) {
                $result[$item['username']] = $value;
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
        foreach ($this->getStatistic() as $item) {
            if (isset($item['username']) && $value = ArrayHelper::getValue($item, 'all_ons', 0)) {
                $result[$item['username']] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getColumnTypeRevenue()
    {
        $result = [];
        foreach ($this->getStatistic() as $item) {
            if (isset($item['username']) && $value = ArrayHelper::getValue($item, 'partner_gross_revenue_' . $this->getUserCurrency(), 0)) {
                $result[$item['username']] = $value;
            }
        }

        return $result;
    }

    protected function getContent()
    {
        $data = $this->getFrontData();
        return $this->render('top_publishers', [
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
        return 'publishers';
    }

    public function getPermission()
    {
        return 'AppBackendWidgetTopPublishers';
    }

    /**
     * @inheritdoc
     */
    public function hasAccess()
    {
        return Yii::$app->authManager->checkAccess($this->userId, $this->getPermission());
    }

    public function getToolbarContent()
    {
        return $this->render('toolbar', ['type' => $this->type, 'canViewRevenue' => $this->canViewRevenue()]);
    }

    public function getUrl()
    {
        return Url::to(['/widget/top-publishers/']);
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