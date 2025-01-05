<?php

namespace mcms\partners\components\widgets;

use Yii;
use yii\base\Widget;

class TicketsListWidget extends Widget
{

  const PJAX_ID = 'ticketsListPjax';

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    $tickets = Yii::$app->getModule('support')->api(
      'getTicketList',
      [
        'conditions' => ['created_by' => Yii::$app->user->id],
        'pagination' => [
          'pageSize' => 10
        ],
        'sort' => [
          'defaultOrder' => [
            'support.last_text_created_at' => SORT_DESC
          ],
          'attributes' => ['support.last_text_created_at']
        ]
      ]
    )->getResult();

    return $this->render('tickets_list', [
      'tickets' => $tickets,
      'pjaxId' => self::PJAX_ID
    ]);
  }
}