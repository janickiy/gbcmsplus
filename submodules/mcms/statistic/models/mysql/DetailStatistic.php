<?php
namespace mcms\statistic\models\mysql;


use mcms\statistic\components\AbstractStatistic;
use Yii;
use yii\db\Query;


class DetailStatistic extends AbstractStatistic
{
  // не путать с поле group в AbstractStatistic, так как этот класс по сути не должен наследоваться от AbstractStatistic
  public $group;

  const GROUP_SUBSCRIPTIONS = 'subscriptions';
  const GROUP_IK = 'ik';
  const GROUP_SELLS = 'sells';
  const GROUP_HIT = 'hit';

  /** @var  AbstractStatistic */
  private $statisticModel;




  public function init()
  {
    parent::init();

    switch ($this->group) {
      case self::GROUP_IK:
        $this->statisticModel = new DetailStatisticIK(['requestData' => $this->requestData]);
        break;
      case self::GROUP_SELLS:
        $this->statisticModel = new DetailStatisticSells(['requestData' => $this->requestData]);
        break;
      case DetailStatisticComplains::GROUP_NAME:
        $this->statisticModel = new DetailStatisticComplains(['requestData' => $this->requestData]);
        break;
      case self::GROUP_SUBSCRIPTIONS:
      default:
        $this->statisticModel = new DetailStatisticSubscriptions(['requestData' => $this->requestData]);
        break;
    }
  }



  public function getModelGroup()
  {
    return $this->statisticModel->group;
  }

  public function getStatisticModel()
  {
    return $this->statisticModel;
  }

  function getFilterFields()
  {
    return $this->statisticModel->getFilterFields();
  }


  function gridColumnLabels()
  {
    return $this->statisticModel->gridColumnLabels();
  }

  function handleFilters(Query &$query)
  {
    $this->statisticModel->handleFilters($query);
  }

  function getStatisticGroup()
  {
    return $this->statisticModel->getStatisticGroup();
  }

  public function getGroups()
  {
    return array_filter([
      self::GROUP_SUBSCRIPTIONS => $this->canGroupBySubscriptions()
        ? Yii::_t('statistic.statistic.group_by_main_subscriptions')
        : false,
      self::GROUP_IK => $this->canGroupByIk()
        ? Yii::_t('statistic.statistic.group_by_main_ik')
        : false,
      self::GROUP_SELLS => $this->canGroupBySells()
        ? Yii::_t('statistic.statistic.group_by_main_sells')
        : false,
      DetailStatisticComplains::GROUP_NAME => $this->canGroupByComplains()
        ? Yii::_t('statistic.statistic.group_by_main_complains')
        : false,
      self::GROUP_HIT => $this->canViewHitDetails() ? Yii::_t('statistic.statistic.hit') : false,
    ]);
  }

  public function getGroupsBy()
  {
    return array_filter([
      self::GROUP_SUBSCRIPTIONS => Yii::_t('statistic.statistic.group_by_main_subscriptions'),
      self::GROUP_IK => Yii::_t('statistic.statistic.group_by_main_ik'),
      self::GROUP_SELLS => Yii::_t('statistic.statistic.group_by_main_sells'),
    ]);
  }

  public function canGroupBySubscriptions()
  {
    return $this->checkPermission('StatisticDetailSubscriptions');
  }

  public function canGroupByIk()
  {
    return $this->checkPermission('StatisticDetailIk');
  }

  public function canGroupBySells()
  {
    return $this->checkPermission('StatisticDetailSells');
  }

  public function canGroupByComplains()
  {
    return $this->canViewComplainsStatistic();
  }

  public function canViewHitDetails()
  {
    return $this->checkPermission('StatisticDetailHit');
  }

  function getAdminProfit(array $gridRow, $currency)
  {
    return $this->statisticModel->getAdminProfit($gridRow, $currency);
  }

  function getResellerProfit(array $gridRow, $currency)
  {
    return $this->statisticModel->getResellerProfit($gridRow, $currency);
  }

  function getPartnerProfit(array $gridRow, $currency)
  {
    return $this->statisticModel->getPartnerProfit($gridRow, $currency);
  }

  public function attributeLabels()
  {
    return [
      'start_date' => Yii::_t('statistic.statistic.start_date'),
      'end_date' => Yii::_t('statistic.statistic.end_date'),
      'streams' => Yii::_t('statistic.statistic.streams'),
      'sources' => Yii::_t('statistic.statistic.sources'),
      'landings' => Yii::_t('statistic.statistic.landings'),
      'countries' => Yii::_t('statistic.statistic.countries'),
      'operators' => Yii::_t('statistic.statistic.operators'),
      'providers' => Yii::_t('statistic.statistic.providers'),
      'platforms' => Yii::_t('statistic.statistic.platforms'),
      'users' => Yii::_t('statistic.statistic.users'),
      'landing_pay_types' => Yii::_t('statistic.statistic.landing_pay_types'),
      'date' => Yii::_t('statistic.statistic.date'),
    ];
  }
}