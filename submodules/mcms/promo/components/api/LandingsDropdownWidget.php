<?php
namespace mcms\promo\components\api;

use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\LandingsDropdown;

/**
 * Пример использования:
 * <?= $form->field($model, 'landings')->widget(Yii::$app->getModule('promo')->api('landingsDropdown')
 *    ->getWidgetclass, [
 *      'options' => [
 *      'title' => $model->getAttributeLabel('landings'),
 *      'prompt' => null,
 *      'multiple' => true,
 *      'data-selected-text-format' => 'count>0',
 *      'data-count-selected-text' => $model->getAttributeLabel('landings'). ' ({0}/{1})',
 *    ]
 * ]) ?>
 *
 * Class LandingsDropdownWidget
 * @package mcms\promo\components\api
 */
class LandingsDropdownWidget extends ApiResult
{
  public $params;

  public function init($params = [])
  {
    $this->params = $params;
  }

  public function getWidgetclass
  {
    return LandingsDropdown::class;
  }

  public function getResult()
  {
    $this->prepareWidget($this->getWidgetclass, $this->params);
    return parent::getResult();
  }
}