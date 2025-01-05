<?php

namespace mcms\payments\components\paysystem_icons\wallet;


use yii\base\Object;

class BaseWalletIcon extends Object
{
  public $defaultIcon;
  public $defaultIconSrc;

  public $uniqueValue;

  public function getDefaultIcon()
  {
    return $this->defaultIcon;
  }

  public function getDefaultIconSrc()
  {
    return $this->defaultIconSrc;
  }

  public function getIcon()
  {
    return $this->defaultIcon;
  }

  public function getIconSrc()
  {
    return $this->defaultIconSrc;
  }
}