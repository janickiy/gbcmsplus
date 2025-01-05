<?php

namespace admin\modules\alerts\components\metrics;

use admin\modules\alerts\components\events\AlertEvent;
use admin\modules\alerts\components\events\InfoEvent;
use admin\modules\alerts\components\events\WarningEvent;
use admin\modules\alerts\models\Event;
use admin\modules\alerts\models\EventLog;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Базовый класс обработчиков
 * Class BaseHandler
 * @package admin\modules\alerts\components\metrics
 */
abstract class BaseHandler extends BaseObject
{
    const CURRENCY_RUB = 1;
    const CURRENCY_USD = 2;
    const CURRENCY_EUR = 3;

    /** @var int текущее время, до которого вычисляется статистика */
    public $time;
    /** @var int время, от которого вычисляется статистика */
    public $timeFrom;

    protected $_currentMetric;
    protected $_comparedMetric;
    protected $_deviation;
    protected $_maxDeviation;

    protected $_is_more;

    /** @var Event $event */
    public $event;

    /**
     * @param array $where
     * @return ActiveQuery
     */
    abstract public function baseQuery(array $where = []);

    /**
     * @inheritdoc
     */
    public function __construct(Event $event, array $config = [])
    {
        $this->event = $event;

        $this->time = time();
        $this->timeFrom = $this->time - $this->event->minutes * 60;
        parent::__construct($config);
    }

    /**
     * Проверка на то, что событие актуально, чтобы не проверялось часто
     * @return bool
     */
    protected function isEventActual()
    {
        return !EventLog::find()
            ->where(['event_id' => $this->event->id])
            ->andWhere('created_at > :time', [':time' => time() - $this->event->check_interval])
            ->exists();
    }

    /**
     * @throws Exception
     */
    public function run()
    {
        if (!$this->isEventActual()) return;

        // Логируем обработку правила
        $this->event->link('logs', new EventLog());

        // если все ок, выходим
        if ($this->isNormal()) return;

        // дергаем событие
        $event = null;
        switch ($this->event->priority) {
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

        $event->eventName = $this->event->name;
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
     * Увеличился ли показатель
     * @return boolean
     */
    protected function isMore()
    {
        return $this->_is_more;
    }

    /**
     * @param boolean $value
     * @return bool
     */
    private function setIsMore($value)
    {
        return $this->_is_more = (bool)$value;
    }

    /**
     * Получить фильтры в виде строки
     * @return string
     */
    protected function getFiltersString()
    {
        $result = '';
        foreach ($this->event->getFilterValues() as $filter) {
            $result .= $filter['name'] . ': ' . implode(',', ArrayHelper::getColumn($filter['values'], 'name')) . '<br>';
        }
        return $result ?: Yii::_t('alerts.notifications.empty-filters');
    }

    /**
     * Проверяем, что отклонение в пределах нормы
     * @return bool
     */
    public function isNormal()
    {
        return $this->getMaxDeviation() >= $this->getDeviation();
    }

    /**
     * Максимальное возможное отклонение от эталона
     * @return int
     */
    public function getMaxDeviation()
    {
        if (!is_null($this->_maxDeviation)) return $this->_maxDeviation;
        return $this->_maxDeviation = $this->event->is_percent && $this->event->value
            ? $this->getComparedMetric() / 100 * $this->event->value
            : $this->event->value;
    }

    /**
     * Рассчет отклонения от эталона
     * @return int
     */
    public function getDeviation()
    {
        if (!is_null($this->_deviation)) return $this->_deviation;
        $devMore = 0;
        $devLess = 0;

        if ($this->event->more) {
            $devMore = $this->getCurrentMetric() - $this->getComparedMetric();
        }
        if ($this->event->less) {
            $devLess = $this->getComparedMetric() - $this->getCurrentMetric();
        }
        if ($devMore > $devLess) {
            $this->_deviation = $devMore;
            $this->setIsMore(true);
        } else {
            $this->_deviation = $devLess;
            $this->setIsMore(false);
        }

        return $this->_deviation;
    }

    /**
     * Рассчет отклонения от эталона в процентах
     * @return int
     */
    public function getPercentDeviation()
    {
        return $this->getComparedMetric()
            ? (int)($this->getDeviation() / ($this->getComparedMetric() / 100))
            : 100;
    }

    /**
     * получаем значение метрики, которую проверяем
     * @return int
     */
    public function getCurrentMetric()
    {
        if (!is_null($this->_currentMetric)) return $this->_currentMetric;
        return $this->_currentMetric = $this->getMetric(null, $this->timeFrom, $this->time);
    }

    /**
     * получаем значение эталонной метрики (с которой сравниваем)
     * @return int
     */
    public function getComparedMetric()
    {
        if (!is_null($this->_comparedMetric)) return $this->_comparedMetric;

        // Если тип проверки "Достижение порога", эталонная метрика равна 0
        if ($this->event->checking_type == Event::CHECKING_LIMIT) {
            $this->_comparedMetric = 0;
            return $this->_comparedMetric;
        }

        $timeFrom = $this->timeFrom - $this->event->minutes * 60 * $this->event->interval_periods_sample;

        $this->_comparedMetric = $this->event->is_consider_last_days
            ? $this->getMetric($this->event->interval_periods_sample)
            : $this->getMetric(null, $timeFrom, $this->timeFrom);

        return is_int($this->_comparedMetric * 1)
            ? $this->_comparedMetric = (int)($this->_comparedMetric / $this->event->interval_periods_sample)
            : $this->_comparedMetric = round($this->_comparedMetric / $this->event->interval_periods_sample, 3);
    }

    /**
     * Плучение значения необходимой метрики
     * @param $countLastDays integer
     * @param $timeFrom integer
     * @param $timeTo integer
     * @return integer
     */
    private function getMetric($countLastDays, $timeFrom = null, $timeTo = null)
    {
        if ($countLastDays) {
            $where = ['or'];
            for ($i = 1; $i <= $countLastDays; $i++) {
                $timeFrom = $this->timeFrom - $i * 3600 * 24;
                $timeTo = $this->time - $i * 3600 * 24;
                $where[] = ['and', ['>', 'st.time', $timeFrom], ['<', 'st.time', $timeTo]];
            }
        } else {
            $where = ['and', ['>', 'st.time', $timeFrom], ['<', 'st.time', $timeTo]];
        }
        $query = $this->baseQuery($where);
        $this->handleFilters($query);
        return $query->scalar();
    }

    /**
     * Добавление фильтров к запросу
     * @param Query $query
     */
    protected function handleFilters(Query &$query)
    {
        /** @var $query Query */
        $query->andFilterWhere(['country_id' => $this->event->countries]);
        $query->andFilterWhere(['st.operator_id' => $this->event->operators]);
        $query->andFilterWhere(['user_id' => $this->event->users]);
        $query->andFilterWhere(['st.landing_pay_type_id' => $this->event->landingPayTypes]);
        $query->andFilterWhere(['stream_id' => $this->event->streams]);
        $query->andFilterWhere(['st.landing_id' => $this->event->landings]);
        $query->andFilterWhere(['st.platform_id' => $this->event->platforms]);

        $query->andFilterWhere(['not in', 'user_id', $this->getIgnoreIds()]);
    }

    /**
     * Список id пользователей, которые нужно скрыть от реса
     * @return array
     */
    protected function getIgnoreIds()
    {
        $resellerId = null;
        if ($reseller = Yii::$app->getModule('users')->api('usersByRoles', ['reseller'])->getResult()) {
            $resellerId = current($reseller)['id'];
        }

        return Yii::$app->getModule('users')
            ->api('notAvailableUserIds', [
                'userId' => $resellerId
            ])
            ->getResult();
    }
}
