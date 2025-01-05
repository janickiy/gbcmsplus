<?php
namespace mcms\common\widget;

use yii\base\Widget;

/**
 * Виджет массового обновления
 * Class MassUpdateWidget
 * @package mcms\common\widget
 */
class MassUpdateWidget extends Widget
{
  public $model;
  public $pjaxId;

  public $viewPath = 'mass_update';

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    return $this->render($this->viewPath, [
      'model' => $this->model,
      'pjaxId' => $this->pjaxId,
    ]);
  }
}