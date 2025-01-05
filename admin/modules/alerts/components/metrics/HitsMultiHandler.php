<?php
namespace admin\modules\alerts\components\metrics;

use admin\modules\alerts\components\events\AlertEvent;
use admin\modules\alerts\components\events\InfoEvent;
use admin\modules\alerts\components\events\WarningEvent;
use admin\modules\alerts\models\Event;
use admin\modules\alerts\models\EventFilter;
use admin\modules\alerts\models\EventLog;
use Exception;
use mcms\promo\models\Source;
use yii\db\Query;

class HitsMultiHandler extends BaseHandler
{
  public $continueRunning = false;

  public function __construct(Event $event, array $config = [])
  {
    parent::__construct($event, $config);

    if ($this->continueRunning) return ;

    // Получить все источники по пользователям и выполнить run в каждом хендлере
    // Это делается для того, чтобы алерты приходили по каждому пользователю

    // получить все сорцы
    // зассетить в фильтры
    // выполнить ран у каждого класса хендлера
    /** @var Source[] $sourceIterator */
    $sourceIterator = Source::find()
      ->where(['status' => Source::STATUS_APPROVED])
      ->each()
      ;

    $handlers = [];

    foreach ($sourceIterator as $source) {
      $modifiedEvent = clone $event;
      $modifiedEvent->setFilterByType(EventFilter::BY_SOURCES, $source->id);

      $handlers[] = new static(
        $modifiedEvent,
        array_merge($config, ['continueRunning' => true])
      );
    }

    array_map(function(HitsMultiHandler $handler) {
      $handler->run();
    }, $handlers);
  }

  protected function isEventActual()
  {
    $query = EventLog::find()
      ->where([
        'event_id' => $this->event->id,
        'model_id' => $this->event->getFiltersByType(EventFilter::BY_SOURCES)
      ])
      ->andWhere('created_at > :time', [':time' => time() - $this->event->check_interval])
    ;

    return !$query->exists();
  }


  public function run()
  {
    if (!$this->continueRunning) return ;
    if (!$this->isEventActual()) return ;
    // Логируем обработку правила
    $sourceId = $this->event->getFiltersByType(EventFilter::BY_SOURCES);
    $this->event->link('logs', new EventLog([
      'model_id' => $sourceId,
    ]));

    // если все ок, выходим
    if ($this->isNormal()) return;

    // дергаем событие
    $event = null;
    switch($this->event->priority) {
      case Event::PRIORITY_INFO:
        $event = new InfoEvent();
        break;
      case Event::PRIORITY_ALERT:
        $event = new AlertEvent();
        break;
      case Event::PRIORITY_WARNING:
        $event = new WarningEvent();
        break;
      default:
        throw new Exception('Неверный тип уведомления');
    }

    $event->eventName = $this->event->name . 'Source: #' . $sourceId;
    $event->metric = Event::getMetrics()[$this->event->metric];
    $event->is_more = $this->isMore();
    $event->value = $this->event->is_percent ? $this->getPercentDeviation() : $this->getDeviation();
    $event->is_percent = $this->event->is_percent;
    $event->minutes = $this->event->minutes;
    $event->addEmails = $this->event->emails;
    $event->comparedValue = $this->getComparedMetric();
    $event->currentValue = $this->getCurrentMetric();
    $event->filters = $this->getFiltersString();
    $event->trigger();

    echo $this->event->name . ' - ' . $event::class . "\n";
  }

  /**
   * @inheritdoc
   */
  public function baseQuery(array $where = [])
  {
    return (new Query())
      ->select(['count_hits' => "COUNT(st.id)"])
      ->from(['st' => 'hits'])
      ->andFilterWhere(['st.source_id' => $this->event->sources])
      ->leftJoin('operators o', 'o.id = st.operator_id')
      ->leftJoin('sources s', 's.id = st.source_id')
      ->leftJoin('landings l', 'l.id = st.landing_id')
      ->andWhere($where);
  }
}
