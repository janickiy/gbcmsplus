<?php

namespace mcms\statistic\components\newStat\mysql\query;

use mcms\statistic\components\newStat\ColumnsGlossary;
use mcms\statistic\components\newStat\FormModel;
use yii\db\Query;

/**
 * Базовый класс для запросов в БД
 */
abstract class BaseQuery extends Query
{
  /**
   * @var bool Приджоинены ли менеджеры
   */
  protected $isJoinedManagers = false;

  /** @var int */
  public $templateId;

  private $_neededFields;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();
    $this->select($this->getSelectedFields());
  }

  /**
   * Поля, которые необходимо получить. TRICKY: Названию поля обязательно должен соответствовать ключ массива
   * @return array
   */
  abstract protected function getFieldList();

  /**
   * Поля, которые будут получены с учетом шаблона
   * Результат этого метода нужно передать в select()
   * @return array
   */
  protected function getSelectedFields()
  {
    return $this->getFieldList();
    /*
     * TODO: Пока достаем все поля. Возможно, в дальнейшем понадобится выбирать только нужные
    $result = [];
    foreach ($this->getFieldList() as $key => $value) {
      if (in_array($key, $this->getNeededFields())) {
        $result[$key] = $value;
      }
    }
    return $result;
    */
  }

  /**
   * Необходим ли данный Query в текущем шаблоне
   * @return bool
   */
  public function isQueryNeeded()
  {
    return true;
//    return (bool)$this->getNeededFields(); // временно отключили, ато столбцы постоянно меняются.
// При раскомментировании потом обязательно надо проверить словари что всё ок там прописано
  }

  /**
   * Получить ключи колонок, которые необходимо выбрать
   * @return array
   */
  protected function getNeededFields()
  {
    if ($this->_neededFields === null) {
      $this->_neededFields = array_intersect(ColumnsGlossary::getTemplateFields($this->templateId), array_keys($this->getFieldList()));
    }
    return $this->_neededFields;
  }

  /**
   * обработчик группировки по датам
   */
  abstract public function handleGroupByDates();
  /**
   * обработчик группировки по месяцам
   */
  abstract public function handleGroupByMonthNumbers();
  /**
   * обработчик группировки по неделям
   */
  abstract public function handleGroupByWeekNumbers();
  /**
   * обработчик группировки по часам
   */
  abstract public function handleGroupByHours();
  /**
   * обработчик группировки по лендам
   */
  abstract public function handleGroupByLandings();
  /**
   * обработчик группировки по ист вебмастера
   */
  abstract public function handleGroupByWebmasterSources();
  /**
   * обработчик группировки по ссылкам
   */
  abstract public function handleGroupByArbitraryLinks();
  /**
   * обработчик группировки по потокам
   */
  abstract public function handleGroupByStreams();
  /**
   * обработчик группировки по платформам
   */
  abstract public function handleGroupByPlatforms();
  /**
   * обработчик группировки по операторам
   */
  abstract public function handleGroupByOperators();
  /**
   * обработчик группировки по странам
   */
  abstract public function handleGroupByCountries();
  /**
   * обработчик группировки по провайдерам
   */
  abstract public function handleGroupByProviders();
  /**
   * обработчик группировки по пользователям
   */
  abstract public function handleGroupByUsers();
  /**
   * обработчик группировки по типам полаты на лендах
   */
  abstract public function handleGroupByLandingPayTypes();
  /**
   * обработчик группировки по менеджерам
   */
  abstract public function handleGroupByManagers();
  /**
   * обработчик группировки по источникам (линкам и источникам вебмастеров)
   */
  abstract public function handleGroupBySources();
  /**
   * обработчик группировки по категориям лендов
   */
  abstract public function handleGroupByLandingCategories();
  /**
   * обработчик группировки по категориям офферов
   */
  abstract public function handleGroupByOfferCategories();

  /**
   * обработчик фильтрации по диапазонам дат
   * @param string $dateFrom
   * @param string $dateTo
   * @param $ltvDateTo
   */
  abstract public function handleFilterByDates($dateFrom, $dateTo, $ltvDateTo);

  /**
   * обработчик фильтрации по часам
   * @param int $hour
   */
  abstract public function handleFilterByHour($hour);

  /**
   * обработчик фильтрации по менеджерам
   * @param int $managers
   */
  abstract public function handleFilterByManagers($managers);

  /**
   * обработчик фильтрации по типам полаты на лендах
   * @param int|int[] $types
   */
  abstract public function handleFilterByLandingPayTypes($types);

  /**
   * обработчик фильтрации по провайдерам
   * @param int|int[] $providers
   */
  abstract public function handleFilterByProviders($providers);

  /**
   * обработчик фильтрации по пользователям
   * @param int|int[] $users
   */
  abstract public function handleFilterByUsers($users);

  /**
   * обработчик фильтрации по потокам
   * @param int|int[] $streams
   */
  abstract public function handleFilterByStreams($streams);

  /**
   * обработчик фильтрации по источникам
   * @param int|int[] $sources
   */
  abstract public function handleFilterBySources($sources);

  /**
   * обработчик фильтрации по лендам
   * @param int|int[] $landings
   */
  abstract public function handleFilterByLandings($landings);

  /**
   * обработчик фильтрации по категориям лендов
   * @param int|int[] $landingCategories
   */
  abstract public function handleFilterByLandingCategories($landingCategories);

  /**
   * обработчик фильтрации по категориям офферов
   * @param int|int[] $landingCategories
   */
  abstract public function handleFilterByOfferCategories($offerCategories);

  /**
   * обработчик фильтрации по платформам
   * @param int|int[] $platforms
   */
  abstract public function handleFilterByPlatforms($platforms);

  /**
   * обработчик фильтрации по фейк/ревшар
   * @param int|int[] $fake
   */
  abstract public function handleFilterByFake($fake);

  /**
   * обработчик фильтрации по странам
   * @param int|int[] $countries
   */
  abstract public function handleFilterByCountries($countries);

  /**
   * обработчик фильтрации по операторам
   * @param int|int[] $operators
   */
  abstract public function handleFilterByOperators($operators);

  /**
   * Какие-то кастомные обработчики.
   * Например фильтруем по недоступным юзерам
   * @param FormModel $formModel
   */
  abstract public function handleInitial(FormModel $formModel);
}
