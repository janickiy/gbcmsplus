<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Domain;
use Yii;

/**
 * Class DomainChanged
 * @package mcms\promo\components\events
 */
class DomainChanged extends Event
{

  /**
   * @var null
   */
  public $domain;

  /**
   * @param Domain|null $domain
   */
  public function __construct(Domain $domain = null)
  {
    $this->domain = $domain;
  }

  public function getModelId()
  {
    return $this->domain->id;
  }

  public function trigger()
  {
    if ($this->domain->isBanned()) {
      if ($this->domain->isSystem()) {
        (new SystemDomainBanned($this->domain))->trigger();
        return ;
      }

      (new DomainBanned($this->domain))->trigger();
      return ;
    }
    parent::trigger();
  }


  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.domain_changed');
  }
}