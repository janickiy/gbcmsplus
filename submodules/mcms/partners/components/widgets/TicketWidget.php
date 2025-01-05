<?php

namespace mcms\partners\components\widgets;

use Yii;
use yii\base\Widget;

class TicketWidget extends Widget
{

  /** @var \mcms\support\models\Support */
  public $model;

  const PJAX_ID_PREFIX = 'ticketMessagesPjax';

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    return $this->render('one_ticket', [
      'model' => $this->model
    ]);
  }
}