<?php
namespace mcms\currency\components\events;

use mcms\common\event\Event;
use mcms\currency\models\Currency;
use Yii;

/**
 * Курс стал невыгодным и теперь будет использоваться оригинальный
 */
class CustomCourseBecameUnprofitable extends Event
{

  /** @var Currency */
  public $currency;
  /** @var string */
  public $unprofitableCourseCurency;

  /**
   * CustomCourseBecameUnprofitable constructor.
   * @param Currency|null $currency
   * @param string|null $unprofitableCourseCurency
   */
  public function __construct(Currency $currency = null, $unprofitableCourseCurency = null)
  {
    $this->currency = $currency;
    $this->unprofitableCourseCurency = $unprofitableCourseCurency;
  }

  public function getModelId()
  {
    return $this->currency->id;
  }

  function getEventName()
  {
    return Yii::_t('currency.events.custom_course_became_unprofitable');
  }

  /**
   * @inheritdoc
   */
  public function getAdditionalReplacements()
  {
    return $this->currency && $this->unprofitableCourseCurency ? [
      'currency.course' => $this->currency->code . ' => ' . $this->unprofitableCourseCurency,
      'currency.original_course' => $this->currency->{'to_' . $this->unprofitableCourseCurency} * (100 - $this->currency->{'partner_percent_' . $this->unprofitableCourseCurency}) / 100,
      'currency.custom_course' => $this->currency->{'custom_to_' . $this->unprofitableCourseCurency},
    ] : [];
  }

  /**
   * @inheritdoc
   */
  public function getReplacementsHelp()
  {
    return [
      '{currency.course}' => Yii::_t('currency.events.course'),
      '{currency.original_course}' => Yii::_t('currency.events.original_course'),
      '{currency.custom_course}' => Yii::_t('currency.events.custom_course'),
    ];
  }
}