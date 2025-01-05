<?php

namespace mcms\pages\actions\partner_cabinet_styles\fields;
use mcms\common\web\AjaxResponse;

/**
 * Class DeleteField
 * @package mcms\pages\actions\partner_cabinet_styles\fields
 */
class DeleteField extends FieldActionAbstract
{

  /**
   * @param $id
   * @return array
   */
  public function run($id)
  {
    return AjaxResponse::set($this->findModel($id)->delete());
  }
}