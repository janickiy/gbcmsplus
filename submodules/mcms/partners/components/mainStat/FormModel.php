<?php

namespace mcms\partners\components\mainStat;

use mcms\partners\Module;
use mcms\statistic\components\mainStat\Group;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель для фильтрации основной статистики для партнера
 *
 * @property int $defaultDaysInterval
 * @property string $dateFrom Y-m-d
 * @property string $dateTo Y-m-d
 * @property bool $isRatioByUniques
 */
class FormModel extends \mcms\statistic\components\mainStat\FormModel
{

  public $webmasterSources;
  public $arbitraryLinks;
  public $isRatioByUniquesEnabled = false;
  private $_isRatioByUniques;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return ArrayHelper::merge(parent::rules(), [
      [['webmasterSources', 'arbitraryLinks', 'isRatioByUniques'], 'safe'],
      [['dateFrom'], 'maxNumberOfMonth'],
    ]);
  }

  /**
   * @param array $data
   * @param null $formName
   * @return bool
   */
  public function load($data, $formName = null)
  {
    $parentStatus = parent::load($data, $formName);

    if (empty($this->groups)) {
      $this->groups = [Group::BY_DATES];
    }

    // Из ПП приходит в формате d.m.Y, преобразовываем тут
    if ($this->dateFrom) {
      $this->dateFrom = Yii::$app->formatter->asDate($this->dateFrom, 'php:Y-m-d');
    }
    if ($this->dateTo) {
      $this->dateTo = Yii::$app->formatter->asDate($this->dateTo, 'php:Y-m-d');
    }

    if ($this->dateFrom === $this->dateTo && in_array(Group::BY_DATES, $this->groups, true)) {
      $this->groups = [Group::BY_HOURS];
    }

    return $parentStatus;
  }

  /**
   * @return array|int|int[]
   */
  public function getSources()
  {
    return ArrayHelper::merge(
      parent::getSources(),
      is_array($this->webmasterSources) ? $this->webmasterSources : [],
      is_array($this->arbitraryLinks) ? $this->arbitraryLinks : []
    );
  }

  /**
   * @return bool
   */
  public function getIsRatioByUniques()
  {
    if ($this->_isRatioByUniques === null && $this->isRatioByUniquesEnabled) {
      return true; // если включено по юникам - по дефолту отдаем true
    }

    return (bool) $this->_isRatioByUniques;
  }

  /**
   * @param $value
   */
  public function setIsRatioByUniques($value)
  {
    $this->_isRatioByUniques = (bool) $value;
  }

  /**
   * Валидация максимального диапазона для вывода статистики
   * @param $attribute
   */
  public function maxNumberOfMonth($attribute)
  {
    if (!in_array(Group::BY_MONTH_NUMBERS, $this->groups, true)) {
      return;
    }
    /** @var Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');
    $maxNumberOfMonth = $partnersModule->getMaxNumberOfMonth();
    $startDateLimit = date('Y-m-d', strtotime("-$maxNumberOfMonth month"));

    if ($this->dateFrom < $startDateLimit) {
      $this->addError($attribute, Yii::_t('statistic.statistic.incorrect_period'));
    }
  }


  /**
   * @param $value
   */
  public function setCurrency($value)
  {
    // заглушка чтобы не сеттили валюту, т.к. она всегда должна быть валютой кабинета партнера
  }

  /**
   * @return string
   */
  public function getCurrency()
  {
    if ($this->_currency === null) {
      /** @var \mcms\payments\Module $paymentsModule */
      $paymentsModule = Yii::$app
        ->getModule('payments');
      $this->_currency = $paymentsModule
        ->api('getUserCurrency', [
          'userId' => $this->getViewerId(),
        ])
        ->getResult();
    }
    return $this->_currency;
  }
}
