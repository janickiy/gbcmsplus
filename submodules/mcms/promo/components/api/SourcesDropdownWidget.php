<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\SourcesDropdown;

/**
 * Пример использования:
 * <?= $form->field($model, 'operators')->widget(Yii::$app->getModule('promo')->api('sourcesDropdown')
    ->getWidgetclass, [
    'options' => [
      'title' => $model->getAttributeLabel('operators'),
      'prompt' => null,
  ]]) ?>
 *
 *
 * Class OperatorsDropdownWidget
 * @package mcms\promo\components\api
 */
class SourcesDropdownWidget extends ApiResult
{

  public function init($params = [])
  {
  }

  public function getWidgetclass
  {
    return SourcesDropdown::class;
  }
}
