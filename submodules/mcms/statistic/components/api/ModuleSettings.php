<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 09.08.16
 * Time: 15:14
 */

namespace mcms\statistic\components\api;
use mcms\common\module\api\ApiResult;
use mcms\statistic\Module;
use Yii;

/**
 * Class ModuleSettings
 * @package mcms\statistic\components\api
 */
class ModuleSettings extends ApiResult
{
  /** @var  Module */
  private $module;

  /**
   * @param array $params
   */
  function init($params = [])
  {
    $this->module = Yii::$app->getModule('statistic');
  }

  /**
   * @return bool
   */
  public function isPostbackTransferPhone()
  {
    return $this->module->settings->getValueByKey(Module::SETTINGS_POSTBACK_TRANSFER_PHONE) ? : false;
  }

  /**
   * @return bool
   */
  public function isPostbackHashPhone()
  {
    return $this->module->settings->getValueByKey(Module::SETTINGS_POSTBACK_HASH_PHONE) ? : false;
  }

  /**
   * @return string
   */
  public function getHashSalt()
  {
    return $this->module->settings->getValueByKey(Module::SETTINGS_POSTBACK_HASH_SALT);
  }

  /**
   * @return int
   */
  public function getBuyoutMinutes()
  {
    return $this->module->settings->getValueByKey(Module::SETTINGS_BUYOUT_MINUTES);
  }

  /**
   * Дублировать постбеки
   * @return bool
   */
  public function isDuplicatePostback()
  {
    return (bool)$this->module->settings->getValueByKey(Module::SETTINGS_DUPLICATE_POSTBACK)
    && $this->getDuplicatePostbackUrl();
  }

  /**
   * Глобальный урл для дублирования постбеков
   * @return mixed|null
   */
  public function getDuplicatePostbackUrl()
  {
    return $this->module->settings->getValueByKey(Module::SETTINGS_DUPLICATE_POSTBACK_URL);
  }
}