<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\AjaxLandingsDropdown;

/**
 * Пример использования:
 * <?= $form->field($model, 'operators')->widget(Yii::$app->getModule('promo')->api('ajaxLandingsDropdown')
    ->getWidgetclass, [
    'options' => [
      'title' => $model->getAttributeLabel('operators'),
      'prompt' => null,
  ]]) ?>
 *
 *
 * Class AjaxLandingsDropdownWidget
 * @package mcms\promo\components\api
 */
class AjaxLandingsDropdownWidget extends ApiResult
{

  public function init($params = [])
  {
  }

  public function getWidgetclass
  {
    return AjaxLandingsDropdown::class;
  }
}
