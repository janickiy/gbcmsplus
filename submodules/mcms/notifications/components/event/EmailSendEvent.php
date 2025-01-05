<?php
namespace mcms\notifications\components\event;

use mcms\common\event\Event;

class EmailSendEvent extends Event
{
  public $from;
  public $to;
  public $header;
  public $template;
  public $status;
  public $language;

  /**
   * EmailSendEvent constructor.
   * @param $from
   * @param $to
   * @param $header
   * @param $template
   * @param $status
   * @param null $language
   */
  public function __construct($from = null, $to = null, $header = null, $template = null, $status = null, $language = null)
  {
    $this->from = $from;
    $this->to = $to;
    $this->header = $header;
    $this->template = $template;
    $this->status = $status;
    $this->language = $language;
  }

  public function getReplacementsHelp()
  {
    return [
      '{from}' => 'From',
      '{to}' => 'To',
      '{header}' => 'Header',
      '{template}' => 'Template',
      '{status}' => 'Status',
      '{language}' => 'Language'
    ];
  }

  public function getReplacements()
  {
    return [
      '{from}' => $this->from,
      '{to}' => $this->to,
      '{header}' => $this->header,
      '{template}' => $this->template,
      '{status}' => $this->status,
      '{language}' => $this->language,
    ];
  }


  function getEventName()
  {
    return 'Email send event';
  }

}