<?php

namespace mcms\payments\components\api;

use Yii;
use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\search\ReferralIncomeSearch;

class ReferralsGroupedBalance extends ApiResult
{

  private $_userId;
  private $_currency;
  private $_searchModel;
  private $_params;
  private $_partnerReferralSearch;

  public function init($params = array())
  {
    if (!($this->_userId = ArrayHelper::getValue($params, 'userId'))) {
      $this->addError('userId required');
    }
    if (!($this->_currency = ArrayHelper::getValue($params, 'currency'))) {
      $this->addError('currency required');
    }
    if (ArrayHelper::getValue($params, 'partnerSearch', null) === true) {
      $this->_partnerReferralSearch = true;
    }

    $this->_searchModel = new ReferralIncomeSearch([
      'user_id' => $this->_userId,
      'currency' => $this->_currency,
      'scenario' => $this->_partnerReferralSearch ? ReferralIncomeSearch::SCENARIO_PARTNER_REFERRAL_SEARCH : ReferralIncomeSearch::SCENARIO_DEFAULT
    ]);
    $this->_params = $params;

    $this->setResultTypeDataProvider();
    $this->prepareDataProvider($this->_searchModel, [
      'conditions' => $this->_params
    ]);

    return $this;
  }

  /**
   * Получение модели поиска
   * @return \mcms\payments\models\search\ReferralIncomeSearch
   */
  public function getSearchModel()
  {
    return $this->_searchModel;
  }

  /**
   * Получение дохода по заданным периодом для холда или основного счета
   * @param type $isHold
   * @return type
   */
  public function getTotalAmount($isHold)
  {
    $this->_searchModel->is_hold = (int) $isHold;
    return $this->_searchModel->getTotalAmount($this->_params);
  }

  /**
   * Получение дохода за последнюю неделю
   * @param type $isHold
   * @return type
   */
  public function getLastWeekAmount($isHold)
  {
    $this->_searchModel->date_from = Yii::$app->formatter->asDate('now -7 day', 'php:Y-m-d');
    $this->_searchModel->date_to = Yii::$app->formatter->asDate('now', 'php:Y-m-d');
    $this->_searchModel->is_hold = (int) $isHold;
    return $this->_searchModel->getTotalAmount($this->_params);
  }

}
