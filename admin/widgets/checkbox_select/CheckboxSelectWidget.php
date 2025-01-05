<?php

namespace admin\widgets\checkbox_select;

use admin\widgets\checkbox_select\assets\CheckboxSelectAsset;
use Yii;
use yii\base\Widget;

/**
 * Class CheckboxSelectWidget
 * @package admin\widgets\checkbox_select
 */
class CheckboxSelectWidget extends Widget
{

  /**
   * @var array Пример: ['Страна1' => [1 => 'operator1', 2 => 'operator2', 3 => 'operator3']]
   */
  public $data = [];

  public $inputName = 'elements[]';
  /**
   * @inheritdoc
   */
  public function run()
  {
    CheckboxSelectAsset::register($this->view);

    return $this->render('index', ['groupedElements' => $this->data, 'inputName' => $this->inputName]);
  }
}