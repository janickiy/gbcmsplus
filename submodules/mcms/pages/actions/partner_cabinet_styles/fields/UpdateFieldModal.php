<?php

namespace mcms\pages\actions\partner_cabinet_styles\fields;


/**
 * Class UpdateFieldModal
 * @package mcms\pages\actions\partner_cabinet_styles\fields
 */
class UpdateFieldModal extends FieldActionAbstract
{

  /**
   * @inheritdoc
   */
  public function run($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

}