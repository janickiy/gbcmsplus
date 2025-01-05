<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Domain;
use Yii;

/**
 * Class DomainAdded
 * @package mcms\promo\components\events
 */
class DomainAdded extends Event
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
    if ($this->domain->isSystem()) {
      (new SystemDomainAdded($this->domain))->trigger();
      return ;
    }
    parent::trigger();
  }

  public static function getUrl($id = null)
  {
    return ['/promo/domains/view/', 'id' => $id];
  }


  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.domain_created');
  }
}