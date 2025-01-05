<?php

namespace mcms\partners\components\widgets;

use mcms\partners\models\TicketForm;
use Yii;
use yii\base\Widget;

class TicketCreateWidget extends Widget
{

  const MODAL_ID = 'ticketModal';
  const FORM_ID = 'ticketModalForm';

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    $ticketsCategories =  Yii::$app->getModule('support')->api('getTicketCategories', [
      'conditions' => [
        'is_disabled' => 0
      ]
    ])->getResult();

    $model = new TicketForm();

    return $this->render('ticket_create', [
      'model' => $model,
      'ticketsCategories' => $ticketsCategories
    ]);
  }
}