<?php

namespace admin\dashboard\models;

use Yii;
use yii\web\Cookie;
use yii\web\CookieCollection;

/**
 * Class CookiesFiltersHandler
 * @package admin\dashboard\models
 */
class CookiesFiltersHandler extends AbstractFiltersHandler
{
    /**
     * @var CookieCollection $requestCookies
     */
    private $requestCookies;
    /**
     * @var CookieCollection $requestCookies
     */
    private $responseCookies;

    public function init()
    {
        $this->requestCookies = Yii::$app->request->cookies;
        $this->responseCookies = Yii::$app->response->cookies;
    }

    /**
     * @inheritdoc
     */
    public function getValue($name, $default = null)
    {
        return $this->requestCookies->getValue($name, $default);
    }

    /**
     * @inheritdoc
     */
    public function add($name, $value, $expire = 0)
    {
        return $this->responseCookies->add(new Cookie([
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
        ]));
    }

    /**
     * @inheritdoc
     */
    public function remove($name)
    {
        $this->responseCookies->remove($name);
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            'countries' => $this->getValue(self::FILTER_COUNTRIES_NAME),
            'period' => $this->getValue(self::FILTER_PERIOD_NAME),
            'forecast' => $this->getValue(self::FILTER_FORECAST_NAME),
            'currency' => $this->getValue(self::FILTER_CURRENCY_NAME),
            'publisherType' => $this->getValue(self::FILTER_PUBLISHER_TYPE_NAME),
            'countryType' => $this->getValue(self::FILTER_COUNTRY_TYPE_NAME),
        ];
    }
}