<?php
namespace mcms\promo\components\events;

use mcms\common\event\Event;
use mcms\promo\models\Domain;
use Yii;

/**
 * Class DomainChanged
 * @package mcms\promo\components\events
 */
class DomainBanned extends Event
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

  public function getOwner()
  {
    return $this->domain->user;
  }

  public static function getUrl($id = null)
  {
    return ['/partners/domains/index/'];
  }

  /**
   * @return string
   */
  function getEventName()
  {
    return Yii::_t('promo.events.domain_banned');
  }
}