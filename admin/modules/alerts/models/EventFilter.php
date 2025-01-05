<?php

namespace admin\modules\alerts\models;

use Exception;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingPayType;
use mcms\promo\models\Operator;
use mcms\promo\models\Platform;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use Yii;

/**
 * This is the model class for table "alert_event_filters".
 *
 * @property integer $id
 * @property integer $event_id
 * @property integer $type
 * @property integer $value
 *
 * @property Event $event
 */
class EventFilter extends \yii\db\ActiveRecord
{
    const BY_COUNTRIES = 1;
    const BY_OPERATORS = 2;
    const BY_PARTNERS = 3;
    const BY_PAY_TYPES = 4;
    const BY_STREAMS = 6;
    const BY_SOURCES = 7;
    const BY_LANDINGS = 8;
    const BY_PLATFORMS = 9;

    const CACHE_KEY_FILTER_VALUES = 'cache-key-filter-values-';
    const CACHE_KEY_FILTER_VALUES_DURATION = 600;


    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {

        return parent::load($data, $formName);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'alert_event_filters';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_id', 'type', 'value'], 'required'],
            [['event_id', 'type', 'value'], 'integer'],
            [['event_id'], 'exist', 'skipOnError' => true, 'targetClass' => Event::class, 'targetAttribute' => ['event_id' => 'id']],
            [['event_id', 'type', 'value'], 'unique', 'targetAttribute' => ['event_id', 'type', 'value'], 'message' => Yii::_t('alerts.event_filter.filter-unique')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_id' => 'Event ID',
            'type' => Yii::_t('alerts.event_filter.type'),
            'value' => Yii::_t('alerts.event_filter.value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(Event::class, ['id' => 'event_id']);
    }

    /**
     * получить массив фильтров
     * @return array
     */
    public static function getFilters()
    {
        return [
            self::BY_COUNTRIES => Yii::_t('alerts.event_filter.filter-countries'),
            self::BY_OPERATORS => Yii::_t('alerts.event_filter.filter-operators'),
            self::BY_PARTNERS => Yii::_t('alerts.event_filter.filter-partners'),
            self::BY_PAY_TYPES => Yii::_t('alerts.event_filter.filter-pay-types'),
            self::BY_STREAMS => Yii::_t('alerts.event_filter.filter-streams'),
            self::BY_SOURCES => Yii::_t('alerts.event_filter.filter-sources'),
            self::BY_LANDINGS => Yii::_t('alerts.event_filter.filter-landings'),
            self::BY_PLATFORMS => Yii::_t('alerts.event_filter.filter-platforms'),
        ];
    }

    /**
     * Получить массив возможных значений фильтров
     * @param $id
     * @return null|array
     */
    public static function getFilterValues($id)
    {
        if (($data = Yii::$app->cache->get(self::CACHE_KEY_FILTER_VALUES . $id))) {
            return $data;
        }
        $data = null;
        switch ($id) {
            case self::BY_COUNTRIES:
                $data = self::getCountries();
                break;
            case self::BY_OPERATORS:
                $data = self::getOperators();
                break;
            case self::BY_PARTNERS:
                $data = self::getPartners();
                break;
            case self::BY_PAY_TYPES:
                $data = self::getPayTypes();
                break;
            case self::BY_STREAMS:
                $data = self::getStreams();
                break;
            case self::BY_SOURCES:
                $data = self::getSources();
                break;
            case self::BY_LANDINGS:
                $data = self::getLandings();
                break;
            case self::BY_PLATFORMS:
                $data = self::getPlatforms();
                break;
        }
        Yii::$app->cache->set(
            self::CACHE_KEY_FILTER_VALUES . $id,
            $data,
            self::CACHE_KEY_FILTER_VALUES_DURATION
        );
        return $data;
    }

    /**
     * @return array
     */
    private static function getCountries()
    {
        return self::responseFormatter(
            Country::find()->each(),
            ['id', 'name']
        );
    }

    /**
     * @return array
     */
    private static function getOperators()
    {
        return self::responseFormatter(
            Operator::find()->each(),
            ['id', 'name']
        );
    }

    /**
     * @return array
     */
    private static function getPartners()
    {
        return self::responseFormatter(
            Yii::$app->getModule('users')->api('usersByRoles', [
                'partner',
                'pagination' => false
            ])->getResult(),
            ['id', 'username']
        );
    }

    /**
     * @return array
     */
    private static function getPayTypes()
    {
        return self::responseFormatter(
            LandingPayType::find()->each(),
            ['id', 'name']
        );
    }

    /**
     * @return array
     */
    private static function getStreams()
    {
        return self::responseFormatter(
            Stream::find()->each(),
            ['id', 'name']
        );
    }

    /**
     * @return array
     */
    private static function getSources()
    {
        return self::responseFormatter(
            Source::find()->each(),
            ['id', 'name']
        );
    }

    /**
     * @return array
     */
    private static function getLandings()
    {
        return self::responseFormatter(
            Landing::find()->each(),
            ['id', 'name']
        );
    }

    /**
     * @return array
     */
    private static function getPlatforms()
    {
        return self::responseFormatter(
            Platform::find()->each(),
            ['id', 'name']
        );
    }

    /**
     * @return array
     */
    private static function responseFormatter($data, $keysFrom)
    {
        $result = [];
        foreach ($data as $i => $item) {
            $id = ArrayHelper::getValue($keysFrom, 0);
            $name = ArrayHelper::getValue($keysFrom, 1);
            if (!is_object($item) && !is_array($item)) {
                throw new Exception('Передан неправильный тип данных');
            }
            $result[$i]['id'] = is_object($item) ? $item->$id : $item[$id];
            $result[$i]['name'] = is_object($item) ? $item->$name : $item[$name];
            $result[$i]['name'] = '#' . $result[$i]['id'] . ' - ' . $result[$i]['name'];
        }
        return $result;
    }
}