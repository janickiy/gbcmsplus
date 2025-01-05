<?php

namespace mcms\support\components\grid;

use Yii;
use yii\grid\DataColumn;

class SupportDelegatedTo extends DataColumn
{
  public function init()
  {
    parent::init();
    $this->value = function($model) {
      /** @var \mcms\support\models\Support $model */
      $delegatedTo = $model->getDelegatedTo()->one();
      return $delegatedTo === null
        ? Yii::_t('support.controller.ticket_notDelegated')
        : $delegatedTo->username
        ;
    };
  }

}