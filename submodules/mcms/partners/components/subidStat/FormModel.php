<?php

namespace mcms\partners\components\subidStat;

use Yii;
use yii\helpers\ArrayHelper;

/**
 */
class FormModel extends \mcms\statistic\components\mainStat\FormModel
{
  public $subid1;
  public $subid2;

  public $hitsFrom;
  public $hitsTo;
  public $uniquesFrom;
  public $uniquesTo;
  public $tbFrom;
  public $tbTo;
  public $acceptedFrom;
  public $acceptedTo;
  public $onsFrom;
  public $onsTo;
  public $offsFrom;
  public $offsTo;
  public $rebillsFrom;
  public $rebillsTo;
  public $webmasterSources;
  public $arbitraryLinks;

  protected $_arbitraryLinks;

  public function init()
  {
    parent::init();
    $this->currency = Yii::$app
      ->getModule('payments')
      ->api('getUserCurrency', [
        'userId' => Yii::$app->getUser()->id,
      ])
      ->getResult();
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['hitsFrom', 'hitsTo', 'uniquesFrom', 'uniquesTo', 'tbFrom', 'tbTo', 'acceptedFrom', 'acceptedTo', 'onsFrom', 'onsTo', 'offsFrom', 'offsTo', 'rebillsFrom', 'rebillsTo',
        'group', 'groups', 'dateFrom', 'dateTo', 'forceDatePeriod', 'landingPayTypes', 'providers', 'users', 'streams', 'sources', 'landings', 'landingCategories', 'platforms', 'isFake', 'currency', 'countries', 'operators', 'revshareOrCpa', 'subid1', 'subid2', 'arbitraryLinks', 'webmasterSources'], 'safe'],
      // Чистим группировки от некорректных значений
      [['groups'], 'filter', 'filter' => function ($value) {
        return array_intersect($value, Group::getAvailableGroups());
      }],
      ['revshareOrCpa', 'in', 'range' => [self::SELECT_ALL, self::SELECT_REVSHARE, self::SELECT_CPA], 'strict' => true],
    ];
  }

  /**
   * @return string|string[]
   */
  public function getGroup()
  {
    return ($this->groups === [Group::BY_SUBID1, Group::BY_SUBID2])
      ? Group::BY_SUBID12
      : $this->groups;
  }

  /**
   * @param $value
   */
  public function setGroup($value)
  {
    $this->groups = ($value === Group::BY_SUBID12)
      ? [Group::BY_SUBID1, Group::BY_SUBID2]
      : [$value];
  }

  /**
   * @param array $data
   * @param string $formName
   * @return bool
   * @throws \yii\base\InvalidConfigException
   */
  public function load($data, $formName = null)
  {
    $parentStatus = parent::load($data, $formName);

    if (empty($this->group)) {
      $this->group = Group::BY_SUBID12;
    }

    // Из ПП приходит в формате d.m.Y, преобразовываем тут
    if ($this->dateFrom) {
      $this->dateFrom = Yii::$app->formatter->asDate($this->dateFrom, 'php:Y-m-d');
    }
    if ($this->dateTo) {
      $this->dateTo = Yii::$app->formatter->asDate($this->dateTo, 'php:Y-m-d');
    }

    return $parentStatus;
  }

  /**
   * @return array
   */
  public function getGroupsList()
  {
    return [
      'subid1' => Yii::_t('statistic.subid1'),
      'subid2' => Yii::_t('statistic.subid2'),
      'subid12' =>
        Yii::_t('statistic.subid1') . ' + ' .
        Yii::_t('statistic.subid2'),
    ];
  }

  /**
   * @return bool
   */
  public function isShowRatio()
  {
    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');
    return $partnersModule->isShowRatio();
  }

  /**
   * @return bool
   */
  public function isRatioByUniques()
  {
    /** @var \mcms\statistic\Module $module */
    $module = Yii::$app->getModule('statistic');
    return $module->isRatioByUniquesEnabled();
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
}
