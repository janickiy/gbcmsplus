<?php


namespace mcms\promo\components;


use mcms\common\helpers\curl\Curl;
use mcms\promo\models\Domain;
use mcms\promo\Module;
use Yii;

/**
 * Помощник для микросервисов.
 *
 * Class ApiHandlersHelper
 * @package mcms\promo\components
 */
class ApiHandlersHelper
{
  const SECRET_KEY = 'ykUPzk4gYd5vsq59om91';

  const CONSOLE_FILE = 'console';
  const CONSOLE_CLEAR = 'cache/clear';
  const CONSOLE_INVALIDATE_TAGS = 'cache/invalidate-tags';
  const CONSOLE_FLUSH = 'cache/flush-all';
  const BANNER_GENERATE = 'banners/generate';
  const BANNER_GENERATE_BY_TEMPLATE = 'banners/generate-all';
  const TRAFFIC_BLOCK_CACHE_PREFIX = 'traffic_block_';

  /**
   * @const скапливаем кеш до того как отправить его в апи на очистку
   */
  const FLUSH_CACHE_INTERVAL = 30;

  /** @const в микросервисе с этим префиксом сохраняется кэш настроек rgk/settings */
  const RGK_SETTINGS_CACHE_PREFIX = 'RGKSETTINGS_';

  /** @var array */
  private static $moduleSettings;

  /** @var string */
  private static $consoleFile;

  /** @var string */
  private static $phpBinary;

  /** @var bool */
  private static $isConsole;

  /** @var Domain $domain */
  private static $domain;

  /**
   * @var array буфер для сброса кеша пачками
   */
  private static $cacheBuffer = [];

  /**
   * Очистить весь кеш микросервисов
   * Запускается только как консольная команда
   * @return bool
   */
  public static function cacheFlushAll()
  {
    self::getModuleSettings();

    if (self::isConsole()) {
      return self::sendConsole(self::CONSOLE_FLUSH);
    }

    return false;
  }

  /**
   * @param $key
   * @param $delimiter
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   * @return bool
   */
  public static function clearCache($key, $delimiter = '|')
  {
    self::getModuleSettings();

    if (is_array($key)) $key = implode($delimiter, $key);

    if (self::isConsole()) {
      return self::sendConsole(sprintf('%s "%s"', self::CONSOLE_CLEAR, $key));
    }

    return self::sendHTTP(['key' => $key]);
  }

  /**
   * @param string $tags Теги через запятую
   * @return bool
   */
  public static function invalidateTags($tags)
  {
    self::getModuleSettings();

    if (self::isConsole()) {
      return self::sendConsole(sprintf('%s "%s"', self::CONSOLE_INVALIDATE_TAGS, $tags));
    }

    return false;
  }

  /**
   * Сбрасывает кеш пачками
   * NOTICE
   * !!!!!!!!!!! после окончания работы скрипта который сбрасывает кеш !!!!!!!!!!!!!!!!!!!!!
   * !!!!!!!!!!! обязательно вызывать этот метод в последний раз с ключем NULL !!!!!!!!!!!!!
   * !!!!!!!!!!! чтобы он все что осталось в буфере обработал !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
   * @param string|null $key если null то не добавляет ключ а просто отправляем кеш на обработку в апи
   * @param string $delimiter
   */
  public static function bufferedClearCache($key, $delimiter = '|')
  {
    if ($key !== null) {
      self::$cacheBuffer[] = $key;
    }

    if ($key === null || count(self::$cacheBuffer) >= self::FLUSH_CACHE_INTERVAL) {
      self::clearCache(implode($delimiter, self::$cacheBuffer));

      self::$cacheBuffer = [];
    }
  }

  /**
   * @return array
   */
  private static function getModuleSettings()
  {
    if (!empty(self::$moduleSettings)) return self::$moduleSettings;

    $settings = Yii::$app->getModule('promo')->settings;

    return self::$moduleSettings = [
      'type' => $settings->getValueByKey(Module::SETTINGS_API_HANDLER_CLEAR_CACHE_TYPE),
      'consolePath' => $settings->getValueByKey(Module::SETTINGS_API_HANDLER_PATH),
      'urlPath' => $settings->getValueByKey(Module::SETTINGS_API_HANDLER_CLEAR_CACHE_URL_PATH)
    ];
  }

  /**
   * @return bool
   */
  private static function isConsole()
  {

    // Если уже проверяли, возвращаем
    if (!is_null(self::$isConsole)) {
      return self::$isConsole;
    }

    // Если тип не консольный
    if (self::$moduleSettings['type'] !== Module::SETTINGS_API_HANDLER_CLEAR_CACHE_TYPE_CONSOLE) {
      return self::$isConsole = false;
    }

    // Если не указан путь
    if (empty(self::$moduleSettings['consolePath'])) {
      Yii::warning('api handlers path not specified');
      return self::$isConsole = false;
    }

    self::$consoleFile = empty(self::$consoleFile)
      ? rtrim(self::$moduleSettings['consolePath'], '/') . '/' . self::CONSOLE_FILE
      : self::$consoleFile
    ;

    // Если исполняемого файла не нашлось
    if (!is_file(self::$consoleFile)) {
      Yii::warning('api handlers path is incorrect');
      return self::$isConsole = false;
    }

    // Если бинарь php не найден
    exec('command -v php', $phpBin);
    if (empty($phpBin)) {
      Yii::warning('php binary is not found');
      return self::$isConsole = false;
    }
    self::$phpBinary = current($phpBin);

    return self::$isConsole = true;
  }

  /**
   * @param $command
   * @return mixed
   */
  private static function sendConsole($command)
  {
    $console = sprintf('%s %s %s', self::$phpBinary, self::$consoleFile, $command);
    exec($console, $output);

    if (empty($output)) {
      Yii::error('api handler cache not cleared (response is empty)! Request: ' . $console);
      return false;
    }

    return true;
  }

  /**
   * @param $params
   * @return bool
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  private static function sendHTTP($params)
  {
    self::$domain = empty($domain) ? Domain::findOne(['is_system' => 1]) : self::$domain;

    if (!self::$domain) {
      Yii::error('System domains not found');
      return false;
    }

    if (!self::$domain->url) {
      Yii::error('Domain url not specified');
      return false;
    }

    $params = array_merge($params, [
      'secret' => self::SECRET_KEY
    ]);

    $curl = (new Curl([
      'url' => sprintf('%s%s?%s', self::$domain->url, self::$moduleSettings['urlPath'], http_build_query($params))
    ]));


    if (empty($curl->getResult())) {
      Yii::error('api handler cache not cleared (response is empty)! Curl info: ' . json_encode($curl->getCurlInfo()));
      return false;
    }

    return true;
  }

  /**
   * TODO: отрефакторить!!!
   * @param $params
   * @return bool
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  private static function sendBannerHTTP($params, $file)
  {
    self::$domain = empty($domain) ? Domain::getActiveSystemDomain() : self::$domain;

    if (!self::$domain) {
      Yii::error('System domains not found');
      return false;
    }

    if (!self::$domain->url) {
      Yii::error('Domain url not specified');
      return false;
    }

    $params = array_merge($params, [
      'secret' => self::SECRET_KEY
    ]);

    $curl = (new Curl([
      'url' => sprintf('%s%s?%s', self::$domain->url, '/banners/' . $file . '.php', http_build_query($params))
    ]));

    if (empty($curl->getResult())) {
      Yii::error('api handler not sent http (response is empty)! Curl info: ' . json_encode($curl->getCurlInfo()));
      return false;
    }

    return true;
  }

  public static function generateBanner($id)
  {
    self::getModuleSettings();

    if (self::isConsole()) {
      return self::sendConsole(sprintf('%s %s', self::BANNER_GENERATE, $id));
    }

    return self::sendBannerHTTP(['bannerId' => $id], 'id');
  }

  public static function generateBannerByTemplate($code = null)
  {
    self::getModuleSettings();

    if (self::isConsole()) {
      return self::sendConsole(sprintf('%s %s', self::BANNER_GENERATE_BY_TEMPLATE, $code));
    }

    return self::sendBannerHTTP(['templateCode' => $code], 'all');
  }

  /**
   * чистим кэш для настроек блокировки трафика
   * @param $userId
   */
  public static function clearTrafficBlockCache($userId)
  {
    self::clearCache(self::TRAFFIC_BLOCK_CACHE_PREFIX . $userId);
  }
}
