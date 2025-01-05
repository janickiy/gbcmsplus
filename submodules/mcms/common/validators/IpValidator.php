<?php

namespace mcms\common\validators;

use Yii;
use yii\base\InvalidConfigException;

/**
 * todo можно удалить после обновления yii2.
 * Переопределили из-за некорректной ошибки @see https://github.com/yiisoft/yii2/issues/13976
 */
class IpValidator extends \yii\validators\IpValidator
{
  public function init()
  {
    try {
      parent::init();
    } catch (InvalidConfigException $e) {
      if ($this->message === null) {
        $this->message = Yii::t('yii', '{attribute} must be a valid IP address.');
      }
      if ($this->ipv6NotAllowed === null) {
        $this->ipv6NotAllowed = Yii::t('yii', '{attribute} must not be an IPv6 address.');
      }
      if ($this->ipv4NotAllowed === null) {
        $this->ipv4NotAllowed = Yii::t('yii', '{attribute} must not be an IPv4 address.');
      }
      if ($this->wrongCidr === null) {
        $this->wrongCidr = Yii::t('yii', '{attribute} contains wrong subnet mask.');
      }
      if ($this->noSubnet === null) {
        $this->noSubnet = Yii::t('yii', '{attribute} must be an IP address with specified subnet.');
      }
      if ($this->hasSubnet === null) {
        $this->hasSubnet = Yii::t('yii', '{attribute} must not be a subnet.');
      }
      if ($this->notInRange === null) {
        $this->notInRange = Yii::t('yii', '{attribute} is not in the allowed range.');
      }
    }
  }
}
