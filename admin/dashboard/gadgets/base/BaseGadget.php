<?php

namespace admin\dashboard\gadgets\base;

use admin\dashboard\common\base\BaseBlock;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Базовый класс для гаджетов
 */
abstract class BaseGadget extends BaseBlock
{
    public $useFilters = true;
    public $currencies = ['rub', 'usd', 'eur'];
    /** @var bool Использовать символ валюты? */
    protected $useCurrencySymbol = false;

    public function init()
    {
        parent::init();

        if (!$this->useFilters) {
            $this->period = $this->defaultPeriod;
            $this->countries = [];
        }
    }

    public static function getInstance($params = [])
    {
        return new static([
            'userId' => Yii::$app->user->id,
            'countries' => ArrayHelper::getValue($params, 'countries'),
            'period' => ArrayHelper::getValue($params, 'period'),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function runInternal()
    {
        $currency = $this->getUserCurrency();

        $values = $this->getValues();
        $showArrowUp = false;
        if (is_array($values) && count($values) > 0) {
            $lastValue = array_values(array_slice($values, -1))[0];
            $preLastValue = array_values(array_slice($values, -2, 1))[0];
            if ($lastValue > $preLastValue) $showArrowUp = true;
        }

        $view = ($this->useFilters) ? '@app/dashboard/gadgets/base/views/common_stat' : '@app/dashboard/gadgets/base/views/overview';
        $viewParams = [
            'title' => $this->getTitle(),
            'url' => $this->getUrl(),
            'id' => $this->id,
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'values' => $values,
            'currencySymbol' => $this->useCurrencySymbol ? $this->getCurrencySymbol($currency) : null,
            'updatable' => $this->useFilters,
            'className' => self::class,
            'showArrowUp' => $showArrowUp,
            'currentCurrency' => $currency,
            'otherCurrencies' => array_diff($this->currencies, array($currency)),
            'valuesByCyrrency' => $this->getValuesByCurrency(),
            'canFilterByCurrency' => $this->getPermissionChecker()->canFilterByCurrency(),
        ];

        return $this->render($view, $viewParams);
    }

    /**
     * значение за последний день в разрезе валют
     * @param bool $formatting
     * @return array
     */
    protected function getValuesByCurrency(bool $formatting = true)
    {
        return [];
    }

    /**
     * Значение
     * @return string
     */
    abstract protected function getValue();

    /**
     * Название гаджета как БД, для добавления в компонент подгрузки по ajax
     * @return string
     */
    abstract protected function getName();

    /**
     * История значений для графика
     * @return array
     */
    public function getValues()
    {
        return $this->getDataFromCache();
    }

    /**
     * @inheritdoc
     */
    public function getFrontData(): array
    {
        return array_values($this->getValues());
    }

    public function getUrl()
    {
        return false;
    }
}