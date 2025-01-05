<?php

namespace mcms\partners\components\widgets;

use mcms\common\helpers\ArrayHelper;
use site\controllers\UnsubscribeController;
use yii\base\Widget;

class EmailTemplateWidget extends Widget
{
  public $subject;

  public $body;

  public $email;

  public $options;

  public $unsubscribeUrl;

  public function init()
  {
    $this->subject = ArrayHelper::getValue($this->options, 'subject');
    $this->body = ArrayHelper::getValue($this->options, 'body');
    $this->email = ArrayHelper::getValue($this->options, 'email');
    $this->unsubscribeUrl = ArrayHelper::getValue($this->options, 'unsubscribeUrl');

    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    return $this->render('email_layout', [
      'title' => $this->subject,
      'text' => $this->body,
      'email' => $this->email,
      'unsubscribeUrl' => $this->unsubscribeUrl,
    ]);
  }
}
