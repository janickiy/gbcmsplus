<?php

namespace mcms\promo\components\provider_instances_sync\response_parsers;

use mcms\promo\components\provider_instances_sync\dto\Error;
use yii\helpers\ArrayHelper;

abstract class AbstractResponseParser
{

  private $hasError = false;

  /** @var mixes */
  private $data;

  public function __construct($rawResponse)
  {
    if (empty($rawResponse)) {
      $this->hasError = true;

      $this->data = [
        'name' => 'Unknown error'
      ];

      return ;
    }
    $this->data = ArrayHelper::getValue($rawResponse, 'data', []);

    if (!$responseStatus = ArrayHelper::getValue($rawResponse, 'success', false)) {
      $this->hasError = true;
    }
  }

  /**
   * @return array
   */
  public function getData()
  {
    return $this->data;
  }

  public function getError()
  {
    $errorDto = new Error();
    $errorDto->name = ArrayHelper::getValue($this->data, 'name');
    $errorDto->message = ArrayHelper::getValue($this->data, 'message');
    $errorDto->code = (int) ArrayHelper::getValue($this->data, 'code');
    $errorDto->status = (int) ArrayHelper::getValue($this->data, 'status');

    return $errorDto;
  }

  /**
   * @return bool
   */
  public function isHasError()
  {
    return $this->hasError;
  }

  /**
   * @return mixed
   */
  abstract public function parse();
}