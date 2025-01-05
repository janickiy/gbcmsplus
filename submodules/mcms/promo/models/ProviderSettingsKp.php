<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\components\provider_instances_sync\api_drivers\ApiDriverInterface;
use mcms\promo\components\provider_instances_sync\dto\Error;
use mcms\promo\components\provider_instances_sync\dto\Instance;
use mcms\promo\components\provider_instances_sync\KpApiClient;
use mcms\promo\components\provider_instances_sync\requests\AuthRequest;
use mcms\promo\Module;
use Yii;

/**
 * Настройки хэндлера КП
 */
class ProviderSettingsKp extends AbstractProviderSettings
{
  use Translate;

  const LANG_PREFIX = 'promo.provider_settings.';

  public $api_url;
  public $hash;
  public $email;
  public $language;

  public $instanceId;
  public $providerId;
  public $streamId;

  /** @var KpApiClient */
  private $commonApi;

  /** @var KpApiClient */
  private $instanceApi;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['api_url', 'filter', 'filter' => function ($value) {
        return rtrim($value, '/');
      }],
      [['api_url', 'hash', 'email', 'language'], 'required'],
      [['api_url', 'hash', 'email'], 'string'],
      ['language', 'string', 'min' => 2, 'max' => 2],
      ['email', 'email'],
      ['api_url', 'url'],
      [['instanceId','providerId', 'streamId'], 'safe']
    ];
  }

  public function init()
  {
    parent::init();
    /** @var $promoModule Module*/
    $promoModule = Yii::$app->getModule('promo');

    $this->email or $this->email = $promoModule->getUserAuthEmail();
    $this->hash or $this->hash = $promoModule->getUserAuthHash();
  }


  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'api_url',
      'hash',
      'email',
      'language',
      'instanceId',
      'providerId',
      'streamId'
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeHints()
  {
    return [
      'language' => static::t('attribute-language_hint'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getViewName()
  {
    return 'kp';
  }

  /**
   * @param null $instanceHost
   * @return bool|KpApiClient
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function getKpApi($instanceHost = null)
  {
    /** @var $promoModule Module*/
    $promoModule = Yii::$app->getModule('promo');

    $constructorParams = [];

    if ($instanceHost) {
      // получить хост инстанца
      $constructorParams[0] = $instanceHost;
    }

    /** @var ApiDriverInterface $apiDriver */
    $apiDriver = Yii::$container->get(ApiDriverInterface::class, $constructorParams);
    $api = new KpApiClient($apiDriver);

    $authRequest = new AuthRequest();
    $authRequest->email = $promoModule->getUserAuthEmail();
    $authRequest->hash = $promoModule->getUserAuthHash();

    if (!$api->auth($authRequest)) {
      return false;
    }

    return $api;
  }

  /**
   * @return \mcms\promo\components\provider_instances_sync\dto\Error|\mcms\promo\components\provider_instances_sync\dto\Instance[]
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function getInstancesDropdown()
  {
    $api = $this->getKpApi();
    if (!$api) {
      return [];
    }

    $response = $api->getInstances();

    if ($response instanceof Error) {
      $this->addError('instanceId', 'Api error: ' . $response->message);
      return [];
    }

    return ArrayHelper::map($response, 'id', 'domain');
  }

  /**
   * @return array
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function getProvidersDropdown()
  {
    if (!$this->instanceId) {
      return [];
    }

    $api = $this->getKpApi();
    if (!$api) {
      return [];
    }

    $instances = $api->getInstances();

    if ($instances instanceof Error) {
      return [];
    }

    $instances = ArrayHelper::map($instances, 'id', function($item) {
      return $item;
    });

    $instance = isset($instances[$this->instanceId])
      ? $instances[$this->instanceId]
      : null
    ;

    if ($instance === null) {
      return [];
    }

    /** @var Instance $instance */

    $api = $this->getKpApi($instance->domain);
    $providers = $api->getProviders();
    if ($providers instanceof Error) {
      $this->addError('providerId', 'Api error: ' . $providers->message);

      return [];
    }

    return ArrayHelper::map($providers, 'id', 'name');
  }

  /**
   * @return array
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function getStreamsDropdown()
  {
    $api = $this->getKpApi();
    if (!$api) {
      return [];
    }

    $instances = $api->getInstances();

    if ($instances instanceof Error) {
      return [];
    }

    $instances = ArrayHelper::map($instances, 'id', function($item) {
      return $item;
    });

    $instance = isset($instances[$this->instanceId])
      ? $instances[$this->instanceId]
      : null
    ;

    if ($instance === null) {
      return [];
    }

    /** @var Instance $instance */

    $api = $this->getKpApi($instance->domain);
    $streams = $api->getStreams();
    if ($streams instanceof Error) {
      $this->addError('providerId', 'Api error: ' . $streams->message);

      return [];
    }

    return ArrayHelper::map($streams, 'id', function($stream) {
      return sprintf('#%d %s', $stream->id, $stream->name);
    });
  }
}