<?php


namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\OperatorsDropdown;

/**
 * Пример использования:
 * <?= $form->field($model, 'operators')->widget(Yii::$app->getModule('promo')->api('operatorsDropdown')
    ->getWidgetclass, [
    'options' => [
    'title' => $model->getAttributeLabel('operators'),
    'prompt' => null,
    'multiple' => true,
    'data-selected-text-format' => 'count>0',
    'data-count-selected-text' => $model->getAttributeLabel('operators'). ' ({0}/{1})',
    ]]) ?>
 *
 *
 * Class OperatorsDropdownWidget
 * @package mcms\promo\components\api
 */
class OperatorsDropdownWidget extends ApiResult
{

  public $params;

  public function init($params = [])
  {
    $this->params = $params;
  }

  public function getWidgetclass
  {
    return OperatorsDropdown::class;
  }

  public function getResult()
  {
    $this->prepareWidget($this->getWidgetclass, $this->params);
    return parent::getResult();
  }


}