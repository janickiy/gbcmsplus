<?php

namespace mcms\pages\actions\partner_cabinet_styles\category;


use mcms\pages\models\PartnerCabinetStyleCategory;
use Yii;
use yii\web\Response;
use mcms\common\web\AjaxResponse;
use yii\widgets\ActiveForm;

/**
 * Class DeleteCategory
 * @package mcms\pages\actions\partner_cabinet_styles
 */
class DeleteCategory extends CategoryActionAbstract
{

  /**
   * Удаление категории полей
   * @param $id
   * @return array
   */
  public function run($id)
  {
    return AjaxResponse::set($this->findModelStyleCategory($id)->delete());
  }
}