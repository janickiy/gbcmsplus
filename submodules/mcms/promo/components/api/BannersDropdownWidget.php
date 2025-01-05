<?php


namespace mcms\promo\components\api;


use mcms\common\module\api\ApiResult;
use mcms\promo\components\widgets\BannersDropdown;

/**
 * Пример использования:
 * <?= $form->field($model, 'banners')->widget(Yii::$app->getModule('promo')->api('bannersDropdown')
    ->getWidgetclass, [
    'options' => [
    'title' => $model->getAttributeLabel('banners'),
    'prompt' => null,
    'multiple' => true,
    'data-selected-text-format' => 'count>0',
    'data-count-selected-text' => $model->getAttributeLabel('banners'). ' ({0}/{1})',
    ]]) ?>
 *
 *
 * Class BannersDropdownWidget
 * @package mcms\promo\components\api
 */
class BannersDropdownWidget extends ApiResult
{

  public $params;

  public function init($params = [])
  {
    $this->params = $params;
  }

  public function getWidgetclass
  {
    return BannersDropdown::class;
  }

  public function getResult()
  {
    $this->prepareWidget($this->getWidgetclass, $this->params);
    return parent::getResult();
  }


}