<?php

namespace admin\modules\alerts\models;

use admin\modules\alerts\models\query\EventQuery;
use Yii;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;

/**
 * This is the model class for table "alert_events".
 *
 * @property integer $id
 * @property string $name
 * @property integer $priority
 * @property string $emails
 * @property integer $metric
 * @property integer $more
 * @property integer $less
 * @property integer $value
 * @property integer $is_percent
 * @property integer $minutes
 * @property integer $interval_periods_sample
 * @property integer $is_consider_last_days
 * @property integer $is_active
 * @property integer $checking_type
 * @property integer $check_interval
 *
 *
 * @property EventFilter[] $filters
 */
class Event extends \yii\db\ActiveRecord
{
    const PRIORITY_INFO = 1;
    const PRIORITY_ALERT = 2;
    const PRIORITY_WARNING = 3;

    const METRIC_HIT = 1;
    const METRIC_TB = 2;
    const METRIC_UNIQUE = 3;
    const METRIC_CALLS = 4;
    const METRIC_SOLD = 5;
    const METRIC_IK = 6;
    const METRIC_SUBSCRIBED = 7;
    const METRIC_CPR_RUB = 8;
    const METRIC_CPR_USD = 9;
    const METRIC_CPR_EUR = 10;
    const METRIC_ECPM_RUB = 11;
    const METRIC_ECPM_USD = 12;
    const METRIC_ECPM_EUR = 13;
    const METRIC_REV_SUB_RUB = 14;
    const METRIC_REV_SUB_USD = 15;
    const METRIC_REV_SUB_EUR = 16;
    const METRIC_PROFIT_RUB = 17;
    const METRIC_PROFIT_USD = 18;
    const METRIC_PROFIT_EUR = 19;
    const METRIC_HIT_MULTI = 20;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /** @const int Тип проверки "Сравнивать с периодами" */
    const CHECKING_PERIOD = 1;
    /** @const int Тип проверки "Достижение лимита" */
    const CHECKING_LIMIT = 2;

    /** @var array */
    protected $_filterValues = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'alert_events';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['more'], 'required', 'requiredValue' => 1, 'when' => function ($model) {
                return empty($model->more) && empty($model->less);
            }, 'whenClient' => 'function (attribute, value) {
              return !$("#event-more").prop("checked") && !$("#event-less").prop("checked");
            }', 'message' => Yii::_t('alerts.event.more_less_required')],
            [['name', 'priority', 'metric', 'value', 'minutes', 'checking_type'], 'required'],
            [['priority', 'metric', 'more', 'less', 'is_active', 'is_percent', 'interval_periods_sample', 'is_consider_last_days', 'checking_type'], 'integer'],
            ['value', 'number'],
            ['minutes', function ($attribute, $params) {
                if ($this->$attribute % 5) {
                    $this->addError($attribute, Yii::_t('alerts.event.multiplicity'));
                    return false;
                }
                return true;
            }],
            ['minutes', 'integer', 'min' => 10],
            ['interval_periods_sample', 'default', 'value' => 1],
            ['check_interval', 'integer', 'min' => 0],
            ['name', 'unique'],
            [['emails'], function ($attribute, $params) {
                $emailValidator = new EmailValidator();
                $emails = explode(',', $this->$attribute);
                foreach ($emails as $email) {
                    if (!$emailValidator->validate($email)) {
                        $this->addError($attribute, Yii::_t('alerts.event.emails-error', ['mistake' => $email]));
                        return false;
                    }
                }
                return true;
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $this->emails = array_map(function ($email) {
            return trim($email);
        }, explode(',', $this->emails));

        $this->emails = implode(',', $this->emails);
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::_t('alerts.event.name'),
            'priority' => Yii::_t('alerts.event.priority'),
            'checking_type' => Yii::_t('alerts.event.checking_type'),
            'emails' => Yii::_t('alerts.event.emails'),
            'metric' => Yii::_t('alerts.event.metric'),
            'more' => Yii::_t('alerts.event.more'),
            'less' => Yii::_t('alerts.event.less'),
            'value' => Yii::_t('alerts.event.value'),
            'is_percent' => Yii::_t('alerts.event.is_percent'),
            'minutes' => Yii::_t('alerts.event.minutes'),
            'interval_periods_sample' => Yii::_t('alerts.event.interval_periods_sample'),
            'is_consider_last_days' => Yii::_t('alerts.event.is_consider_last_days'),
            'is_active' => Yii::_t('alerts.event.is_active'),
            'check_interval' => Yii::_t('alerts.event.check_interval'),
        ];
    }

    public static function find()
    {
        return new EventQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // При переключении на "Достижение порога" убираем ненужные значения
        if ($this->checking_type == self::CHECKING_LIMIT) {
            $this->more = true;
            $this->less = false;
            $this->is_percent = false;
            $this->interval_periods_sample = 0;
            $this->is_consider_last_days = false;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Логируем после изменения правила
        (new EventLog([
            'event_id' => $this->id,
        ]))->save();

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilters()
    {
        return $this->hasMany(EventFilter::class, ['event_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(EventLog::class, ['event_id' => 'id']);
    }

    /**
     * получить массив приоритетов
     * @return array
     */
    public static function getPriorities()
    {
        return [
            self::PRIORITY_INFO => Yii::_t('alerts.main.priority-info'),
            self::PRIORITY_ALERT => Yii::_t('alerts.main.priority-alert'),
            self::PRIORITY_WARNING => Yii::_t('alerts.main.priority-warning'),
        ];
    }

    /**
     * получить массив типов проверки
     * @return array
     */
    public static function getCheckingTypes()
    {
        return [
            self::CHECKING_PERIOD => Yii::_t('alerts.main.checking-period'),
            self::CHECKING_LIMIT => Yii::_t('alerts.main.checking-limit'),
        ];
    }

    /**
     * получить массив метрик
     * @return array
     */
    public static function getMetrics()
    {
        return [
            self::METRIC_HIT => Yii::_t('alerts.main.metric-hit'),
            self::METRIC_HIT_MULTI => Yii::_t('alerts.main.metric-hit-by-source'),
            self::METRIC_TB => Yii::_t('alerts.main.metric-tb'),
            self::METRIC_UNIQUE => Yii::_t('alerts.main.metric-unique'),
            self::METRIC_CALLS => Yii::_t('alerts.main.metric-calls'),
            self::METRIC_SOLD => Yii::_t('alerts.main.metric-sold'),
            self::METRIC_IK => Yii::_t('alerts.main.metric-ik'),
            self::METRIC_SUBSCRIBED => Yii::_t('alerts.main.metric-subscribed'),
            self::METRIC_CPR_RUB => Yii::_t('alerts.main.metric-cpr_rub'),
            self::METRIC_CPR_USD => Yii::_t('alerts.main.metric-cpr_usd'),
            self::METRIC_CPR_EUR => Yii::_t('alerts.main.metric-cpr_eur'),
            self::METRIC_ECPM_RUB => Yii::_t('alerts.main.metric-ecpm_rub'),
            self::METRIC_ECPM_USD => Yii::_t('alerts.main.metric-ecpm_usd'),
            self::METRIC_ECPM_EUR => Yii::_t('alerts.main.metric-ecpm_eur'),
            self::METRIC_REV_SUB_RUB => Yii::_t('alerts.main.metric-rev_sub_rub'),
            self::METRIC_REV_SUB_USD => Yii::_t('alerts.main.metric-rev_sub_usd'),
            self::METRIC_REV_SUB_EUR => Yii::_t('alerts.main.metric-rev_sub_eur'),
            self::METRIC_PROFIT_RUB => Yii::_t('alerts.main.metric-profit_rub'),
            self::METRIC_PROFIT_USD => Yii::_t('alerts.main.metric-profit_usd'),
            self::METRIC_PROFIT_EUR => Yii::_t('alerts.main.metric-profit_eur'),
        ];
    }

    /**
     * @return self[]
     */
    public static function findActual()
    {
        return self::find()
            ->active()
            ->with('filters')
            ->all();
    }

    /**
     * Метод для перегрузки значений фильтра
     * Значения перезаписываются
     *
     * Если значение не перегружено, оно будет вытащено из БД соответствующим методом
     *
     * @param $filterType
     * @param $value
     *
     * @return Event
     */
    public function setFilterByType($filterType, $value)
    {
        $this->_filterValues[$filterType] = $value;

        return $this;
    }

    /**
     * Массив id стран в фильтре
     * @return array
     */
    public function getCountries()
    {
        return $this->getFiltersByType(EventFilter::BY_COUNTRIES);
    }

    /**
     * Массив id операторов в фильтре
     * @return array
     */
    public function getOperators()
    {
        return $this->getFiltersByType(EventFilter::BY_OPERATORS);
    }

    /**
     * Массив id пользователей в фильтре
     * @return array
     */
    public function getUsers()
    {
        return $this->getFiltersByType(EventFilter::BY_PARTNERS);
    }

    /**
     * Массив id способов оплаты в фильтре
     * @return array
     */
    public function getLandingPayTypes()
    {
        return $this->getFiltersByType(EventFilter::BY_PAY_TYPES);
    }

    /**
     * Массив id потоков в фильтре
     * @return array
     */
    public function getStreams()
    {
        return $this->getFiltersByType(EventFilter::BY_STREAMS);
    }

    /**
     * Массив id источников в фильтре
     * @return array
     */
    public function getSources()
    {
        return $this->getFiltersByType(EventFilter::BY_SOURCES);
    }

    /**
     * Массив id лендингов в фильтре
     * @return array
     */
    public function getLandings()
    {
        return $this->getFiltersByType(EventFilter::BY_LANDINGS);
    }

    /**
     * Массив id платформ в фильтре
     * @return array
     */
    public function getPlatforms()
    {
        return $this->getFiltersByType(EventFilter::BY_PLATFORMS);
    }

    /**
     * Массив id фильтров по типу
     * @param $type
     * @return array
     */
    public function getFiltersByType($type)
    {
        if (array_key_exists($type, $this->_filterValues)) {
            return $this->_filterValues[$type];
        }
        return ArrayHelper::getColumn($this->getFilters()->andWhere(['type' => $type])->all(), 'value');
    }

    /**
     * @return $this
     */
    public function setEnabled()
    {
        $this->is_active = self::STATUS_ACTIVE;
        return $this;
    }

    /**
     * @return $this
     */
    public function setDisabled()
    {
        $this->is_active = self::STATUS_INACTIVE;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->is_active != self::STATUS_ACTIVE;
    }

    /**
     * Получить сгруппированные данные по фильтрам правила
     * @return array
     */
    public function getFilterValues()
    {
        $result = [];
        $filterNames = EventFilter::getFilters();
        foreach ($this->filters as $filter) {
            $values = ArrayHelper::map(EventFilter::getFilterValues($filter->type), 'id', 'name');

            $result[$filter->type]['name'] = $filterNames[$filter->type];
            $result[$filter->type]['values'][] = [
                'id' => $filter->value,
                'name' => $values[$filter->value],
            ];
        }
        return $result;
    }
}
