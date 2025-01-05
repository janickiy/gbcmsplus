<?php

namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\StreamsDropdown;

/**
 * Пример использования:
 * <?= $form->field($model, 'operators')->widget(Yii::$app->getModule('promo')->api('streamsDropdown')
    ->getWidgetclass, [
    'options' => [
      'title' => $model->getAttributeLabel('operators'),
      'prompt' => null,
      'multiple' => true,
  ]]) ?>
 *
 *
 * Class OperatorsDropdownWidget
 * @package mcms\promo\components\api
 */
class StreamsDropdownWidget extends ApiResult
{

  public function init($params = [])
  {
  }

  public function getWidgetclass
  {
    return StreamsDropdown::class;
  }
}
