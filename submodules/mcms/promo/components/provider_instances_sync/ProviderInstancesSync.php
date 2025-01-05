<?php

namespace mcms\promo\components\provider_instances_sync;

use mcms\promo\models\Provider;
use mcms\promo\models\ProviderSettingsKp;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ProviderInstancesSync
 */
class ProviderInstancesSync
{
  const PROVIDER_LANGUAGE = 'ru';

  protected $_currentInstances;
  protected $_newInstances;
  protected $accessToken;
  protected $instanceUrl;

  /**
   * @return bool
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   * @throws \mcms\common\helpers\curl\CurlInitException
   */
  public function run()
  {
    foreach ($this->getNewInstances() as $instance) {
      $this->saveProvider($instance);
    }

    return true;
  }


  /**
   * Сохранение провайдера в mcms
   * @param array $instance
   * @return bool
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  private function saveProvider($instance)
  {
    $url = ArrayHelper::getValue($instance, 'url');
    $domain = parse_url($url)['host'];
    $code = ArrayHelper::getValue($instance, 'code');
    $hash = ArrayHelper::getValue($instance, 'hash');
    $email = ArrayHelper::getValue($instance, 'email');

    $api = new KpApiClient($url);

    if (!$api->auth($instance)) {
      Yii::error('Api auth failed', __METHOD__);
      return false;
    }

    $stream = $this->createStreamIfNecessary($code, $api);
    if (!$stream) {
      Yii::error('Stream not created', __METHOD__);
      return false;
    }

    $kpSettings = new ProviderSettingsKp([
      'api_url' => $stream['url'],
      'hash' => $stream['hash'],
      'email' => $email,
      'language' => self::PROVIDER_LANGUAGE,
    ]);

    if ($api->testPostback($stream['id']) === false) {
      Yii::error(sprintf('Ошибка при тестировании постбека. Провайдер "%s"', $code), __METHOD__);
      return false;
    }

    $provider = new Provider([
      'name' => $domain,
      'code' => $code,
      'url' => $url,
      'status' => Provider::STATUS_ACTIVE,
      'handler_class_name' => Provider::HANDLER_KP,
      'settings' => $kpSettings,
      'secret_key' => $hash,
      'is_rgk' => true,
    ]);

    return $provider->save();
  }

  /**
   * Создает поток, если необходимо (Если нужного потока нет у инстанса)
   * @param $name
   * @param KpApiClient $api
   * @return array
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  private function createStreamIfNecessary($name, KpApiClient $api)
  {
    /** @var \mcms\promo\Module $module */
    $module = Yii::$app->getModule('promo');

    $streams = $api->getStreams();
    foreach ($streams as $stream) {
      $streamName = ArrayHelper::getValue($stream, 'name');
      if ($streamName === $name) {
        return $stream;
      }
    }

    $result = $api->createStream([
      'name' => $name,
      'postback_url' => $module->getPostbackUrl(),
      'trafficback_url' => $module->getTrafficbackUrl(),
      'complain_url' => $module->getComplainsUrl(),
      'secret_key' => $module->getKpSecretKey(),
      'postback_grouping' => true,
      'access-token' => $this->accessToken,
    ]);

    return $result;
  }

  /**
   * Список новых инстансов
   * @return array
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  private function getNewInstances()
  {
    if (empty($this->_newInstances)) {
      $instances = (new KpApiClient())->getInstances();

      $this->_newInstances = [];
      foreach ($instances as $instance) {
        if (!in_array(ArrayHelper::getValue($instance, 'code'), $this->getCurrentInstances(), true)) {
          $this->_newInstances[] = $instance;
        }
      }
    }

    return $this->_newInstances;
  }

  /**
   * Коды существующих инстансов
   * @return array
   */
  private function getCurrentInstances()
  {
    if (!isset($this->_currentInstances)) {
      $this->_currentInstances = Provider::find()->select(['code'])->column();
    }
    return $this->_currentInstances;
  }


}