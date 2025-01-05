<?php

namespace mcms\pages\actions\partner_cabinet_styles\category;


use mcms\pages\models\PartnerCabinetStyleCategory;
use Yii;

/**
 * Class UpdateCategoryModal
 * @package mcms\pages\actions\partner_cabinet_styles
 */
class CreateCategoryModal extends CategoryActionAbstract
{

  /**
   * Модалка создания категории полей
   * @return array|string
   */
  public function run()
  {
    $this->controller->getView()->title = Yii::_t('pages.partner_cabinet_style_categories.create-category');
    $model = new PartnerCabinetStyleCategory();
    $model->loadDefaultValues();
    return $this->handleCategoriesAjaxForm($model);
  }
}