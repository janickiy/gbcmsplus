<?php

namespace admin\modules\alerts\components;

use admin\modules\alerts\models\Event;
use yii\base\Component;

/**
 * Компонент, перебирающий все добавленные правила
 */
class EventHandler extends Component
{
    protected $events;

    public function init()
    {
        $this->events = $this->getEvents();
    }

    public function run()
    {
        foreach ($this->events as $event) {
            $this->handle($event);
        }
    }

    /**
     * Запуск конкретного хендлера на проверку соответствия правилу
     * @param Event $event
     * @return bool
     */
    protected function handle(Event $event)
    {
        return EventMetricFactory::createHandler($event)->run();
    }

    /**
     * Получение всех активных правил
     * @return Event[]
     */
    protected function getEvents()
    {
        return Event::findActual();
    }
}