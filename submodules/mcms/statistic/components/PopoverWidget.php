<?php

namespace mcms\statistic\components;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Class PopoverWidget
 * @package mcms\statistic\components
 */
class PopoverWidget extends Widget
{

  /** @var array */
  public $strings;
  /** @var string */
  public $content;
  /** @var string */
  public $className;
  /** @var string */
  public $title;
  /**
   * @var string разрешение, позволяющее показывать popover
   */
  private $_permission = 'CanViewCorrectionCalculation';

  /**
   *  Подключение JS
   */
  public function init() {
    $js = <<<JS
    $(function () {
      $('[data-toggle="popover"]').popover({ html : true });
    });
JS;

    Yii::$app->getView()->registerJs($js);
  }

  /**
   * @return string
   */
  public function run()
  {
    return $this->canViewPopover()
      ? Html::button($this->content, [
          'class' => $this->className,
          'data-toggle' => 'popover',
          'title' => $this->title,
          'data-content' => implode('<br>', $this->strings)
        ])
      : $this->content;
  }

  /**
   * Проверка прав на показ popover
   * @return bool
   */
  private function canViewPopover()
  {
    return ($this->_permission && !Yii::$app->user->can($this->_permission)) ? false : true;
  }

}
