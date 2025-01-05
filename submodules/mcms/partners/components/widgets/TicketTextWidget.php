<?php

namespace mcms\partners\components\widgets;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\base\Widget;

class TicketTextWidget extends Widget
{

  /** @var \mcms\support\models\SupportText */
  public $model;

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    return $this->render('one_ticket_text', [
      'model' => $this->model,
      'isOwner' => $this->model->isOwner
    ]);
  }
}