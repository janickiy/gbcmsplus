<?php

namespace mcms\partners\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\partners\models\TicketMessageForm;
use Yii;
use yii\base\Widget;

class TicketCreateMessageWidget extends Widget
{

  /** @var \mcms\support\models\Support */
  public $ticket;

  public $formId;

  const FORM_ID_PREFIX = 'ticketMessageForm';

  public function init()
  {
    parent::init();
    $this->formId = self::FORM_ID_PREFIX . $this->ticket->id;
  }


  /**
   * @inheritdoc
   */
  public function run()
  {
    return $this->render('ticket_create_message', [
      'model' => new TicketMessageForm,
      'ticket' => $this->ticket,
      'formId' => $this->formId
    ]);
  }
}