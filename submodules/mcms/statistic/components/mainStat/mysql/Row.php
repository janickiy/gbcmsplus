<?php

namespace mcms\statistic\components\mainStat\mysql;

use mcms\statistic\models\Complain;
use yii\base\Model;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\models\Cr;
use Yii;

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
   * @var RowDataDto
   */
  protected $_rowDataDto;

  public function init()
  {
    parent::init();
    $this->_rowDataDto = new RowDataDto();
  }

  /**
   * Возвращает объект с данными из БД
   * @return RowDataDto
   */
  public function getRowDataDto()
  {
    return $this->_rowDataDto;
  }

  /**
   * @return int
   */
  public function getHits()
  {
    return (int)$this->rowDataDto->hits;
  }

  /**
   * @return Group[]
   */
  public function getGroups()
  {
    return $this->groups;
  }

  /**
   * @return int
   */
  public function getTb()
  {
    return (int)$this->rowDataDto->tb;
  }

  /**
   * @return int
   */
  public function getAccepted()
  {
    return $this->getHits() - $this->getTb();
  }

  /**
   * @return int
   */
  public function getUniques()
  {
    return (int)$this->rowDataDto->uniques;
  }

  /**
   * @return int
   */
  public function getCpaUniques()
  {
    return (int)$this->rowDataDto->cpaUniques - (int)$this->rowDataDto->onetimeUniques;
  }

  /**
   * @return int
   */
  public function getCpaUniquesTb()
  {
    return (int)$this->rowDataDto->cpaUniquesTb - (int)$this->rowDataDto->onetimeUniquesTb;
  }

  /**
   * @return int
   */
  public function getCpaAcceptedUniques()
  {
    return $this->getCpaUniques() - $this->getCpaUniquesTb();
  }

  /**
   * @return int
   */
  public function getOnetimeUniques()
  {
    return (int)$this->rowDataDto->onetimeUniques;
  }

  /**
   * @return int
   */
  public function getOnetimeUniquesTb()
  {
    return (int)$this->rowDataDto->onetimeUniquesTb;
  }

  /**
   * @return int
   */
  public function getOnetimeAcceptedUniques()
  {
    return $this->getOnetimeUniques() - $this->getOnetimeUniquesTb();
  }

  /**
   * @return int
   */
  public function getRevshareUniques()
  {
    return (int)$this->rowDataDto->revshareUniques;
  }

  /**
   * @return int
   */
  public function getRevshareUniquesTb()
  {
    return (int)$this->rowDataDto->revshareUniquesTb;
  }

  /**
   * @return int
   */
  public function getRevshareAcceptedUniques()
  {
    return $this->getRevshareUniques() - $this->getRevshareUniquesTb();
  }


  /**
   * Ревшар переходы
   * @return int
   */
  public function getRevshareAccepted()
  {
    return (int)$this->rowDataDto->revshareHits - (int)$this->rowDataDto->revshareTb;
  }

  /**
   * Равшар подписки
   * @return int
   */
  public function getOns()
  {
    return (int)$this->rowDataDto->ons;
  }

  /**
   * Равшар отписки
   * @return int
   */
  public function getOffs()
  {
    return (int)$this->rowDataDto->offs;
  }

  /**
   * Ревшар отписки 24
   * @return string
   */
  public function getScopeOffsData()
  {
    return Yii::$app->formatter->asInteger($this->rowDataDto->scopeOffs) .
      ' (' . Yii::$app->formatter->asPercent([$this->rowDataDto->scopeOffs, $this->getOns()], 2) . ')';
  }

  /**
   * Ревшар ратио
   * @param string $format
   * @return string
   */
  public function getRevshareRatio($format = '1:%s')
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareAccepted();
    $cr->fullCount = $this->getOns();
    $rightRatioValue = round($cr->getRate(), 1);

    return sprintf($format, Yii::$app->formatter->asDecimal($rightRatioValue, 1));
  }

  /**
   * Ревшар CR %
   * @return string
   */
  public function getRevshareCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOns();
    $cr->fullCount = $this->getRevshareAccepted();
    return $cr->getRate() * 100;
  }

  /**
   * Ревшар первичные списания
   * @return int
   */
  public function getRebillsDateByDate()
  {
    return (int)$this->rowDataDto->rebillsDateByDate;
  }

  /**
   * Ревшар первичная ребильность %
   * @return float
   */
  public function getChargeRatio()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRebillsDateByDate();
    $cr->fullCount = $this->getOns();
    return $cr->getRate();
  }

  /**
   * Ревшар сумма первичных списаний
   * @return float
   */
  public function getProfitDateByDate()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'profit' . $currency . 'DateByDate'};
  }

  /**
   * Ребиллы
   * @return int
   */
  public function getRebills()
  {
    return (int)$this->rowDataDto->rebills;
  }

  /**
   * Ревшар доход реселлера
   * @return float
   */
  public function getRevshareResellerProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'revshareResellerProfit' . $currency};
  }

  /**
   * Профит реселлера (NET) Revshare
   * @return float
   */
  public function getRevshareResellerNetProfit()
  {
    return $this->getRevshareResellerProfit() - $this->getPartnerRevshareProfit();
  }

  /**
   * Ревшар доход партнера
   * @return float
   */
  public function getPartnerRevshareProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'partnerRevshareProfit' . $currency};
  }

  /**
   * eCPC Revshare
   * @return float
   */
  public function getEcpcRevshare()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getPartnerRevshareProfit();
    $cr->fullCount = $this->getRevshareAccepted();
    return $cr->getRate();
  }

  /**
   * CPA переходы
   * @return int
   */
  public function getCpaAccepted()
  {
    return (int)$this->rowDataDto->cpaHits - (int)$this->rowDataDto->cpaTb - (int)$this->rowDataDto->onetimeHits + (int)$this->rowDataDto->onetimeTb;
  }

  /**
   * CPA подписки
   * @return int
   */
  public function getCpaOns()
  {
    return (int)$this->rowDataDto->cpaOns;
  }

  /**
   * CPA отписки
   * @return int
   */
  public function getCpaOffs()
  {
    return (int)$this->rowDataDto->rejectedOffs + $this->getSoldOffs();
  }

  /**
   * Sold отписки
   * @inheritdoc
   */
  public function getSoldOffs()
  {
    return (int)$this->rowDataDto->soldOffs;
  }

  /**
   * CPA отписки 24
   * @return int
   */
  public function getCpaScopeOffs()
  {
    return (int)$this->rowDataDto->rejectedScopeOffs + $this->getSoldScopeOffs();
  }

  /**
   * Sold отписки 24
   * @inheritdoc
   */
  public function getSoldScopeOffs()
  {
    return (int)$this->rowDataDto->soldScopeOffs;
  }

  /**
   * Ревшар отписки 24
   * @return string
   */
  public function getSoldScopeOffsData()
  {
    return Yii::$app->formatter->asInteger($this->getSoldScopeOffs()) .
      ' (' . Yii::$app->formatter->asPercent([$this->getSoldScopeOffs(), $this->getCpaOns()], 2) . ')';
  }

  /**
   * CPA отписки 24 с процентом
   * @return string
   */
  public function getCpaScopeOffsData()
  {
    return Yii::$app->formatter->asInteger($this->getCpaScopeOffs()) .
      ' (' . Yii::$app->formatter->asPercent([$this->getCpaScopeOffs(), $this->getCpaOns()], 2) . ')';
  }

  /**
   * CPA ратио
   * @param string $format
   * @return string
   */
  public function getCpaRatio($format = '1:%s')
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getCpaAccepted();
    $cr->fullCount = $this->getCpaOns();
    $rightRatioValue = round($cr->getRate(), 1);

    return sprintf($format, Yii::$app->formatter->asDecimal($rightRatioValue, 1));
  }

  /**
   * CPA CR
   * @return string
   */
  public function getCpaCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getCpaOns();
    $cr->fullCount = $this->getCpaAccepted();

    return $cr->getRate() * 100;
  }

  /**
   * CPA CR от проданных
   * @return string
   */
  public function getCpaCrSold()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->rowDataDto->sold;
    $cr->fullCount = $this->getCpaAccepted();

    return $cr->getRate() * 100;
  }

  /**
   * CPA CR от видимых
   * @return string
   */
  public function getCpaCrVisible()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->rowDataDto->soldVisible;
    $cr->fullCount = $this->getCpaAccepted();

    return $cr->getRate() * 100;
  }

  /**
   * Сколько всего первичных ребиллов по ЦПА (проданные и отклоненные в сумме)
   * @return int
   */
  public function getCpaRebillsDateByDate()
  {
    return $this->getSoldRebillsDateByDate()
      + (int)$this->rowDataDto->rejectedRebillsDateByDate;
  }

  /**
   * Сколько проданных первичных ребиллов
   * @return int
   */
  public function getSoldRebillsDateByDate()
  {
    return (int)$this->rowDataDto->soldRebillsDateByDate;
  }

  /**
   * Первичная ребильность
   * @return float|int
   */
  public function getCpaChargeRatio()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getCpaRebillsDateByDate();
    $cr->fullCount = $this->getCpaOns();

    return $cr->getRate();
  }

  /**
   * Выкупленные подписки
   * @return int
   */
  public function getSold()
  {
    return (int)$this->rowDataDto->sold;
  }

  /**
   * Выкупленные видимые партнеру подписки
   * @return int
   */
  public function getSoldVisible()
  {
    return (int)$this->rowDataDto->soldVisible;
  }

  /**
   * Отклоненные подписки
   * @return int
   */
  public function getRejectedOns()
  {
    return (int)$this->rowDataDto->rejectedOns;
  }

  /**
   * Первичный профит с отклоненных
   * @return float
   */
  public function getRejectedProfitDateByDate()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'rejectedProfitDateByDate' . $currency};
  }

  /**
   * Сколько всего первичного профита с подписок по ЦПА (проданные и отклоненные в сумме)
   * @return float
   */
  public function getCpaProfitDateByDate()
  {
    return $this->getSoldProfitDateByDate() + $this->getRejectedProfitDateByDate();
  }

  /**
   * Ребиллы по выкупленным подпискам
   * @return int
   */
  public function getSoldRebills()
  {
    return (int)$this->rowDataDto->soldRebills;
  }

  /**
   * Ребиллы по отклоненным подпискам
   * @return int
   */
  public function getRejectedRebills()
  {
    return (int)$this->rowDataDto->rejectedRebills;
  }

  /**
   * Сколько всего ребиллов по ЦПА (проданные и отклоненные в сумме)
   * @return int
   */
  public function getCpaRebills()
  {
    return $this->getSoldRebills() + $this->getRejectedRebills();
  }

  /**
   * Доход с выкупленных ребилов
   * @return float
   */
  public function getSoldRebillsProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'soldRebillsProfit' . $currency};
  }

  /**
   * Доход с отклоненных подписок
   * @return float
   */
  public function getRejectedProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'rejectedProfit' . $currency};
  }

  /**
   * CPA профит (выкупленные ребилы и отклоненные)
   * @return float
   */
  public function getCpaProfit()
  {
    return $this->getSoldRebillsProfit() + $this->getRejectedProfit();
  }

  /**
   * CPA доход партнера
   * @return float
   */
  public function getSoldPartnerProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'soldPartnerProfit' . $currency};
  }

  /**
   * Прибыль реселлера
   * @return float
   */
  public function getCpaResellerNetProfit()
  {
    return $this->getSoldRebillsProfit() - $this->getSoldPartnerProfit();
  }

  /**
   * eCP СPA
   * @return float
   */
  public function getCpaEcp()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getSoldPrice();
    $cr->fullCount = (int)$this->rowDataDto->hits;
    return $cr->getRate();
  }

  /**
   * eCPC CPA
   * @return float
   */
  public function getCpaEcpc()
  {
    $currency = ucfirst($this->getCurrency());
    $cr = new Cr();
    $cr->convertionsCount = (float)$this->rowDataDto->{'soldPartnerProfit' . $currency};
    $cr->fullCount = (int)$this->getCpaAccepted();
    return $cr->getRate();
  }

  /**
   * CPR
   * @return float|int
   */
  public function getCpaCpr()
  {
    $currency = ucfirst($this->getCurrency());
    $cr = new Cr();
    $cr->convertionsCount = (float)$this->rowDataDto->{'soldPartnerPrice' . $currency};
    $cr->fullCount = (int)$this->getSoldVisible();
    return $cr->getRate();
  }

  /**
   * Avg. CPA
   * @return float|int
   */
  public function getAvgCpa()
  {
    $currency = ucfirst($this->getCurrency());
    $cr = new Cr();
    $cr->convertionsCount = (float)$this->rowDataDto->{'soldPartnerProfit' . $currency};
    $cr->fullCount = (int)$this->getSold();
    return $cr->getRate();
  }

  /**
   * Прибыль на подписку ARPU
   * @return float|null
   */
  public function getRevSub()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getSoldProfitDateByDate();
    $cr->fullCount = (int)$this->getCpaOns() - (int)$this->getRejectedOns();
    return $cr->getRate();
  }

  /**
   * @return float
   */
  public function getSoldPrice()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'soldPrice' . $currency};
  }

  /**
   * Первичный профит с выкупленных
   * @return float
   */
  public function getSoldProfitDateByDate()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'soldProfitDateByDate' . $currency};
  }

  /**
   * Первичное ROI
   * @return float
   */
  public function getRoiOnDate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getSoldProfitDateByDate();
    $cr->fullCount = $this->getSoldPrice();
    $rate = $cr->getRate();

    if ($rate == 0) {
      return 0;
    }

    return ($rate - 1) * 100;
  }

  /**
   * Ecpc Efficiency
   * @return float
   */
  public function getEcpc()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getPartnerTotalProfit();
    $cr->fullCount = $this->getHits();
    return $cr->getRate();
  }

  /**
   * Onetime переходы
   * @return int
   */
  public function getAcceptedOnetime()
  {
    return (int)$this->rowDataDto->onetimeHits - (int)$this->rowDataDto->onetimeTb;
  }

  /**
   * Onetime продажи
   * @return int
   */
  public function getOnetime()
  {
    return (int)$this->rowDataDto->onetime;
  }

  /**
   * Onetime продажи видимые партнеру
   * @return int
   */
  public function getVisibleOnetime()
  {
    return (int)$this->rowDataDto->visibleOnetime;
  }

  /**
   * Onetime ратио
   * @param string $format
   * @return string
   */
  public function getOnetimeRatio($format = '1:%s')
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getAcceptedOnetime();
    $cr->fullCount = $this->getOnetime();

    return sprintf($format, Yii::$app->formatter->asDecimal($cr->getRate(), 1));
  }

  /**
   * Onetime CR %
   * @return string
   */
  public function getOnetimeCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOnetime();
    $cr->fullCount = $this->getAcceptedOnetime();

    return $cr->getRate() * 100;
  }

  /**
   * Видимый Onetime CR %
   * @return string
   */
  public function getVisibleOnetimeCr()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getVisibleOnetime();
    $cr->fullCount = $this->getAcceptedOnetime();

    return $cr->getRate() * 100;
  }

  /**
   * Onetime доход реселлера
   * @return float
   */
  public function getOnetimeResellerProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'onetimeResellerProfit' . $currency};
  }

  /**
   * Onetime доход партнера
   * @return float
   */
  public function getOnetimeProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'onetimeProfit' . $currency};
  }

  /**
   * eCPC Onetime
   * @return float
   */
  public function getEcpcOnetime()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getOnetimeProfit();
    $cr->fullCount = (int) $this->getAcceptedOnetime();
    return $cr->getRate();
  }

  /**
   * Onetime прибыль реселлера (NET)
   * @return float
   */
  public function getOnetimeResellerNetProfit()
  {
    return $this->getOnetimeResellerProfit() - $this->getOnetimeProfit();
  }

  /**
   * Принято продаж ТБ
   * @return float
   */
  public function getSellTbAccepted()
  {
    return (int)$this->rowDataDto->sellTbAccepted;
  }

  /**
   * Отправлено ТБ
   * @return float
   */
  public function getSoldTb()
  {
    return (int)$this->rowDataDto->soldTb;
  }

  /**
   * Доход с продажи ТБ
   * @return float
   */
  public function getSoldTbProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'soldTbProfit' . $currency};
  }

  /**
   * Reseller total profit
   * @return float
   */
  public function getResellerTotalProfit()
  {
    return $this->getRevshareResellerProfit() + $this->getCpaProfit() + $this->getOnetimeResellerProfit() + $this->getSoldTbProfit();
  }

  /**
   * Reseller net profit
   * @return float
   */
  public function getResellerNetProfit()
  {
    return $this->getResellerTotalProfit() - $this->getPartnerTotalProfit();
  }

  /**
   * Partner total profit
   * @return float
   */
  public function getPartnerTotalProfit()
  {
    return $this->getPartnerRevshareProfit() + $this->getSoldPartnerProfit() + $this->getOnetimeProfit();
  }

  /**
   * Письменные жалобы
   * @return int
   */
  public function getComplains()
  {
    return (int)$this->rowDataDto->complains;
  }

  /**
   * Звонки
   * @return int
   */
  public function getCalls()
  {
    return (int)$this->rowDataDto->calls;
  }

  /**
   * Звонок КЦ ОСС
   * @return int
   */
  public function getCallsMno()
  {
    return (int)$this->rowDataDto->callsMno;
  }

  /**
   * @see Complain::TYPE_AUTO_24
   * @return int
   */
  public function getAuto24Complains()
  {
    return (int)$this->rowDataDto->complainAuto24;
  }

  /**
   * @see Complain::TYPE_AUTO_MOMENT
   * @return int
   */
  public function getAutoMomentComplains()
  {
    return (int)$this->rowDataDto->complainAutoMoment;
  }

  /**
   * @see Complain::TYPE_AUTO_DUPLICATE
   * @return int
   */
  public function getAutoDuplicateComplains()
  {
    return (int)$this->rowDataDto->complainAutoDuplicate;
  }

  /**
   * Звонок КЦ ОСС
   * @return float
   */
  public function getComplainsRate()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getComplains() + $this->getCalls() + $this->getCallsMno();
    $cr->fullCount = $this->getOns() + $this->getCpaOns() + $this->getOnetime();
    return $cr->getRate();
  }



  /**
   * Подставляем сразу из файла переводов
   * @param string $attribute
   * @return string
   */
  public function getAttributeLabel($attribute)
  {
    return Yii::_t('main_statistic_refactored.' . $attribute);
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
   * Оборот реселлера
   * @return float[]
   */
  public function getResellerTurnover()
  {
    // TRICKY При добавлении новых параметров нужно учитывать их в total (если надо)
    $data = [
      'revshare' => $this->getRevshareResellerProfit(),
      'cpa_sold' => $this->getSoldRebillsProfit(),
      'cpa_rejected' => $this->getRejectedProfit(),
      'onetime' => $this->getOnetimeResellerProfit(),
    ];
    $data['cpa'] = $this->getCpaProfit();
    $data['total'] = round($data['revshare'] + $data['cpa_sold'] + $data['cpa_rejected'] + $data['onetime'], 3);

    return $data;
  }
}
