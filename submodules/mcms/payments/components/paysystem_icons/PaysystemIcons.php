<?php

namespace mcms\payments\components\paysystem_icons;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\paysystem_icons\wallet\BaseWalletIcon;
use Yii;
use yii\base\Component;

/**
 * Class PaysystemIcons
 * @package mcms\payments\components\paysystem_icons
 */
class PaysystemIcons extends Component
{
  public $iconWidgets = [];

  /**
   * Возвращает отрендеренную иконку для указанного типа ПС
   *
   * @param string $class
   * @param string $uniqueValue
   * @return string
   */
  public function getIcon($class, $uniqueValue = '')
  {
    $icon = $this->getIconObject($class, $uniqueValue);

    return $icon ? $icon->getIcon() : '';
  }

  /**
   * Возвращает отрендеренную дефолтную иконку для указанного типа ПС
   *
   * @param string $class
   * @return string
   */
  public function getDefaultIcon($class)
  {
    $icon = $this->getIconObject($class);

    return $icon ? $icon->getDefaultIcon() : '';
  }

  /**
   * Возвращает ссылку на иконку
   *
   * @param $class
   * @param string $uniqueValue
   * @return string
   */
  public function getIconSrc($class, $uniqueValue = '')
  {
    $icon = $this->getIconObject($class, $uniqueValue);

    return $icon ? $icon->getIconSrc() : '';
  }

  /**
   * Возвращает ссылку на дефолтную иконку
   *
   * @param $class
   * @return string
   */
  public function getDefaultIconSrc($class)
  {
    $icon = $this->getIconObject($class);

    return $icon ? $icon->getDefaultIconSrc() : '';
  }

  /**
   * @param $class
   * @param string $uniqueValue
   * @return BaseWalletIcon|null
   */
  protected function getIconObject($class, $uniqueValue = '')
  {
    $widgetConfig = ArrayHelper::getValue($this->iconWidgets, $class);

    if (!$widgetConfig || empty($widgetConfig['class'])) {
      Yii::error('Не указан класс виджета рендера иконки');

      return null;
    }
    $uniqueValue && $widgetConfig['uniqueValue'] = $uniqueValue;

    return Yii::createObject($widgetConfig);
  }
}