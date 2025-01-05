<?php

namespace mcms\common\helpers;

use mcms\common\module\api\ApiResult;
use mcms\notifications\Module;
use Yii;

/**
 * Class SmartMenuHelper
 * @package mcms\common\helpers
 */
class SmartMenuHelper
{

    private static $notifications;

    /**
     * Форматирует пункты меню под класс [[rgk\theme\smartadmin\widgets\menu\SmartAdminMenu]]
     * @param $items
     * @return array
     */
    public static function format($items)
    {
        $newItems = [];

        foreach ($items as $item) {
            $newItems[] = self::formatItem($item);
        }

        return $newItems;
    }

    /**
     * Форматируем 1 пункт меню
     * @param $item
     * @return array
     */
    protected static function formatItem($item)
    {
        $newItem = $item;
        unset($newItem['items']);

        $eventCount = self::getEventsCount($item);

        // рекурсивно форматируем дочерние элементы
        if (isset($item['items'])) {
            foreach ($item['items'] as $child) {
                $newItem['items'][] = self::formatItem($child);
            }
        }

        $newItem['templateValues'] = [
            'badge' => $eventCount ? '<span class="badge pull-right inbox-badge my-badge' . (isset($newItem['items']) ? ' margin-right-13' : '') . '">' . $eventCount . '</span>' : ''
        ];

        unset($newItem['events']);
        return $newItem;
    }


    /**
     * Получить количество событий для бейджика
     * @param $item
     * @return float|int
     */
    protected static function getEventsCount($item)
    {
        $count = array_sum(array_map(function ($event) {
            return ArrayHelper::getValue(self::getNotifications(), $event, 0);
        }, ArrayHelper::getValue($item, 'events', [])));

        // рекурсивно суммируем все дочерние элементы
        if (isset($item['items'])) foreach ($item['items'] as $child) {
            $count += self::getEventsCount($child);
        }

        return $count;
    }

    /**
     * @return array
     */
    protected static function getNotifications()
    {
        if (self::$notifications) return self::$notifications;
        /** @var Module $module */
        $module = Yii::$app->getModule('notifications');
        /** @var ApiResult $api */
        $api = $module->api('getBrowserNotificationCount', [
            'user_id' => Yii::$app->user->id,
        ]);
        self::$notifications = $api->getResult();

        return self::$notifications;
    }

}