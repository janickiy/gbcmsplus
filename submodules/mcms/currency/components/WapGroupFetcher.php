<?php

namespace mcms\currency\components;

use mcms\common\helpers\HttpAdapter;
use rgk\exchange\components\fetcher\AbstractFetcher;
use rgk\exchange\models\Currency;
use rgk\exchange\components\Currencies;
use Yii;
use yii\httpclient\Client;
use yii\httpclient\Response;

/**
 * Получает курсы из geo.wap.group
 *
 * Class WapGroupFetcher
 * @package mcms\currency\components
 */
class WapGroupFetcher extends AbstractFetcher
{
  const CACHE_KEY = 'wap-group-fetcher-cources';

  public $apiUrl;

  public $token;

  public $adapter;

  protected $accessToken;

  /** @var array */
  private $response = [];

  /**
   * WapGroupFetcher constructor.
   */
  public function __construct()
  {
    if (!$this->adapter) {
      $this->adapter = new HttpAdapter(['method' => 'get']);
    }
  }

  /**
   * @inheritdoc
   */
  public function getExchangerCourses()
  {
    if (!$this->apiUrl || !$this->token) {
      throw new \RuntimeException('Api Url and Token are required');
    }

    if (!$this->currencyCourses) {
      $this->currencyCourses = $this->getCurrencyCourses();
    }

    return $this->currencyCourses;
  }

  /**
   * @inheritdoc
   */
  public function getCode()
  {
    return 'wap_group_fetcher';
  }

  /**
   * Возвращает объект заполненый курсами
   * @return Currencies
   */
  protected function getCurrencyCourses()
  {
    $accessToken = $this->getAccessToken();

    $this->adapter->headers['Authorization'] = "Bearer $accessToken";

    $currencyCourses = new Currencies();
    $url = $this->apiUrl . '/currencies?expand=rate';

    do {
      $response = $this->adapter->getResponse($url, [], 10);
      $response->setFormat(Client::FORMAT_JSON);

      $data = $response->data;
      foreach ($data as $item) {
        $currencyCourses->addCurrency(new Currency(
          $item['id'],
          $item['code3l'],
          $item['rate']['to_rub'],
          $item['rate']['to_usd'],
          $item['rate']['to_eur']
        ));
      }

      // продолжаем запрашивать, пока есть ссылка на следующую страницу
      $links = $this->getLinks($response->headers->get('link'));
      $url = $links['next'] ?? null;

    } while ($url);

    return $currencyCourses;
  }

  public function getCountries()
  {
    // временно поставил токен для мгмп, так как у этого пользователя есть доступ к странам
    // надо пофиксить OP-13 для начала
    $this->token = 'ZcCXkJJfR4sjvu2HtnzPl_O8mBbHnsPi';
    $accessToken = $this->getAccessToken();

    $this->adapter->headers['Authorization'] = "Bearer $accessToken";

    $countries = [];
    $url = $this->apiUrl . '/countries';

    do {
      $response = $this->adapter->getResponse($url);
      $response->setFormat(Client::FORMAT_JSON);

      $countries = array_merge($countries, $response->data);

      // продолжаем запрашивать, пока есть ссылка на следующую страницу
      $links = $this->getLinks($response->headers->get('link'));
      $url = $links['next'] ?? null;

    } while ($url);

    return $countries;
  }

  public function getOperators()
  {
    // временно поставил токен для мгмп, так как у этого пользователя есть доступ к странам
    // надо пофиксить OP-13 для начала
    $this->token = 'ZcCXkJJfR4sjvu2HtnzPl_O8mBbHnsPi';
    $accessToken = $this->getAccessToken();

    $this->adapter->headers['Authorization'] = "Bearer $accessToken";

    $operators = [];
    $url = $this->apiUrl . '/operators';

    do {
      $response = $this->adapter->getResponse($url);
      $response->setFormat(Client::FORMAT_JSON);

      $operators = array_merge($operators, $response->data);

      // продолжаем запрашивать, пока есть ссылка на следующую страницу
      $links = $this->getLinks($response->headers->get('link'));
      $url = $links['next'] ?? null;

    } while ($url);

    return $operators;
  }

  /**
   * @return string|null
   */
  protected function getAccessToken()
  {
    $accessTokenCacheKey = self::CACHE_KEY . '-access_token';

    if (!$this->accessToken) {
      $this->accessToken = Yii::$app->cache->get($accessTokenCacheKey);
    }

    if (!$this->accessToken) {
      $url = $this->apiUrl . '/auth/token?auth-token=' . $this->token;

      $response = $this->adapter->getFormattedResponse($url);

      $this->accessToken = $response['access_token'];

      Yii::$app->cache->set($accessTokenCacheKey, $this->accessToken, $response['expires_in'] - time());
    }

    return $this->accessToken;
  }

  /**
   * Получает ссылки из хедера:
   *
   * link: <http://geo.wap.group/api/v1/currencies?expand=rate&page=4>; rel=self,...
   *
   * @param $header
   * @return array
   */
  protected function getLinks($header)
  {
    $rawLinks = explode(',', $header);
    $links = [];

    foreach($rawLinks as $rawLink) {
      preg_match('/^<(.*?)>[\s\S]+\=([a-z]+)$/', trim($rawLink), $matches);

      list(, $link, $rel) = $matches;
      $links[$rel] = $link;
    }

    return $links;
  }
}
