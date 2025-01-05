<?php

namespace mcms\pages\actions\partner_cabinet_styles\fields;

use mcms\pages\models\PartnerCabinetStyleField;

/**
 * Class CreateFieldModal
 * @package mcms\pages\actions\partner_cabinet_styles\fields
 */
class CreateFieldModal extends FieldActionAbstract
{

  /**
   * @inheritdoc
   */
  public function run()
  {
    $model = new PartnerCabinetStyleField();
    $model->loadDefaultValues();
    return $this->handleAjaxForm($model);
  }
}