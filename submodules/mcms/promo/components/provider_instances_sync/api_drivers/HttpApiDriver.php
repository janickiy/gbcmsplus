<?php

namespace mcms\promo\components\provider_instances_sync\api_drivers;

use mcms\common\helpers\curl\Curl;
use mcms\promo\components\provider_instances_sync\dto\Error;
use mcms\promo\components\provider_instances_sync\dto\Instance;
use mcms\promo\components\provider_instances_sync\dto\Provider;
use mcms\promo\components\provider_instances_sync\dto\Stream;
use mcms\promo\components\provider_instances_sync\requests\AuthRequest;
use mcms\promo\components\provider_instances_sync\requests\CreateStreamRequest;
use mcms\promo\components\provider_instances_sync\response_parsers\InstancesResponseParser;
use mcms\promo\components\provider_instances_sync\response_parsers\ProvidersResponseParser;
use mcms\promo\components\provider_instances_sync\response_parsers\StreamResponseParser;
use mcms\promo\components\provider_instances_sync\response_parsers\StreamsResponseParser;
use Yii;
use yii\helpers\ArrayHelper;

class HttpApiDriver implements ApiDriverInterface
{
  const COMMON_URL = 'http://by.wap.group';
  /**
   * @var string
   */
  private $_instanceUrl;

  /**
   * @var string
   */
  private $_accessToken;

  /**
   * HttpApiDriver constructor.
   * @param null $instance
   */
  public function __construct($instance = null)
  {
    $this->_instanceUrl = $instance ?: self::COMMON_URL;
  }

  /**
   * @param $url
   * @param array $postParams
   * @param bool $isPost
   * @return mixed
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function sendRequest($url, array $postParams = [], $isPost = false)
  {
    $curl = new Curl([
      'isPost' => $isPost,
      'postFields' => $postParams,
      'url' => $url,
      'followRedirect' => false,
    ]);
    $curl->notUseProxy();

    $response = $curl->getResult();
    $info = $curl->getCurlInfo();

    $httpCode = ArrayHelper::getValue($info, 'http_code');

    if ($httpCode !== 200 && $httpCode !== 302) {
      Yii::error('Api failed: http_code=' . $httpCode . '; response=' . $response, __METHOD__);
      return null;
    }

    if (empty($response)) {
      Yii::error('Api failed: response is empty', __METHOD__);
      return null;
    }

    return $response;
  }

  /**
   * @param AuthRequest $request
   * @return mixed|string
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function auth(AuthRequest $request)
  {
    if (!$this->_accessToken = Yii::$app->cache->get(__CLASS__ . __FUNCTION__ . $this->_instanceUrl)) {

      $response = $this->sendRequest(
        $this->buildUrl('/api/v1/user/auth', false),
        $request->getRequestData(),
        true)
      ;

      $response = json_decode($response, true);
      $this->_accessToken = ArrayHelper::getValue($response, ['data', 'access_token']);

      Yii::$app->cache->set(__CLASS__ . __FUNCTION__ . $this->_instanceUrl, $this->_accessToken, 600);
    }

    return $this->_accessToken;
  }

  /**
   * @return Error|Stream[]
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function getStreams()
  {
    $response = $this->sendRequest($this->buildUrl('/api/v1/streams'));

    $response = json_decode($response, true);
    $responseParser = new StreamsResponseParser($response);
    if ($responseParser->isHasError()) {
      return $responseParser->getError();
    }

    return $responseParser->parse();
  }

  public function createStream(CreateStreamRequest $request)
  {
    $response = $this->sendRequest(
      $this->buildUrl('/api/v1/streams/create'),
      $request->getRequestData(),
      true
    );
    $response = json_decode($response, true);

    $responseParser = new StreamResponseParser($response);
    if ($responseParser->isHasError()) {
      return $responseParser->getError();
    }

    return $responseParser->parse();
  }

  /**
   * @return Error|Provider[]
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function getProviders()
  {
    $response = $this->sendRequest($this->buildUrl('/api/v1/providers'));

    $response = json_decode($response, true);

    $responseParser = new ProvidersResponseParser($response);
    if ($responseParser->isHasError()) {
      return $responseParser->getError();
    }

    return $responseParser->parse();
  }

  /**
   * @return Error|Instance[]
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function getInstances()
  {
    $response = $this->sendRequest($this->buildUrl('/api/v1/instances'));

    $response = json_decode($response, true);

    $responseParser = new InstancesResponseParser($response);
    if ($responseParser->isHasError()) {
      return $responseParser->getError();
    }

    return $responseParser->parse();
  }

  /**
   * @param $path
   * @param bool $withAuthToken
   * @param array $queryParams
   * @return string
   */
  private function buildUrl($path, $withAuthToken = true, array $queryParams = [])
  {
    if ($withAuthToken) {
      $queryParams['access-token'] = $this->_accessToken;
    }

    return sprintf('%s%s?%s', $this->_instanceUrl, $path, http_build_query($queryParams));
  }

  /**
   * @param int $streamId
   * @return boolean
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  public function testPostback($streamId)
  {
    $response = $this->sendRequest($this->buildUrl('/api/v1/postback/test', true, [
      'streamId' => $streamId
    ]), [], false);

    $response = json_decode($response, true);

    return ArrayHelper::getValue($response, 'success', false);
  }
}