<?php

namespace mcms\holds\models;

use mcms\holds\components\RulePicker;
use mcms\holds\components\RuleUnholdPlan;
use mcms\promo\models\Country;
use Yii;
use yii\base\Model;

/**
 * Модель для работы с холдами партнера
 * Используется для отображения информации по холдам партнера
 */
class PartnerHold extends Model
{
  public $userId;
  public $countryId;
  public $date;
  public $lastUnholdDate;
  public $userCurrency;
  public $holdProfit;

  private $_ruleModel;
  private $_unholdPlanModel;
  private $_countryModel;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['userId'], 'required'],
      [['userId', 'countryId'], 'integer'],
      [['date', 'lastUnholdDate'], 'date', 'format' => 'php:Y-m-d'],
      [['userCurrency'], 'safe'],
      [['holdProfit'], 'double'],
    ];
  }

  /**
   * Описание правила расхолда
   * @return string
   */
  public function getRule()
  {
    if (!$this->getRuleModel()) {
      return null;
    }
    if (!$this->getUnholdPlan()->date_from) {
      Yii::error(sprintf(
        'Не найден план расхолда для правила #%d, партнера #%d, на дату холда %s',
        $this->getRuleModel()->id,
        $this->userId,
        $this->date
      ), __METHOD__);
    }
    return sprintf('#%d %s (%s - %s)',
      $this->getRuleModel()->program->id,
      $this->getRuleModel()->program->name,
      $this->getUnholdPlan()->date_from,
      $this->getUnholdPlan()->date_to
    );
  }

  /**
   * Объект модели правила
   * @return HoldProgramRule
   */
  private function getRuleModel()
  {
    if ($this->_ruleModel) return $this->_ruleModel;

    $this->_ruleModel = (new RulePicker([
      'userId' => $this->userId,
      'countryId' => $this->countryId,
    ]))->getRule();
    if (!$this->_ruleModel) {
      Yii::error(sprintf('Не найдено правило для партнера #%d и страны #%d', $this->userId, $this->countryId), __METHOD__);
    }

    return $this->_ruleModel;
  }

  /**
   * Дата расхолда
   * @return string
   */
  public function getUnholdDate()
  {
    if (!$this->getUnholdPlan()) return null;
    return $this->getUnholdPlan()->unhold_date;
  }

  /**
   * Модель плана расхолда
   * @return RuleUnholdPlan
   */
  private function getUnholdPlan()
  {
    if ($this->_unholdPlanModel) return $this->_unholdPlanModel;
    if (!$this->getRuleModel()) return null;
    return $this->getRuleModel()->getUnholdPlan($this->date);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'userId' => Yii::_t('holds.partner_hold.label-userId'),
      'countryId' => Yii::_t('holds.partner_hold.label-countryId'),
      'date' => Yii::_t('holds.partner_hold.label-date'),
      'lastUnholdDate' => Yii::_t('holds.partner_hold.label-lastUnholdDate'),
      'unholdDate' => Yii::_t('holds.partner_hold.label-unholdDate'),
      'userCurrency' => Yii::_t('holds.partner_hold.label-userCurrency'),
      'holdProfit' => Yii::_t('holds.partner_hold.label-holdProfit'),
      'rule' => Yii::_t('holds.partner_hold.label-rule'),
    ];
  }

  /**
   * @return Country
   */
  public function getCountry()
  {
    if ($this->_countryModel) return $this->_countryModel;
    $this->_countryModel = Country::findOne($this->countryId);
    return $this->_countryModel;
  }

  /**
   * Ссылка на страну
   * @return null|string
   */
  public function getCountryLink()
  {
    $country = $this->getCountry();
    return $country ? $country->getViewLink() : null;
  }
}
