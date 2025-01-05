<?php

namespace mcms\payments\components\paysystem_icons\wallet;


class Card extends BaseWalletIcon
{
  const TYPE_VISA = 'visa';
  const TYPE_MASTERCARD = 'mc';

  public $iconVisa;
  public $iconMc;

  public $iconVisaSrc;
  public $iconMcSrc;

  protected $cardExpressions = [
    self::TYPE_VISA => '^4[0-9]{12}(?:[0-9]{3})?$',
    self::TYPE_MASTERCARD => '^5[1-5][0-9]{14}$',
  ];

  public function getIcon()
  {
    switch ($this->getTypeOfCard()) {
      case self::TYPE_VISA:
        return $this->iconVisa;
      case self::TYPE_MASTERCARD:
        return $this->iconMc;
    }

    return $this->defaultIcon;
  }

  public function getIconSrc()
  {
    switch ($this->getTypeOfCard()) {
      case self::TYPE_VISA:
        return $this->iconVisaSrc;
      case self::TYPE_MASTERCARD:
        return $this->iconMcSrc;
    }

    return $this->defaultIconSrc;
  }

  /**
   * @return string
   */
  public function getTypeOfCard()
  {
    if (!$this->uniqueValue) {
      return '';
    }
    foreach ($this->cardExpressions as $card => $pattern) {
      if (preg_match('/' . $pattern . '/', $this->uniqueValue)) {
        return $card;
      }
    }

    return '';
  }
}