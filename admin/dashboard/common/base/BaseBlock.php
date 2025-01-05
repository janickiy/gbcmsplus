<?php

namespace admin\dashboard\common\base;

use admin\components\DashboardApiLoader;
use admin\dashboard\common\DasboardRequest;
use admin\dashboard\models\AbstractFiltersHandler;
use admin\dashboard\models\CookiesFiltersHandler;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\payments\models\UserPaymentSetting;
use mcms\statistic\components\api\Dashboard;
use mcms\statistic\components\CheckPermissions;
use mcms\user\models\User;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Базовый класс для виджетов / гаджетов
 */
abstract class BaseBlock extends Widget
{
    const COLOR_MAGENTA = 'magenta';
    const COLOR_PINK = 'pink';
    const COLOR_PINK_DARK = 'pinkDark';
    const COLOR_YELLOW = 'yellow';
    const COLOR_ORANGE = 'orange';
    const COLOR_ORANGE_DARK = 'orangeDark';
    const COLOR_DARKEN = 'darken';
    const COLOR_PURPLE = 'purple';
    const COLOR_TEAL = 'teal';
    const COLOR_BLUE = 'blue';
    const COLOR_BLUE_LIGHT = 'blueLight';
    const COLOR_RED = 'red';
    const COLOR_RED_LIGHT = 'redLight';
    const COLOR_WHITE = 'white';
    const COLOR_GREEN_DARK = 'greenDark';
    const COLOR_GREEN = 'green';
    const COLOR_GREEN_LIGHT = 'greenLight';
    const COLOR_BLUE_DARK = 'blueDark';
    const COLOR_LIGHTEN = 'lighten';
    const COLOR_GREY_DARK = 'grayDark';

    const TYPE_GROSS = 'gross';
    const TYPE_NET = 'net';

    /** @const string Префикс HTML идентификатора блока */
    const ID_PREFIX = 'dashboard-block-id-';

    /**
     * @var int ID гаджета/виджета в БД
     */
    public $itemId = null;

    /**
     * @var int ID юзера
     */
    public $userId = null;

    /**
     * @var bool использовать ли фильтры
     */
    public $useFilters = true;
    /**
     * @var string фильтр по валюте
     */
    public $currency;
    /**
     * @var array фильтр по странам
     */
    public $countries;
    /**
     * @var array фильтр по партнерам
     */
    public $users;
    /**
     * @var int фильтр даты начала виджета (дней назад)
     */
    public $period;
    /**
     * @var string дефолтный период
     */
    public $defaultPeriod = '-6 days';
    /**
     * @var bool не подгружать статистику (пустые виджеты)
     */
    public $emptyData = false;
    /**
     * @var array валюты виджета
     */
    protected $_currencies;
    /**
     * @var AbstractFiltersHandler
     */
    private static $filtersHandler;

    /**
     * @var array Массив соответствия currency_code - currency_id
     */
    private static $_mainCurrencies = [];


    /** @var int Количество выведенных виджетов и гаджетов (будет удалено после внедрения выбора виджетов для отображения пользователем) */
    static protected $dashboardBlocks = 0;

    /** @var string HTML ID блока */
    protected $id;

    /** @var CheckPermissions */
    private $_checkPermissions;

    protected $formatter;

    /**
     * Название
     * @return string
     */
    abstract public function getTitle();

    /**
     * @return string
     */
    abstract protected function runInternal();


    /**
     * Разрешение для проверки доступа
     * @return string|null
     */
    abstract public function getPermission();

    /**
     * Ключ кеша для виджета
     * @return mixed
     */
    abstract public function getCacheKey();

    /**
     * Получение данных виджета
     * @return mixed
     */
    abstract protected function getData();

    /**
     * Получение данных виджета при подгрузке через ajax
     * @return mixed
     */
    abstract public function getFrontData();

    public function init()
    {
        parent::init();

        $this->formatter = Yii::$app->formatter;
        $this->formatter->decimalSeparator = '.';
        $this->formatter->thousandSeparator = '&nbsp;';

        static::$dashboardBlocks++;
        $uniqueId = static::$dashboardBlocks;

        $this->id = static::ID_PREFIX . $uniqueId;

        if ($this->useFilters) {
            $this->countries = $this->countries ?: self::getFiltersHandler($this->userId)->getValue(AbstractFiltersHandler::FILTER_COUNTRIES_NAME);

            $this->period = $this->period ?: self::getFiltersHandler($this->userId)->getValue(AbstractFiltersHandler::FILTER_PERIOD_NAME, $this->defaultPeriod);

            if (!$this->currency) {
                $this->currency = self::getFiltersHandler($this->userId)->getValue(
                    AbstractFiltersHandler::FILTER_CURRENCY_NAME,
                    Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => $this->userId])->getResult() ?: 'rub'
                );
            }
        }
        // tricky: Если есть управляемые партнеры, берем их.
        $this->userId && $this->users = User::getManageUsersByUserId($this->userId);
        if (!$this->getPermissionChecker()->canViewAdminProfit() && !$this->getPermissionChecker()->canViewResellerProfit()) {
            // tricky: Если есть управляемые партнеры, берем их. Иначе берем себя
            !$this->users && $this->users = [$this->userId];
        }

        self::$_mainCurrencies = Yii::$app->getModule('promo')
            ->api('mainCurrencies')
            ->setResultTypeMap()
            ->setMapParams(['code', 'id'])
            ->getResult();
    }

    /**
     * Получение значения за последний день в зависимости от типа дохода (валовый или чистый)
     * @param string $type
     * @param bool $formatting
     * @return array
     */
    protected function prepareValuesByCurrency($type, $formatting = true)
    {
        $data = $this->getApi($this->useFilters)->getTodayRevenues();
        $partnerCurrenciesProvider = PartnerCurrenciesProvider::getInstance();

        $result = [];
        foreach ($this->getCurrencies() as $currency) {
            $real = 0;
            $cpa = 0;
            $rs = 0;
            // tricky: Если видит профит (админский или ресовский), заполняем суммы
            if ($this->getPermissionChecker()->canViewAdminProfit() || $this->getPermissionChecker()->canViewResellerProfit()) {
                $value = $type == self::TYPE_GROSS ? 'resGross' : 'resNet';
                $real = $data[$value][$currency];
                $cpa = $data['resCPA'][$currency];
                $rs = $data['resRS'][$currency];
            }

            $course = $currency === $this->getUserCurrency()
                ? 1
                : $partnerCurrenciesProvider
                    ->getCurrencies()
                    ->getCurrency($currency)
                    ->{'getTo' . lcfirst($this->getUserCurrency())}();
            $converted = $real * $course;
            $convertedCPA = $cpa * $course;
            $convertedRS = $rs * $course;

            $result[$currency] = [
                'real' => $formatting ? $this->formatter->asDecimal($real, 2) : $real,
                'converted' => $formatting ? $this->formatter->asDecimal($converted, 2) : $converted,
                'course' => $formatting ? $this->formatter->asDecimal($course, 3) : $course,
                'convertedCPA' => $formatting ? $this->formatter->asDecimal($convertedCPA, 3) : $convertedCPA,
                'convertedRS' => $formatting ? $this->formatter->asDecimal($convertedRS, 3) : $convertedRS,
            ];
        }

        return $result;
    }

    /**
     * @param array $params
     * @return static
     */
    public static function getInstance($params = [])
    {
        return new static($params);
    }

    /**
     * @inheritdoc
     */
    public function getId($autoGenerate = true)
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    final public function run()
    {
        if (!$this->hasAccess()) return null;

        return $this->runInternal();
    }

    /**
     * Проверка доступа
     * @return bool
     */
    public function hasAccess()
    {
        return !$this->getPermission() || Yii::$app->authManager->checkAccess($this->userId, $this->getPermission());
    }

    /**
     * @return string
     */
    public function getCurrencySymbol($currency = null)
    {
        if ($currency === null) {
            $currency = $this->getUserCurrency();
        }
        switch ($currency) {
            case 'rub':
                return '₽';
                break;
            case 'usd':
                return '$';
                break;
            case 'eur':
                return '€';
                break;
            default:
                return '';
        }
    }

    /**
     * @param int $userId
     * @return AbstractFiltersHandler
     */
    public static function getFiltersHandler($userId)
    {
        if (!self::$filtersHandler) {
            $class = ArrayHelper::getValue(Yii::$app->params, 'dashboardFilterHandlerClass', null);
            if (!$class) {
                $class = CookiesFiltersHandler::class;
            }
            self::$filtersHandler = Yii::createObject(['class' => $class, 'userId' => $userId]);
        }
        return self::$filtersHandler;
    }

    /**
     * @return array
     */
    protected function getCurrencies()
    {
        if (!$this->_currencies) {

            if ($this->getPermissionChecker()->canFilterByCurrency()) {
                $this->_currencies = ['rub', 'usd', 'eur'];
            } else {
                $this->_currencies = [$this->getUserCurrency()];
            }
        }

        return $this->_currencies;
    }

    private static $_userPaymentSettings = [];

    /**
     * @param $userId
     * @return UserPaymentSetting
     */
    public function getUserSettings($userId)
    {
        if (!isset(self::$_userPaymentSettings[$userId])) {
            /** @var \mcms\payments\Module $paymentsModule */
            $paymentsModule = Yii::$app->getModule('payments');

            /** @var UserPaymentSetting $paymentSettings */
            self::$_userPaymentSettings[$userId] = $paymentsModule->api('userSettingsData', ['userId' => $this->userId])->getResult();
        }
        return self::$_userPaymentSettings[$userId];
    }

    /**
     * @return mixed
     */
    protected function getUserCurrency($userId = null)
    {
        if ($this->getPermissionChecker()->canFilterByCurrency()) {
            return Yii::$app->getModule('promo')->api('mainCurrenciesWidget')->getSelectedCurrency();
        }

        if ($userId === null) {
            $userId = $this->userId;
        }
        if (!$this->currency) {
            $this->currency = Yii::$app->getModule('payments')->api('getUserCurrency', [
                'userId' => $userId,
            ])->getResult();
        }

        return $this->currency;
    }

    /**
     * подготовка данных по времени
     * @param array $data
     * @return array
     */
    protected function prepareTimeLine($data = [])
    {
        $startTime = strtotime($this->period);
        $endTime = time();

        $result = [];
        for ($i = $startTime; $i <= $endTime; $i += 86400) {
            $date = date('Y-m-d', $i);
            if (empty($data[$date])) {
                $result[$date] = 0;
                continue;
            }
            $result[$date] = $data[$date];
        }

        return $result;
    }

    /**
     * @param bool $useCache
     * @return mixed
     */
    protected function getDataFromCache($useCache = false)
    {
        // пустая статистика для инициализации виджетов и гаджетов с фильтрами
        if ($this->emptyData && $this->useFilters) return [];

        if (!$useCache || !$data = Yii::$app->cache->get($this->getCacheKey())) {
            $data = $this->getData();

            Yii::$app->cache->set($this->getCacheKey(), $data, 60 * 10);
        }

        return $data;
    }

    /**
     * Оптимизировать ли запросы группировкой по валютам?
     * @return bool
     */
    public function canOptimizeByGroupCurrency()
    {
        return $this->getUserSettings($this->userId)->canUseMultipleCurrenciesBalance();
    }

    /**
     * Хеш для записи в статическое свойств
     * @param string $group
     * @param null $currency
     * @param array $excludeUserIds
     * @return string
     */
    protected function getHashKey($group = 'date', $currency = null, $excludeUserIds = [])
    {
        // для тех у кого 1 валюта всего всегда вытаскиваем данные только по этой валюте
        if (
            !$this->getPermissionChecker()->canFilterByCurrency()
        ) {
            $currency = $this->getUserSettings($this->userId)->getCurrency();
        }

        if ($this->canOptimizeByGroupCurrency()) {
            $cacheCurrency = $currency ? 'custom' : 'all';
        } else {
            $cacheCurrency = $currency ?: 'all';
        }

        $countries = is_array($this->countries) ? implode(',', $this->countries) : '';
        $excludeUserIds = is_array($excludeUserIds) ? implode(',', $excludeUserIds) : '';
        return md5(implode(':', [$this->userId, $group, $cacheCurrency, $countries, $this->period, $excludeUserIds]));
    }

    /**
     * @param DasboardRequest $request
     */
    public static function handleFilters(DasboardRequest $request)
    {
        $filterHandler = self::getFiltersHandler(Yii::$app->user->id);

        $countries = $request->getFilter('countries');
        $filterHandler->add(AbstractFiltersHandler::FILTER_COUNTRIES_NAME, $countries, time() + AbstractFiltersHandler::FILTER_DURATION);

        if (!$countries) {
            $filterHandler->remove(AbstractFiltersHandler::FILTER_COUNTRIES_NAME);
        }

        if ($period = $request->getFilter('period')) {
            $filterHandler->add(AbstractFiltersHandler::FILTER_PERIOD_NAME, $period, time() + AbstractFiltersHandler::FILTER_DURATION);
        }

        if (($forecast = $request->getFilter('forecast')) !== null) {
            $filterHandler->add(AbstractFiltersHandler::FILTER_FORECAST_NAME, Json::decode($forecast), time() + AbstractFiltersHandler::FILTER_DURATION);
        }

        if ($currency = $request->getFilter('currency')) {
            $filterHandler->add(AbstractFiltersHandler::FILTER_CURRENCY_NAME, $currency, time() + AbstractFiltersHandler::FILTER_DURATION);
        }

        $publishersWidget = $request->getWidget('top_publishers');
        $publisherType = ArrayHelper::getValue($publishersWidget, 'filter');
        if ($publisherType) {
            $filterHandler->add(AbstractFiltersHandler::FILTER_PUBLISHER_TYPE_NAME, $publisherType, time() + AbstractFiltersHandler::FILTER_DURATION);
        }

        $countriesWidget = $request->getWidget('top_countries');
        $countryType = ArrayHelper::getValue($countriesWidget, 'filter');
        if ($countryType) {
            $filterHandler->add(AbstractFiltersHandler::FILTER_COUNTRY_TYPE_NAME, $countryType, time() + AbstractFiltersHandler::FILTER_DURATION);
        }
    }

    /**
     * @param bool $useFilters
     * @return Dashboard
     */
    protected function getApi($useFilters = true)
    {
        return DashboardApiLoader::getApi(
            date('Y-m-d', strtotime($this->period)),
            null,
            $useFilters ? $this->countries : [],
            [],
            $this->users
        );
    }

    /**
     * @return CheckPermissions
     */
    protected function getPermissionChecker()
    {
        if (!isset($this->_checkPermissions)) {
            $this->_checkPermissions = new CheckPermissions([
                'viewerId' => $this->userId,
            ]);
        }

        return $this->_checkPermissions;
    }
}