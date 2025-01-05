<?php
namespace mcms\promo\components\events;

use Yii;
use yii\helpers\Url;

class SourceCreated extends AbstractSource
{
  function getEventName()
  {
    return Yii::_t('promo.events.source_created');
  }

  public function trigger()
  {
    if ($this->source->isStatusModeration()) {
      (new SourceCreatedModeration($this->source))->trigger();
      return ;
    }

    parent::trigger();
  }


  public static function getUrl($id = null)
  {
    return ['/promo/webmaster-sources/index/'];
  }
}