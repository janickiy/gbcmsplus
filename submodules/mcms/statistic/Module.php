<?php
namespace mcms\statistic;

use mcms\statistic\components\AbstractStatistic;
use mcms\statistic\models\Complain;
use mcms\statistic\models\mysql\Analytics;
use mcms\statistic\models\mysql\AnalyticsLtv;
use mcms\statistic\models\mysql\AnalyticsByDate;
use mcms\statistic\models\mysql\Banners;
use mcms\statistic\models\mysql\DetailStatistic;
use mcms\statistic\models\mysql\DetailStatisticComplains;
use mcms\statistic\models\mysql\MainAdminStatistic;
use mcms\statistic\models\mysql\PartnerReferrals;
use mcms\statistic\models\mysql\Referrals;
use mcms\statistic\models\ResellerProfitStatistics;
use Yii;
use yii\console\Application as ConsoleApplication;

/**
 * Class Module
 * @package mcms\statistic
 */
class Module extends \mcms\common\module\Module
{
  const SETTINGS_EXPORT_LIMIT = 'export_limit';
  const SETTINGS_EXPORT_POSTBACK_LIMIT = 'export_postback_limit';
  const SETTINGS_PARTNERS_EXPORT_POSTBACK_LIMIT = 'partner_export_postback_limit';
  const SETTINGS_PREDICT_SAMPLE_DAYS = 'predict_sample_days';
  const SETTINGS_POSTBACK_MAX_ATTEMPTS = 'postback_max_attempts';
  const SETTINGS_POSTBACK_MAX_DAYS = 'postback_max_day';
  const SETTINGS_POSTBACK_TRANSFER_PHONE = 'postback_transfer_phone';
  const SETTINGS_POSTBACK_HASH_PHONE = 'postback_hash_phone';
  const SETTINGS_POSTBACK_HASH_SALT = 'postback_hash_salt';
  const SETTINGS_BUYOUT_MINUTES = 'buyout_minutes';
  const SETTINGS_UNIQUE_BUYOUT_HOURS = 'unique_buyout_hours';
  const SETTINGS_DUPLICATE_POSTBACK= 'postback_is_campaign_with_preland';
  const SETTINGS_DUPLICATE_POSTBACK_URL = 'postback_campaign_with_preland_url';
  const SETTINGS_ENABLE_LABEL_STAT = 'enable_label_stat';
  const SETTINGS_ENABLE_RATIO_BY_UNIQUES = 'enable_ratio_by_uniques';
  const SETTINGS_AUTO_SUBMIT = 'auto_submit';
  const SETTINGS_RETURN_SUBSCRIPTION_AFTER_COMPLAINT = 'return_subscription_after_complaint';
  const STATISTIC_MAIN_HOUR = 'hour';
  const STATISTIC_MAIN_DATE = 'date';
  const STATISTIC_DETAIL_SUBSCRIPTIONS = 'subscriptions';
  const STATISTIC_DETAIL_IK = 'ik';
  const STATISTIC_DETAIL_SELLS = 'sells';
  const SETTINGS_CPA_DIFF_CALC_DAYS = 'cpa_diff_calc_days';
  const SETTINGS_PARTNER_VIEW_TEXT_COMPLAIN = 'partner_view_text_complain';
  const SETTINGS_PARTNER_VIEW_CALL_COMPLAIN = 'partner_view_call_complain';
  const SETTINGS_PARTNER_VIEW_AUTO24_COMPLAIN = 'partner_view_auto24_complain';
  const SETTINGS_PARTNER_VIEW_AUTO_MOMENT_COMPLAIN = 'partner_view_auto_moment_complain';
  const SETTINGS_PARTNER_VIEW_AUTO_DUPLICATE_COMPLAIN = 'partner_view_auto_duplicate_complain';
  const SETTINGS_PARTNER_VIEW_CALL_MNO_COMPLAIN = 'partner_view_call_mno_complain';
  const SETTINGS_CPR_CALC_THROUGTH_PRICE = 'settings.cpr_calc_through_price';
  const SETTINGS_ALLOW_BUYOUT_WITH_OFFS = 'settings.buyout.is_allow_buyout_with_offs';
  const SETTINGS_COUNT_DAYS_ALIVE_SUBS_CALC = 'count_days_alive_subs_calc';
  const SETTING_CALC_AVERCPA_ALL_SUBS = 'settings.calc_avercpa_all_subs';
  const SETTINGS_BUYOUT_AFTER_1ST_REBILL_ONLY = 'settings.buyout_after_1st_rebill_only';

  const PERMISSION_CAN_VIEW_FULL_TIME_STATISTIC = 'StatisticViewFullTimeStatistic';
  const PERMISSION_CAN_EXPORT_NEW_STATISTIC = 'StatisticNewExport';
  const PERMISSION_CAN_EXPORT_DETAIL_STATISTIC = 'StatisticDetailExport';

  /** @var string Разрешение на изменение количества знаков после запятой */
  const VIEW_COLUMNS_DECIMALS = 'StatisticViewColumnsDecimals';
  /** @var string Разрешение, проверяющее на роль менеджера */
  const DETAIL_IK_MANAGER = 'StatisticDetailIkManager';

  public $controllerNamespace = 'mcms\statistic\controllers';

  /* statistic class */
  public $statistic;

  public function init()
  {

    parent::init();

    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\statistic\commands';
    }

  }

  /**
   * @return int|null
   */
  public function getExportLimit()
  {
    return $this->settings->getValueByKey(self::SETTINGS_EXPORT_LIMIT);
  }

  /**
   * @return int|null
   */
  public function getExportPostbackLimit()
  {
    return $this->settings->getValueByKey(self::SETTINGS_EXPORT_POSTBACK_LIMIT);
  }

  /**
   * @return int|null
   */
  public function getPartnersExportPostbackLimit()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PARTNERS_EXPORT_POSTBACK_LIMIT);
  }

  /**
   * Считать Aver. CPA относительно всех подписок (Sold+Rejected)?
   * @return bool
   */
  public function getCalcAverCpaAllSubs()
  {
    return (bool)$this->settings->getValueByKey(self::SETTING_CALC_AVERCPA_ALL_SUBS);
  }

  /**
   * @param array $requestData
   * @param string $type
   * @return AbstractStatistic
   */
  public function getStatisticModel(array $requestData = [], $type = 'default')
  {
    switch ($type) {
      case 'detail':
        return new DetailStatistic(['requestData' => $requestData]);
        break;
      case 'tb':
        return new \mcms\statistic\models\mysql\TBStatistic(['requestData' => $requestData]);
        break;
      case Analytics::STATISTIC_NAME:
        return new \mcms\statistic\models\mysql\Analytics(['requestData' => $requestData]);
        break;
      case AnalyticsByDate::STATISTIC_NAME:
        return new \mcms\statistic\models\mysql\AnalyticsByDate(['requestData' => $requestData]);
        break;
      case AnalyticsLtv::STATISTIC_NAME:
        return new \mcms\statistic\models\mysql\AnalyticsLtv(['requestData' => $requestData]);
        break;
      case Banners::STATISTIC_NAME:
        return new \mcms\statistic\models\mysql\Banners(['requestData' => $requestData]);
        break;
      case Referrals::STATISTIC_NAME:
        return new \mcms\statistic\models\mysql\Referrals(['requestData' => $requestData]);
        break;
      case PartnerReferrals::STATISTIC_NAME:
        return new \mcms\statistic\models\mysql\PartnerReferrals(['requestData' => $requestData]);
        break;
      case DetailStatisticComplains::STATISTIC_NAME:
        return new DetailStatisticComplains(['requestData' => $requestData]);
        break;
      case ResellerProfitStatistics::STATISTIC_NAME:
        return new ResellerProfitStatistics(['requestData' => $requestData]);
      case MainAdminStatistic::STATISTIC_NAME:
        return new MainAdminStatistic(['requestData' => $requestData]);
      default:
        return new \mcms\statistic\models\mysql\Statistic(['requestData' => $requestData]);
    }
  }

  /**
   * @return int
   */
  public function getSampleDaysCount()
  {
    return (int)$this->settings->getValueByKey(self::SETTINGS_PREDICT_SAMPLE_DAYS);
  }

  /**
   * @return int
   */
  public function getCpaDiffCalcDays()
  {
    return (int)$this->settings->getValueByKey(self::SETTINGS_CPA_DIFF_CALC_DAYS, 3);
  }

  /**
   * @return int
   */
  public function getUniqBuyoutHours()
  {
    return (int)$this->settings->getValueByKey(self::SETTINGS_UNIQUE_BUYOUT_HOURS, 24);
  }

  /**
   * Ратио по уникам включено
   * @return bool
   */
  public function isRatioByUniquesEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_ENABLE_RATIO_BY_UNIQUES, false);
  }

  /**
   * Автосабмит форм статистики
   * @return mixed|null
   */
  public function isAutoSubmitEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_AUTO_SUBMIT, true);
  }

  /**
   * Включен ли возврат подписки после получения жалобы
   * @return mixed|null
   */
  public function isReturnSubscriptionAfterComplaintEnabled()
  {
    return $this->settings->getValueByKey(self::SETTINGS_RETURN_SUBSCRIPTION_AFTER_COMPLAINT, true);
  }

  /**
   * Настройка показывать ли партнеру текстовые жалобы
   * @return bool
   */
  public function canPartnerViewComplainText()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PARTNER_VIEW_TEXT_COMPLAIN);
  }

  /**
   * Настройка показывать ли партнеру жалобы по звонку
   * @return bool
   */
  public function canPartnerViewComplainCall()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PARTNER_VIEW_CALL_COMPLAIN);
  }

  /**
   * Настройка показывать ли партнеру жалобу отписка за 24 часа
   * @return bool
   */
  public function canPartnerViewComplainAuto24()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PARTNER_VIEW_AUTO24_COMPLAIN);
  }

  /**
   * Настройка показывать ли партнеру жалобу отписка за 15 мин
   * @return bool
   */
  public function canPartnerViewComplainAutoMoment()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PARTNER_VIEW_AUTO_MOMENT_COMPLAIN);
  }

  /**
   * Настройка показывать ли партнеру жалобу дубликат подписки
   * @return bool
   */
  public function canPartnerViewComplainAutoDuplicate()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PARTNER_VIEW_AUTO_DUPLICATE_COMPLAIN);
  }

  /**
   * Настройка показывать ли партнеру жалобу звонка ОСС
   * @return bool
   */
  public function canPartnerViewComplainCallMno()
  {
    return $this->settings->getValueByKey(self::SETTINGS_PARTNER_VIEW_CALL_MNO_COMPLAIN);
  }

  /**
   * Показывать ли в стате ПП колонку жалоб
   * @return bool
   */
  public function partnerVisibleComplains()
  {
    return $this->canPartnerViewComplainText() ||
      $this->canPartnerViewComplainCall() ||
      $this->canPartnerViewComplainCallMno() ||
      $this->canPartnerViewComplainAuto24() ||
      $this->canPartnerViewComplainAutoMoment() ||
      $this->canPartnerViewComplainAutoDuplicate();
  }

  /**
   * Типы жалоб доступные для показа партнеру
   * @return array
   */
  public function getPartnerVisibleComplainsTypes()
  {
    return array_filter([
      $this->canPartnerViewComplainText() ? Complain::TYPE_TEXT : null,
      $this->canPartnerViewComplainCall() ? Complain::TYPE_CALL : null,
      $this->canPartnerViewComplainCallMno() ? Complain::TYPE_CALL_MNO : null,
      $this->canPartnerViewComplainAuto24() ? Complain::TYPE_AUTO_24 : null,
      $this->canPartnerViewComplainAutoMoment() ? Complain::TYPE_AUTO_MOMENT : null,
      $this->canPartnerViewComplainAutoDuplicate() ? Complain::TYPE_AUTO_DUPLICATE : null,
    ]);
  }

  /**
   * Включена ли настройка "Выкупать подписки с ребиллом, даже если есть отписка"
   * @return bool
   */
  public function isAllowBuyoutWithOffs()
  {
    return (bool) $this->settings->getValueByKey(self::SETTINGS_ALLOW_BUYOUT_WITH_OFFS);
  }

  /**
   * Включена ли настройка "Выкупать только после 1го ребилла"
   * @return bool
   */
  public function isBuyoutAfter1stRebillOnly()
  {
    return (bool) $this->settings->getValueByKey(self::SETTINGS_BUYOUT_AFTER_1ST_REBILL_ONLY);
  }

  /**
   * За какое кол-во прошлых дней считаем живые подписки
   * @return int
   */
  public function getCountDaysAliveSubsCalc()
  {
    return (int) $this->settings->getValueByKey(self::SETTINGS_COUNT_DAYS_ALIVE_SUBS_CALC);
  }

  /**
   * @return bool
   */
  public function canViewFullTimeStatistic()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_VIEW_FULL_TIME_STATISTIC);
  }

  /**
   * @return bool
   */
  public function canExportNewStatistic()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EXPORT_NEW_STATISTIC);
  }

  /**
   * @return bool
   */
  public function canExportDetailStatistic()
  {
    return Yii::$app->user->can(self::PERMISSION_CAN_EXPORT_DETAIL_STATISTIC);
  }
}
