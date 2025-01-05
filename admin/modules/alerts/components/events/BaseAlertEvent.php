<?php

namespace admin\modules\alerts\components\events;

use mcms\common\event\Event;
use mcms\common\helpers\ArrayHelper;
use Yii;

/**
 * Class BaseAlertEvent
 * @package admin\modules\alerts\components\events
 */
abstract class BaseAlertEvent extends Event
{
    public $eventName;
    public $metric;
    public $is_more;
    public $value;
    public $is_percent;
    public $minutes;
    public $filters;

    public $comparedValue;
    public $currentValue;

    /**
     * Без конструктора будет ошибка в getReplacementsHelp
     * BaseAlertEvent constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    function getReplacements()
    {
        return ArrayHelper::merge(parent::getReplacements(), [
            '{eventName}' => $this->eventName,
            '{metric}' => $this->metric,
            '{moreOrLess}' => $this->is_more ? 'увеличился' : 'уменьшился',
            '{value}' => $this->value . ' ' . ($this->is_percent ? '%' : null),
            '{minutes}' => $this->minutes,
            '{filters}' => $this->filters,
            '{comparedValue}' => $this->comparedValue,
            '{currentValue}' => $this->currentValue,
        ]);
    }

    /**
     * @return array
     */
    function getReplacementsHelp()
    {
        return ArrayHelper::merge(parent::getReplacementsHelp(), [
            '{eventName}' => Yii::_t('alerts.notifications.event_name'),
            '{metric}' => Yii::_t('alerts.notifications.metric'),
            '{moreOrLess}' => Yii::_t('alerts.notifications.more_or_less'),
            '{value}' => Yii::_t('alerts.notifications.value'),
            '{minutes}' => Yii::_t('alerts.notifications.minutes'),
            '{filters}' => Yii::_t('alerts.notifications.filters'),
            '{comparedValue}' => Yii::_t('alerts.notifications.compared-value'),
            '{currentValue}' => Yii::_t('alerts.notifications.current-value'),
        ]);
    }

    /**
     * @param null $id
     * @return array
     */
    public static function getUrl($id = null)
    {
        return ['/statistic/default/index/'];
    }
}