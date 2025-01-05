<?php

namespace admin\modules\alerts\components;


use admin\modules\alerts\components\metrics\BaseHandler;
use admin\modules\alerts\components\metrics\CallsHandler;
use admin\modules\alerts\components\metrics\CPREurHandler;
use admin\modules\alerts\components\metrics\CPRRubHandler;
use admin\modules\alerts\components\metrics\CPRUsdHandler;
use admin\modules\alerts\components\metrics\ECPMEurHandler;
use admin\modules\alerts\components\metrics\ECPMRubHandler;
use admin\modules\alerts\components\metrics\ECPMUsdHandler;
use admin\modules\alerts\components\metrics\HitsHandler;
use admin\modules\alerts\components\metrics\HitsMultiHandler;
use admin\modules\alerts\components\metrics\IKHandler;
use admin\modules\alerts\components\metrics\ProfitEurHandler;
use admin\modules\alerts\components\metrics\ProfitRubHandler;
use admin\modules\alerts\components\metrics\ProfitUsdHandler;
use admin\modules\alerts\components\metrics\RevSubEurHandler;
use admin\modules\alerts\components\metrics\RevSubRubHandler;
use admin\modules\alerts\components\metrics\RevSubUsdHandler;
use admin\modules\alerts\components\metrics\SoldHandler;
use admin\modules\alerts\components\metrics\SubscribedHandler;
use admin\modules\alerts\components\metrics\TbHandler;
use admin\modules\alerts\components\metrics\UniqueHandler;
use admin\modules\alerts\models\Event;
use yii\base\InvalidConfigException;

/**
 * Фабрика обработчиков метрик
 * Class EventMetricFactory
 * @package admin\modules\alerts\components
 */
class EventMetricFactory
{
    private static $types = [
        Event::METRIC_HIT => HitsHandler::class,
        Event::METRIC_HIT_MULTI => HitsMultiHandler::class,
        Event::METRIC_TB => TbHandler::class,
        Event::METRIC_UNIQUE => UniqueHandler::class,
        Event::METRIC_CALLS => CallsHandler::class,
        Event::METRIC_SOLD => SoldHandler::class,
        Event::METRIC_IK => IKHandler::class,
        Event::METRIC_SUBSCRIBED => SubscribedHandler::class,
        Event::METRIC_CPR_RUB => CPRRubHandler::class,
        Event::METRIC_CPR_USD => CPRUsdHandler::class,
        Event::METRIC_CPR_EUR => CPREurHandler::class,
        Event::METRIC_ECPM_RUB => ECPMRubHandler::class,
        Event::METRIC_ECPM_USD => ECPMUsdHandler::class,
        Event::METRIC_ECPM_EUR => ECPMEurHandler::class,
        Event::METRIC_REV_SUB_RUB => RevSubRubHandler::class,
        Event::METRIC_REV_SUB_USD => RevSubUsdHandler::class,
        Event::METRIC_REV_SUB_EUR => RevSubEurHandler::class,
        Event::METRIC_PROFIT_RUB => ProfitRubHandler::class,
        Event::METRIC_PROFIT_USD => ProfitUsdHandler::class,
        Event::METRIC_PROFIT_EUR => ProfitEurHandler::class,
    ];

    /**
     * @param Event $event
     * @param array $params
     * @return BaseHandler
     * @throws InvalidConfigException
     */
    public static function createHandler($event, $params = [])
    {
        if (!array_key_exists($event->metric, self::$types)) {
            throw new InvalidConfigException('Неверный тип метрики');
        }

        $handlerClass = self::$types[$event->metric];
        return new $handlerClass($event, $params);
    }
}
