<?php

namespace mcms\statistic\components\newStat\mysql;

use yii\base\Model;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\models\Cr;
use Yii;
use yii\i18n\Formatter;

/**
 * Реализация для нашего текущего получения инфы из мускуля.
 * В _rowDataDto находятся исходные данные, полученные из БД. Доступны через метод getRowDataDto()
 * @property RowDataDto $rowDataDto
 * TODO форматтеры вынести в columns
 */
class Row extends Model
{
  const RUB = 'rub';
  const USD = 'usd';
  const EUR = 'eur';
  /**
   * @const array Список доступных валют
   */
  const CURRENCIES = [self::RUB, self::USD, self::EUR];
  /**
   * валюта в которой отображаются все профиты
   * @var string
   */
  private $_currency;

  /**
   * @var string группировка
   */
  private $_group;
  /**
   * @var Group[]
   * @see Row::getGroups()
   */
  public $groups;
  /**
   * @var Group
   * @see Row::getSecondGroup()
   */
  public $secondGroup;
  /**
   * @var RowDataDto
   */
  protected $_rowDataDto;
  /**
   * @var Formatter
   */
  public $formatter;

  public function init()
  {
    parent::init();
    $this->formatter = Yii::$app->formatter;
    $this->formatter->thousandSeparator = '&nbsp;';
  }

  /**
   * Возвращает объект с данными из БД
   * @return RowDataDto
   */
  public function getRowDataDto()
  {
    if (!$this->_rowDataDto) {
      $this->_rowDataDto = new RowDataDto();
    }
    return $this->_rowDataDto;
  }

  /**
   * По этому полю делается сортировка при некоторых группировках (по странам, операторам, провайдерам)
   * @return string
   */
  public function getSortValue()
  {
    return $this->getSingleGroup()->getFormattedPlainValue();
  }

  /**
   * @return Group[]
   */
  public function getGroups()
  {
    return $this->groups;
  }

  /**
   * @return Group
   */
  public function getSecondGroup()
  {
    return $this->secondGroup;
  }

  /**
   * Подставляем сразу из файла переводов
   * @param string $attribute
   * @return string
   */
  public function getAttributeLabel($attribute)
  {
    return Yii::_t('new_statistic_refactored.' . $attribute);
  }

  /**
   * @param string $currency
   */
  public function setCurrency($currency)
  {
    if (in_array($currency, self::CURRENCIES)) {
      $this->_currency = $currency;
    }
  }

  /**
   * @return string
   */
  public function getCurrency()
  {
    return $this->_currency;
  }

  /**
   * Установка группировки
   * TRICKY: Сделано через геттер/сеттер чтобы не использовать в подсчете футера
   * @param $value
   */
  public function setGroup($value)
  {
    $this->_group = $value;
  }

  /**
   * Получить группировку
   * @return string
   */
  public function getGroup()
  {
    return $this->_group;
  }

  /**
   * @return Group
   */
  public function getSingleGroup()
  {
    return reset($this->groups);
  }

  /////////////////////// Получение Данных ////////////////////////

  /**
   * Хиты
   * @return int
   */
  public function getHits()
  {
    return $this->getRowDataDto()->hits;
  }

  /**
   * @return string
   */
  public function getHitsPopover()
  {
    return sprintf(
      '<b>REV:</b>&nbsp;%s<br><b>CPA:</b>&nbsp;%s<br><b>OTP:</b>&nbsp;%s',
      $this->formatter->asInteger($this->getRevshareHits()),
      $this->formatter->asInteger($this->getToBuyoutHits()),
      $this->formatter->asInteger($this->getOtpHits())
    );
  }

  /**
   * ТБ
   * @return int
   */
  public function getTb()
  {
    return $this->getRowDataDto()->tb;
  }

  /**
   * Принятые хиты
   * @return int
   */
  public function getAccepted()
  {
    return $this->getHits() - $this->getTb();
  }

  /**
   * Принятые % от всех
   * @return int
   */
  public function getAcceptedRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getAccepted();
    $cr->fullCount = $this->getHits();
    return $cr->getRate();
  }

  /**
   * Уники
   * @return int
   */
  public function getUnique()
  {
    return $this->getRowDataDto()->unique;
  }

  /**
   * Уники % от всех
   * @return int
   */
  public function getUniqueRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getUnique();
    $cr->fullCount = $this->getHits();
    return $cr->getRate();
  }

  /**
   * Все подписки
   * @return int
   */
  public function getTotalSubscriptions()
  {
    return $this->getRevshareOns() + $this->getToBuyoutOns() + $this->getOtpOns();
  }

  /**
   * % всех подписок от принятого
   * @return int
   */
  public function getTotalSubscriptionsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getTotalSubscriptions();
    $cr->fullCount = $this->getAccepted();
    return $cr->getRate();
  }


  /**
   * Все отписки
   * @return int
   */
  public function getTotalOffs()
  {
    return $this->getRevshareOffs() + $this->getToBuyoutOffs();
  }

  /**
   * % отписок от подписок
   * @return int
   */
  public function getTotalOffsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getTotalOffs();
    $cr->fullCount = $this->getTotalSubscriptions();
    return $cr->getRate();
  }

  /**
   * @return int
   */
  public function getTotalCharges()
  {
    return $this->getRevshareRebills() + $this->getBuyoutRebills() + $this->getOtpOns();
  }

  /**
   * @return int
   */
  public function getTotalChargesRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getTotalCharges();
    $cr->fullCount = $this->getTotalOnsWithoutOffs();
    return $cr->getRate();
  }

  /**
   * @return int
   */
  public function getTotalChargesNotified()
  {
    return $this->getRevshareRebillsNotified() + $this->getOtpOns();
  }

  /**
   * @return int
   */
  public function getTotalChargesNotifiedRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getTotalChargesNotified();
    $cr->fullCount = $this->getTotalCharges();
    return $cr->getRate();
  }

  /**
   * Суммарных доход партнера
   * @return float
   */
  public function getTotalPartnerProfit()
  {
    return $this->getBuyoutPartnerProfit() + $this->getOtpPartnerProfit() + $this->getRevsharePartnerProfit();
  }

  /**
   * % процент от суммарного оборота
   * @return int
   */
  public function getTotalPartnerProfitRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getTotalPartnerProfit();
    $cr->fullCount = $this->getTotalResellerProfit();
    return $cr->getRate();
  }

  /**
   * @return float
   */
  public function getTotalResellerProfit()
  {
    return $this->getOtpResellerProfit() + $this->getBuyoutResellerProfit() + $this->getRevshareResellerProfit();
  }

  /**
   * Общий чистый доход реселлера
   * @return float
   */
  public function getTotalResellerNetProfit()
  {
    return $this->getOtpResellerNetProfit() + $this->getBuyoutResellerNetProfit() + $this->getRevshareResellerNetProfit();
  }


  /**
   * Процент общего чистого профита реселлера от общего оборота
   * @return float
   */
  public function getTotalResellerNetProfitTotalRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getTotalResellerNetProfit();
    $cr->fullCount = $this->getTotalResellerProfit();
    return $cr->getRate();
  }

  /**
   * @return int
   */
  public function getOns()
  {
    return $this->getRevshareOns() + $this->getBuyoutOns() + $this->getOtpOns();
  }

  /**
   * @return int
   */
  public function getRgkComplaints()
  {
    return $this->getToBuyoutRgkComplaints() + $this->getRevshareRgkComplaints() + $this->getOtpRgkComplaints();
  }

  /**
   * @return int
   */
  public function getCallMnoComplaints()
  {
    return $this->getToBuyoutCallMnoComplaints() + $this->getRevshareCallMnoComplaints() + $this->getOtpCallMnoComplaints();
  }

  /**
   * @return float
   */
  public function getRgkComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRgkComplaints();
    $cr->fullCount = $this->getOns();
    return $cr->getRate();
  }

  /**
   * @return float
   */
  public function getCallMnoComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getCallMnoComplaints();
    $cr->fullCount = $this->getOns();
    return $cr->getRate();
  }


  /**
   * Возвраты RGK
   * @return int
   */
  public function getRgkRefunds()
  {
    return $this->getRevshareRgkRefunds() + $this->getToBuyoutRgkRefunds() + $this->getOtpRgkRefunds();
  }

  /**
   * Возвраты MNO
   * @return int
   */
  public function getMnoRefunds()
  {
    return $this->getRevshareMnoRefunds() + $this->getToBuyoutMnoRefunds() + $this->getOtpMnoRefunds();
  }

  /**
   * Все возвраты
   * @return int
   */
  public function getRefunds()
  {
    return $this->getMnoRefunds() + $this->getRgkRefunds();
  }

  /**
   * Ревшар возвраты RGK
   * @return int
   */
  public function getRevshareRgkRefunds()
  {
    return $this->getRowDataDto()->revshareRgkRefunds;
  }

  /**
   * Ревшар возвраты MNO
   * @return int
   */
  public function getRevshareMnoRefunds()
  {
    return $this->getRowDataDto()->revshareMnoRefunds;
  }

  /**
   * Все ревшар возвраты
   * @return int
   */
  public function getRevshareRefunds()
  {
    return $this->getRevshareMnoRefunds() + $this->getRevshareRgkRefunds();
  }

  /**
   * Cpa возвраты RGK
   * @return int
   */
  public function getToBuyoutRgkRefunds()
  {
    return $this->getRowDataDto()->toBuyoutRgkRefunds;
  }

  /**
   * Cpa возвраты MNO
   * @return int
   */
  public function getToBuyoutMnoRefunds()
  {
    return $this->getRowDataDto()->toBuyoutMnoRefunds;
  }

  /**
   * Все "на выкуп" возвраты
   * @return int
   */
  public function getToBuyoutRefunds()
  {
    return $this->getToBuyoutMnoRefunds() + $this->getToBuyoutRgkRefunds();
  }

  /**
   * OTP возвраты RGK
   * @return int
   */
  public function getOtpRgkRefunds()
  {
    return $this->getRowDataDto()->otpRgkRefunds;
  }

  /**
   * OTP возвраты MNO
   * @return int
   */
  public function getOtpMnoRefunds()
  {
    return $this->getRowDataDto()->otpMnoRefunds;
  }

  /**
   * Все OTP возвраты
   * @return int
   */
  public function getOtpRefunds()
  {
    return $this->getOtpMnoRefunds() + $this->getOtpRgkRefunds();
  }

  /**
   * Возвраты RGK
   * @return int
   */
  public function getRgkRefundSum()
  {
    return $this->getRevshareRgkRefundSum() + $this->getToBuyoutRgkRefundSum() + $this->getOtpRgkRefundSum();
  }

  /**
   * Возвраты MNO
   * @return int
   */
  public function getMnoRefundSum()
  {
    return $this->getRevshareMnoRefundSum() + $this->getToBuyoutMnoRefundSum() + $this->getOtpMnoRefundSum();
  }

  /**
   * Все возвраты
   * @return int
   */
  public function getRefundSum()
  {
    return $this->getMnoRefundSum() + $this->getRgkRefundSum();
  }

  /**
   * Ревшар возвраты RGK
   * @return int
   */
  public function getRevshareRgkRefundSum()
  {
    return $this->getRowDataDto()->{'revshareRgkRefundSum' . ucfirst($this->getCurrency())};
  }

  /**
   * Ревшар возвраты MNO
   * @return int
   */
  public function getRevshareMnoRefundSum()
  {
    return $this->getRowDataDto()->{'revshareMnoRefundSum' . ucfirst($this->getCurrency())};
  }

  /**
   * Все ревшар возвраты
   * @return int
   */
  public function getRevshareRefundSum()
  {
    return $this->getRevshareMnoRefundSum() + $this->getRevshareRgkRefundSum();
  }

  /**
   * Cpa возвраты RGK
   * @return int
   */
  public function getToBuyoutRgkRefundSum()
  {
    return $this->getRowDataDto()->{'toBuyoutRgkRefundSum' . ucfirst($this->getCurrency())};
  }

  /**
   * Cpa возвраты MNO
   * @return int
   */
  public function getToBuyoutMnoRefundSum()
  {
    return $this->getRowDataDto()->{'toBuyoutMnoRefundSum' . ucfirst($this->getCurrency())};
  }

  /**
   * Все "на выкуп" возвраты
   * @return int
   */
  public function getToBuyoutRefundSum()
  {
    return $this->getToBuyoutMnoRefundSum() + $this->getToBuyoutRgkRefundSum();
  }

  /**
   * OTP возвраты RGK
   * @return int
   */
  public function getOtpRgkRefundSum()
  {
    return $this->getRowDataDto()->{'otpRgkRefundSum' . ucfirst($this->getCurrency())};
  }

  /**
   * OTP возвраты MNO
   * @return int
   */
  public function getOtpMnoRefundSum()
  {
    return $this->getRowDataDto()->{'otpMnoRefundSum' . ucfirst($this->getCurrency())};
  }

  /**
   * Все OTP возвраты
   * @return int
   */
  public function getOtpRefundSum()
  {
    return $this->getOtpMnoRefundSum() + $this->getOtpRgkRefundSum();
  }

  /**
   * Ревшар хиты
   * @return int
   */
  public function getRevshareHits()
  {
    return $this->getRowDataDto()->revshareHits;
  }

  /**
   * Ревшар TB
   * @return int
   */
  public function getRevshareTb()
  {
    return $this->getRowDataDto()->revshareTb;
  }

  /**
   * Ревшар принятые
   * @return int
   */
  public function getRevshareAccepted()
  {
    return $this->getRevshareHits() - $this->getRevshareTb();
  }

  /**
   * Ревшар процент принятых
   * @return float
   */
  public function getRevshareAcceptedRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareAccepted();
    $cr->fullCount = $this->getRevshareHits();
    return $cr->getRate();
  }

  /**
   * Ревшар уникальные хиты
   * @return int
   */
  public function getRevshareUnique()
  {
    return $this->getRowDataDto()->revshareUnique;
  }

  /**
   * Ревшар процент уникальных
   * @return float
   */
  public function getRevshareUniqueRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareUnique();
    $cr->fullCount = $this->getRevshareHits();
    return $cr->getRate();
  }

  /**
   * Ревшар подписки
   * @return int
   */
  public function getRevshareOns()
  {
    return $this->getRowDataDto()->revshareOns;
  }

  /**
   * Ревшар отписки
   * @return int
   */
  public function getRevshareOffs()
  {
    return $this->rowDataDto->revshareOffs;
  }

  /**
   * % ревшар отписок от подписок
   * @return int
   */
  public function getRevshareOffsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareOffs();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * Ревшар CR
   * @return float
   */
  public function getRevshareCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareOns();
    $cr->fullCount = $this->getRevshareAccepted();
    return $cr->getRate();
  }

  /**
   * Ревшар ребиллы
   * @return int
   */
  public function getRevshareRebills()
  {
    return $this->getRevshareRebillsNotified() + $this->getRevshareRebillsCorrected();
  }

  /**
   * Ревшар ребиллы, показаные партнеру
   * @return int
   */
  public function getRevshareRebillsNotified()
  {
    return $this->getRowDataDto()->revshareRebills;
  }

  /**
   * Обертка для ревшар ребиллов, показанных партнеру для страницы total
   * @return int
   */
  public function getTotalRevshareRebillsNotified()
  {
    return $this->getRevshareRebillsNotified();
  }


  /**
   * Процент ревшар ребиллов показанных партнеру
   * @return float
   */
  public function getRevshareRebillsNotifiedRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareRebillsNotified();
    $cr->fullCount = $this->getRevshareRebills();
    return $cr->getRate();
  }

  /**
   * Скорректированные ревшар ребиллы
   * @return int
   */
  public function getRevshareRebillsCorrected()
  {
    return $this->getRowDataDto()->revshareRebillsCorrected;
  }

  /**
   * Ревшар ребиллы % от подписок
   * @return float
   */
  public function getRevshareRebillsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareRebills();
    $cr->fullCount = $this->getRevshareTotalOnsWithoutOffs();
    return $cr->getRate();
  }

  /**
   * Ревшар ребиллы24
   * @return int
   */
  public function getRevshareRebills24()
  {
    return $this->getRowDataDto()->revshareRebills24 + $this->getRowDataDto()->revshareRebills24Corrected;
  }

  /**
   * Ревшар ребиллы24 % от подписок
   * @return float
   */
  public function getRevshareRebills24Rate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareRebills24();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * @return int
   */
  public function getRevshareOffs24()
  {
    return $this->getRowDataDto()->revshareOffs24;
  }

  /**
   * Ревшар отписки24 % от подписок
   * @return float
   */
  public function getRevshareOffs24Rate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareOffs24();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * @return int
   */
  public function getRevshareActiveSubscribers()
  {
    return '';
  }

  /**
   * @return int
   */
  public function getRevshareSubscribers()
  {
    return '';
  }

  /**
   * @return int
   */
  public function getRevshareRebillsTotal()
  {
    return '';
  }

  /**
   * @return int
   */
  public function getRevshareUnsubscribers()
  {
    return '';
  }

  /**
   * Ревшар оборот реселлера
   * @return float
   */
  public function getRevshareResellerProfit()
  {
    return $this->getRowDataDto()->{'revshareResellerProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * Ревшар оборот реселлера (со скорректированных ребилов)
   * @return float
   */
  public function getRevshareResellerCorrectedProfit()
  {
    return $this->getRowDataDto()->{'revshareResellerProfitCorrected' . ucfirst($this->getCurrency())};
  }

  /**
   * Ревшар профит партнера
   * @return float
   */
  public function getRevsharePartnerProfit()
  {
    return $this->getRowDataDto()->{'revsharePartnerProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * Обертка ревешар профит для отображения в шаблоне тотал
   * @return float
   */
  public function getTotalRevsharePartnerProfit()
  {
    return $this->getRevsharePartnerProfit();
  }

  /**
   * Ревшар процент профита партнера
   * @return float
   */
  public function getRevsharePartnerProfitRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevsharePartnerProfit();
    $cr->fullCount = $this->getRevshareResellerProfit();
    return $cr->getRate();
  }

  /**
   * @return float
   */
  public function getRevshareFixComissions()
  {
    return '';
  }

  /**
   * @return float
   */
  public function getRevshareAdjustment()
  {
    return $this->getRevshareResellerCorrectedProfit();
  }

  /**
   * @return float
   */
  public function getRevshareAdjustmentRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareResellerCorrectedProfit();
    $cr->fullCount = $this->getRevshareResellerProfit();
    return $cr->getRate();
  }

  /**
   * Ревшар чистый профит реселлера
   * @return float
   */
  public function getRevshareResellerNetProfit()
  {
    return $this->getRevshareResellerProfit() - $this->getRevsharePartnerProfit();
  }

  /**
   * Обертка для шаблона Тотал
   * @return float
   */
  public function getTotalRevshareResellerNetProfit()
  {
    return $this->getRevshareResellerNetProfit();
  }

  /**
   * Revshare Total Margin
   * @return float
   */
  public function getRevshareTotalMargin()
  {
    return $this->getRevshareResellerNetProfit() + $this->getRevshareResellerCorrectedProfit();
  }

  /**
   * Ревшар процент чистого профита реселлера
   * @return float
   */
  public function getRevshareResellerNetProfitRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareResellerNetProfit();
    $cr->fullCount = $this->getRevshareResellerProfit();
    return $cr->getRate();
  }

  /**
   * Ревшар процент чистого профита реселлера от общего оборота
   * @return float
   */
  public function getRevshareResellerNetProfitTotalRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareResellerNetProfit();
    $cr->fullCount = $this->getTotalResellerProfit();
    return $cr->getRate();
  }

  /**
   * Ревшар жалобы RGK
   * @return int
   */
  public function getRevshareRgkComplaints()
  {
    return $this->getRowDataDto()->revshareTextComplaints + $this->getRowDataDto()->revshareCallComplaints;
  }

  /**
   * Ревшар процент жалоб RGK от ревшар подписок
   * @return float
   */
  public function getRevshareRgkComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareRgkComplaints();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * Ревшар жалобы звонки ОСС
   * @return int
   */
  public function getRevshareCallMnoComplaints()
  {
    return $this->getRowDataDto()->revshareCallMnoComplaints;
  }

  /**
   * Ревшар процент жалоб звонков ОСС от ревшар подписок
   * @return float
   */
  public function getRevshareCallMnoComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareCallMnoComplaints();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }


  /**
   * Хиты по подпискам на выкуп
   * @return int
   */
  public function getToBuyoutHits()
  {
    return $this->getRowDataDto()->cpaHits - $this->getOtpHits();
  }

  /**
   * Принятые хиты по подпискам на выкуп
   * @return int
   */
  public function getToBuyoutAccepted()
  {
    return $this->getRowDataDto()->cpaHits - $this->getRowDataDto()->cpaTb - $this->getOtpAccepted();
  }

  /**
   * Выкуп процент принятых
   * @return float
   */
  public function getToBuyoutAcceptedRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutAccepted();
    $cr->fullCount = $this->getToBuyoutHits();
    return $cr->getRate();
  }

  /**
   * Выкуп уникальные хиты
   * @return int
   */
  public function getToBuyoutUnique()
  {
    return $this->getRowDataDto()->cpaUnique - $this->getOtpUnique();
  }

  /**
   * Выкуп процент уникальных
   * @return float
   */
  public function getToBuyoutUniqueRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutUnique();
    $cr->fullCount = $this->getToBuyoutHits();
    return $cr->getRate();
  }

  /**
   * Выкупленные подписки
   * @return int
   */
  public function getBuyoutOns()
  {
    return $this->getRowDataDto()->buyoutOns;
  }

  /**
   * Отписки по подпискам на выкуп
   * @return int
   */
  public function getToBuyoutOffs()
  {
    return $this->getRejectedOffs() + $this->getBuyoutOffs();
  }

  /**
   * % отписок по подпискам на выкуп от подписок
   * @return int
   */
  public function getToBuyoutOffsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutOffs();
    $cr->fullCount = $this->getToBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Отписки по подпискам на выкуп
   * @return int
   */
  public function getRejectedOffs()
  {
    return $this->rowDataDto->rejectedOffs;
  }

  /**
   * Отписки по подпискам на выкуп
   * @return int
   */
  public function getBuyoutOffs()
  {
    return $this->rowDataDto->buyoutOffs;
  }

  /**
   * Подписки на выкуп
   * @return int
   */
  public function getToBuyoutOns()
  {
    return $this->getRowDataDto()->toBuyoutOns;
  }

  /**
   * Отписки по выкупленным подпискам за 24 часа
   * @return int
   */
  public function getBuyoutOffs24()
  {
    return $this->getRowDataDto()->buyoutOffs24;
  }

  /**
   * Отписки по выкупленным подпискам за 24 часа
   * @return int
   */
  public function getBuyoutOffs24Rate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutOffs24();
    $cr->fullCount = $this->getBuyoutOns();
    return $cr->getRate();
  }

  /**
   * CR подписок на выкуп
   * @return float
   */
  public function getToBuyoutCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutOns();
    $cr->fullCount = $this->getToBuyoutAccepted();
    return $cr->getRate();
  }

  /**
   * Видимые выкупленные подписки
   * @return int
   */
  public function getBuyoutVisibleOns()
  {
    return $this->getRowDataDto()->buyoutVisibleOns;
  }

  /**
   * Обертка для видимых выкупленных подписок для страницы total
   * @return int
   */
  public function getTotalBuyoutVisibleOns()
  {
    return $this->getBuyoutVisibleOns();
  }

  /**
   * Видимые выкупы CR
   * @return float
   */
  public function getBuyoutVisibleCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutVisibleOns();
    $cr->fullCount = $this->getToBuyoutAccepted();
    return $cr->getRate();
  }

  /**
   * Выкуп профит партнера
   * @return float
   */
  public function getBuyoutPartnerProfit()
  {
    return $this->getRowDataDto()->{'buyoutPartnerProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * Обертка для вывода в шаблоне тотал
   * @return float
   */
  public function getTotalBuyoutPartnerProfit()
  {
    return $this->getBuyoutPartnerProfit();
  }

  /**
   * Total Margin CPA
   * @return float
   */
  public function getBuyoutMargin()
  {
    return $this->getBuyoutResellerProfit() - $this->getBuyoutPartnerProfit();
  }

  /**
   * Total Margin CPA Rate
   * @return float
   */
  public function getBuyoutMarginRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutMargin();
    $cr->fullCount = $this->getBuyoutResellerProfit();
    return $cr->getRate();
  }

  /**
   * Средний профит партнера за выкуп
   * @return float
   */
  public function getBuyoutAvgPartnerProfit()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutPartnerProfit();
    $cr->fullCount = $this->getBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Средний профит партнера за выкуп
   * @return float
   */
  public function getVisibleBuyoutAvgPartnerProfit()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutPartnerProfit();
    $cr->fullCount = $this->getBuyoutVisibleOns();
    return $cr->getRate();
  }

  /**
   * Ребиллы по выкупам
   * @return integer
   */
  public function getBuyoutRebills()
  {
    return $this->getRowDataDto()->buyoutRebills;
  }

  /**
   * Обертка для шаблона Тотал
   * @return integer
   */
  public function getTotalBuyoutRebills()
  {
    return $this->getBuyoutRebills();
  }

  /**
   * Процент проребилленных выкупленных подписок от общего кол-ва
   * @return integer
   */
  public function getBuyoutRebillsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutRebills();
    $cr->fullCount = $this->getToBuyoutTotalOnsWithoutOffs();
    return $cr->getRate();
  }

  /**
   * Первичные списания по
   * @return int
   */
  public function getBuyoutRebills24()
  {
    return $this->getRowDataDto()->buyoutRebills24;
  }

  /**
   * Процент первичных проребилленных  выкупленных пподписок от общего кол-ва
   * @return integer
   */
  public function getBuyoutRebills24Rate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutRebills24();
    $cr->fullCount = $this->getBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Cколько профита реселлеру принесло 1000 кликов
   * @return float
   */
  public function getBuyoutRpm()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutResellerProfit();
    $cr->fullCount = $this->getToBuyoutAccepted();
    return $cr->getRate() * 1000;
  }

  /**
   * eCPM в партнерке (сколько профита принесло 1000 кликов)
   * @return float
   */
  public function getBuyoutNotifyRpm()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutPartnerProfit();
    $cr->fullCount = $this->getToBuyoutAccepted();
    return $cr->getRate() * 1000;
  }

  /**
   * @return float
   */
  public function getBuyoutActiveSubscribers()
  {
    return '';
  }

  /**
   * ROI=(Доход от вложений - размер вложений) / Размер вложений *100%
   * В нашем случае доход от вложений - это LTV профит. Размер вложений - это сколько выплатили партнеру
   * @return float
   */
  public function getBuyoutRoi()
  {
    $cr = new Cr();
    $expense = $this->getBuyoutPartnerProfit();
    $cr->convertionsCount = $this->getToBuyoutResellerLtvProfit() - $expense;
    $cr->fullCount = $expense;
    return $cr->getRate();
  }

  /**
   * @return float
   */
  public function getBuyoutUnsubscribers()
  {
    return '';
  }

  /**
   * @return float
   */
  public function getBuyoutChargesTotal()
  {
    return '';
  }

  /**
   * Оборот с выкупленных ребиллов
   * @return float
   */
  public function getBuyoutResellerProfit()
  {
    return $this->getRowDataDto()->{'buyoutResellerProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * Чистый доход с выкупленных ребиллов
   * @return float
   */
  public function getBuyoutResellerNetProfit()
  {
    return $this->getBuyoutResellerProfit() - $this->getBuyoutPartnerProfit();
  }

  /**
   * Ревшар процент чистого профита реселлера с выкупленных ребиллов от общего оборота
   * @return float
   */
  public function getBuyoutResellerNetProfitTotalRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getBuyoutResellerNetProfit();
    $cr->fullCount = $this->getTotalResellerProfit();
    return $cr->getRate();
  }

  /**
   * Cpa жалобы RGK
   * @return int
   */
  public function getToBuyoutRgkComplaints()
  {
    return $this->getRowDataDto()->toBuyoutTextComplaints + $this->getRowDataDto()->toBuyoutCallComplaints;
  }

  /**
   * Cpa процент жалоб RGK от ревшар подписок
   * @return float
   */
  public function getToBuyoutRgkComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutRgkComplaints();
    $cr->fullCount = $this->getBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Cpa жалобы звонки ОСС
   * @return int
   */
  public function getToBuyoutCallMnoComplaints()
  {
    return $this->getRowDataDto()->toBuyoutCallMnoComplaints;
  }

  /**
   * Cpa процент жалоб звонков ОСС от ревшар подписок
   * @return float
   */
  public function getToBuyoutCallMnoComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutCallMnoComplaints();
    $cr->fullCount = $this->getBuyoutOns();
    return $cr->getRate();
  }


  /**
   * Otp хиты
   * @return int
   */
  public function getOtpHits()
  {
    return $this->getRowDataDto()->otpHits;
  }

  /**
   * Otp принятые хиты
   * @return int
   */
  public function getOtpAccepted()
  {
    return $this->getOtpHits() - $this->getRowDataDto()->otpTb;
  }

  /**
   * Otp процент принятых
   * @return float
   */
  public function getOtpAcceptedRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpAccepted();
    $cr->fullCount = $this->getOtpHits();
    return $cr->getRate();
  }

  /**
   * Otp уники
   * @return int
   */
  public function getOtpUnique()
  {
    return $this->getRowDataDto()->otpUnique;
  }

  /**
   * Otp процент уникальных
   * @return float
   */
  public function getOtpUniqueRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpUnique();
    $cr->fullCount = $this->getOtpHits();
    return $cr->getRate();
  }


  /**
   * OTP подписки
   * @return int
   */
  public function getOtpOns()
  {
    return $this->getRowDataDto()->otpOns;
  }

  /**
   * OTP CR
   * @return float
   */
  public function getOtpCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpOns();
    $cr->fullCount = $this->getOtpAccepted();
    return $cr->getRate();
  }

  /**
   * Видимые OTP подписки
   * @return int
   */
  public function getOtpVisibleOns()
  {
    return $this->getRowDataDto()->otpVisibleOns;
  }

  /**
   * Обертка для OTP подпискок для страницы тотал
   * @return int
   */
  public function getTotalOtpVisibleOns()
  {
    return $this->getOtpVisibleOns();
  }

  /**
   * Видимый OTP CR
   * @return float
   */
  public function getOtpVisibleCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpVisibleOns();
    $cr->fullCount = $this->getOtpAccepted();
    return $cr->getRate();
  }

  /**
   * OTP профит партнера
   * @return float
   */
  public function getOtpPartnerProfit()
  {
    return $this->getRowDataDto()->{'otpPartnerProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * Обертка для шаблона Тотал
   * @return float
   */
  public function getTotalOtpPartnerProfit()
  {
    return $this->getOtpPartnerProfit();
  }

  /**
   * OTP процент профита партнера
   * @return float
   */
  public function getOtpPartnerProfitRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpPartnerProfit();
    $cr->fullCount = $this->getOtpResellerProfit();
    return $cr->getRate();
  }

  /**
   * Средний профит партнера за OTP
   * @return float
   */
  public function getOtpAvgPartnerProfit()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpPartnerProfit();
    $cr->fullCount = $this->getOtpOns();
    return $cr->getRate();
  }

  /**
   * Средний профит партнера за видимый OTP
   * @return float
   */
  public function getOtpVisibleAvgPartnerProfit()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpPartnerProfit();
    $cr->fullCount = $this->getOtpVisibleOns();
    return $cr->getRate();
  }

  /**
   * @return float
   */
  public function getOtpRpm()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpResellerProfit();
    $cr->fullCount = $this->getOtpAccepted();
    return $cr->getRate() * 1000;
  }

  /**
   * @return float
   */
  public function getOtpNotifyRpm()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpPartnerProfit();
    $cr->fullCount = $this->getOtpAccepted();
    return $cr->getRate() * 1000;
  }

  /**
   * @return float
   */
  public function getOtpResellerProfit()
  {
    return $this->getRowDataDto()->{'otpResellerProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * OTP чистый профит реселлера
   * @return float
   */
  public function getOtpResellerNetProfit()
  {
    return $this->getOtpResellerProfit() - $this->getOtpPartnerProfit();
  }

  /**
   * OTP Total Margin
   * @return float
   */
  public function getOtpTotalMargin()
  {
    return $this->getOtpResellerNetProfit() + $this->getOtpAdjustment();
  }


  /**
   * Обертка для шаблона Тотал
   * @return float
   */
  public function getTotalOtpResellerNetProfit()
  {
    return $this->getOtpResellerNetProfit();
  }

  /**
   * OTP процент чистого профита реселлера
   * @return float
   */
  public function getOtpResellerNetProfitRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpResellerNetProfit();
    $cr->fullCount = $this->getOtpResellerProfit();
    return $cr->getRate();
  }

  /**
   * OTP процент чистого профита реселлера
   * @return float
   */
  public function getOtpTotalMarginRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpTotalMargin();
    $cr->fullCount = $this->getOtpResellerProfit();
    return $cr->getRate();
  }

  /**
   * OTP процент чистого профита реселлера от общего оборота
   * @return float
   */
  public function getOtpResellerNetProfitTotalRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpResellerNetProfit();
    $cr->fullCount = $this->getTotalResellerProfit();
    return $cr->getRate();
  }

  /**
   * @return float
   */
  public function getOtpFixCommissions()
  {
    return '';
  }

  /**
   * @return float
   */
  public function getOtpAdjustment()
  {
    return $this->getRowDataDto()->{'otpResellerCorrectedProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * @return float
   */
  public function getOtpAdjustmentRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpAdjustment();
    $cr->fullCount = $this->getOtpResellerProfit();
    return $cr->getRate();
  }

  /**
   * OTP жалобы RGK
   * @return int
   */
  public function getOtpRgkComplaints()
  {
    return $this->getRowDataDto()->otpTextComplaints + $this->getRowDataDto()->otpCallComplaints;
  }

  /**
   * OTP процент жалоб RGK от ревшар подписок
   * @return float
   */
  public function getOtpRgkComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpRgkComplaints();
    $cr->fullCount = $this->getOtpOns();
    return $cr->getRate();
  }

  /**
   * OTP жалобы звонки ОСС
   * @return int
   */
  public function getOtpCallMnoComplaints()
  {
    return $this->getRowDataDto()->otpCallMnoComplaints;
  }

  /**
   * OTP процент жалоб звонков ОСС от ревшар подписок
   * @return float
   */
  public function getOtpCallMnoComplaintsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOtpCallMnoComplaints();
    $cr->fullCount = $this->getOtpOns();
    return $cr->getRate();
  }


  /**
   * @return int
   */
  public function getAliveOns()
  {
    return $this->getRevshareAliveOns() + $this->getToBuyoutAliveOns();
  }

  /**
   * @return float
   */
  public function getAliveOnsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getAliveOns();
    $cr->fullCount = $this->getTotalSubscriptions();
    return $cr->getRate();
  }

  /**
   * @return int
   */
  public function getRevshareAliveOns()
  {
    return $this->getRowDataDto()->revshareAliveOns;
  }

  /**
   * @return float
   */
  public function getRevshareAliveOnsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareAliveOns();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * @return int
   */
  public function getToBuyoutAliveOns()
  {
    return $this->getRowDataDto()->toBuyoutAliveOns;
  }

  /**
   * @return float
   */
  public function getToBuyoutAliveOnsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutAliveOns();
    $cr->fullCount = $this->getToBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Профит с ревшар LTV ребиллов
   * @return float
   */
  public function getRevshareResellerLtvProfit()
  {
    return $this->getRowDataDto()->{'revshareResellerLtvProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * Revshar ARPU
   * @return float
   */
  public function getRevshareArpu()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareResellerLtvProfit();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * Профит с CPA LTV ребиллов
   * @return float
   */
  public function getToBuyoutResellerLtvProfit()
  {
    return $this->getRowDataDto()->{'toBuyoutResellerLtvProfit' . ucfirst($this->getCurrency())};
  }

  /**
   * ARPU
   * @return float
   */
  public function getToBuyoutArpu()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutResellerLtvProfit();
    $cr->fullCount = $this->getToBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Профит со всех LTV ребиллов
   * @return float
   */
  public function getTotalResellerLtvProfit()
  {
    return $this->getRevshareResellerLtvProfit() + $this->getToBuyoutResellerLtvProfit();
  }

  /**
   * ARPU
   * @return float
   */
  public function getTotalArpu()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getTotalResellerLtvProfit();
    $cr->fullCount = $this->getRevshareOns() + $this->getToBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Ревшар LTV ребиллы
   * @return int
   */
  public function getRevshareLtvRebills()
  {
    return $this->getRowDataDto()->revshareLtvRebills;
  }

  /**
   * CPA LTV ребиллы
   * @return int
   */
  public function getToBuyoutLtvRebills()
  {
    return $this->getRowDataDto()->toBuyoutLtvRebills;
  }

  /**
   * Ревшар LTV ребиллы % от подписок
   * @return float
   */
  public function getRevshareLtvRebillsAvg()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareLtvRebills();
    $cr->fullCount = $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * CPA LTV ребиллы % от подписок
   * @return float
   */
  public function getToBuyoutLtvRebillsAvg()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutLtvRebills();
    $cr->fullCount = $this->getToBuyoutOns();
    return $cr->getRate();
  }

  /**
   * Все LTV ребиллы % от подписок
   * @return float
   */
  public function getTotalLtvRebillsAvg()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutLtvRebills() + $this->getRevshareLtvRebills();
    $cr->fullCount = $this->getToBuyoutOns() + $this->getRevshareOns();
    return $cr->getRate();
  }

  /**
   * Ревшар подписки без отписок
   * @return int
   */
  public function getRevshareTotalOnsWithoutOffs()
  {
    return $this->getRowDataDto()->revshareTotalOnsWithoutOffs;
  }

  /**
   * Подписки на выкуп без отписок
   * @return int
   */
  public function getToBuyoutTotalOnsWithoutOffs()
  {
    return $this->getRowDataDto()->toBuyoutTotalOnsWithoutOffs;
  }

  /**
   * Все подписки без отписок
   * @return int
   */
  public function getTotalOnsWithoutOffs()
  {
    return $this->getRevshareTotalOnsWithoutOffs() + $this->getToBuyoutTotalOnsWithoutOffs();
  }

  /**
   * @return int
   */
  public function getRevshareAlive30daysOns()
  {
    return $this->rowDataDto->revshareAlive30Ons;
  }

  /**
   * @return int
   */
  public function getToBuyoutAlive30daysOns()
  {
    return $this->rowDataDto->toBuyoutAlive30Ons;
  }

  /**
   * @return int
   */
  public function getAlive30daysOns()
  {
    return $this->getRevshareAlive30daysOns() + $this->getToBuyoutAlive30daysOns();
  }


  /**
   * % Ревшар подписок, подающих признаки жизни последние 30 дней от подписок без отписок
   * @return float
   */
  public function getRevshareAlive30daysOnsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareAlive30daysOns();
    $cr->fullCount = $this->getRevshareTotalOnsWithoutOffs();
    return $cr->getRate();
  }

  /**
   * % подписок на выкуп, подающих признаки жизни последние 30 дней от подписок без отписок
   * @return float
   */
  public function getToBuyoutAlive30daysOnsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getToBuyoutAlive30daysOns();
    $cr->fullCount = $this->getToBuyoutTotalOnsWithoutOffs();
    return $cr->getRate();
  }

  /**
   * % всех подписок, подающих признаки жизни последние 30 дней от подписок без отписок
   * @return float
   */
  public function getTotalAlive30daysOnsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getAlive30daysOns();
    $cr->fullCount = $this->getTotalOnsWithoutOffs();
    return $cr->getRate();
  }
}
