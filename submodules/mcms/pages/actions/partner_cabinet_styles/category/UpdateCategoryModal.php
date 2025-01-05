<?php

namespace mcms\pages\actions\partner_cabinet_styles\category;


use Yii;

/**
 * Class UpdateCategoryModal
 * @package mcms\pages\actions\partner_cabinet_styles
 */
class UpdateCategoryModal extends CategoryActionAbstract
{

  /**
   * Модалка редактирования категории полей
   * @param $id
   * @return array|string
   */
  public function run($id)
  {
    $this->controller->getView()->title = Yii::_t('pages.partner_cabinet_style_categories.update-category');
    return $this->handleCategoriesAjaxForm($this->findModelStyleCategory($id));
  }
}