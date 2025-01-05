<?php
namespace mcms\statistic\components\traffic_generator;

use mcms\common\helpers\curl\Curl;
use mcms\common\output\OutputInterface;
use mcms\common\traits\LogTrait;
use mcms\statistic\models\KpResult;
use yii\helpers\ArrayHelper;

/**
 * Абстрактный генератор, просто общие методы.
 */
abstract class AbstractGenerator
{

  use LogTrait;

  const TRANS_ID_PREFIX = 'traf_generated_';

  /** @var GeneratorConfig  */
  protected $cfg;

  private static $userAgents = [
    'Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Lumia 920)',
    'Mozilla/5.0 (SymbianOS/9.4; Series60/5.0 Nokia5800d-1/60.0.003; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/533.4 (KHTML, like Gecko) NokiaBrowser/7.3.1.33 Mobile Safari/533.4',
    'Opera/9.80 (MTK; Nucleus; U; vi-VN) Presto/2.4.18 Version/10.00',
    'Opera/8.01 (J2ME/MIDP; Opera Mini/3.1.10423/1724; en; U; ssr)',
    'Mozilla/5.0 (iPod; U; CPU iPhone OS 5_1_1 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3',
    'Opera/9.80 (iPad; Opera Mini/7.0.3/28.2197; U; ru) Presto/2.8.119 Version/11.10',
    'Mozilla/5.0 (BlackBerry; U; BlackBerry 9380; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.523 Mobile Safari/534.11+',
    'Mozilla/5.0 (Linux; U; Android 4.2.2; en-gb; GT-P5210 Build/JDQ39) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30',
    'Mozilla/5.0 (Linux; U; Android 4.1.1; en-us; SCH-I605 Build/JRO03C) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
    'Mozilla/5.0 (Linux; U; Linux mips; en) AppleWebKit/531.2+ (KHTML, like Gecko) Version/4.1.4 Safari/531.2+',
  ];

  /**
   * AbstractGenerator constructor.
   * @param GeneratorConfig $config
   */
  public function __construct(GeneratorConfig $config)
  {
    $this->cfg = $config;
  }

  /**
   * Запуск генератора
   */
  abstract public function execute();

  /**
   * @param $url
   * @param array $params
   * @param array $post
   * @return bool|mixed
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   * @throws \mcms\common\helpers\curl\CurlInitException
   */
  protected function sendRequest($url, array $params = [], array $post = [])
  {
    $urlWithParam = $params ? $url . '?' . http_build_query($params) : $url;
    $curl = (new Curl([
      'url' => $urlWithParam,
      'isReturnTransfer' => true,
      'timeout' => 60 // ждём минуту.
    ]))->notUseProxy();

    $curl->userAgent = self::$userAgents[array_rand(self::$userAgents, 1)];

    if (!empty($post)) {
      $curl->httpHeader = [
        'Content-Type: application/x-www-form-urlencoded',
      ];
      $curl->isPost = true;
      $curl->postFields = $post;
    }

    $result = $curl->getResult();
    $error = $curl->getError();
    if ($error) {
      $this->log("Error: $error", [OutputInterface::BREAK_AFTER]);
    }

    return $result;
  }

  /**
   * Отправка ПБ в приемщик
   * @param array $conversions
   * @param string $handlerPath
   * @return int|null
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   * @throws \mcms\common\helpers\curl\CurlInitException
   */
  protected function sendConversions($conversions, $handlerPath = '/kp')
  {
    $noticeTime = time();
    $authParams = ['notice_time' => $noticeTime, 'verify_token' => md5($this->cfg->kpSecret . '-' . $noticeTime)];
    $result = $this->sendRequest(
      $this->cfg->pbHandlerUrl . $handlerPath,
      $authParams,
      ['postback_data' => json_encode($conversions)]
    );

    if (empty($result)) {
      $this->log('KP HANDLER RESPONSE IS EMPTY', [OutputInterface::BREAK_AFTER]);
      return null;
    }

    $decoded = json_decode($result, true);

    if ($decoded === null) {
      $this->log("KP HANDLER RESPONSE IS NOT JSON! CHECK IT:\n" . print_r($result, true), [OutputInterface::BREAK_AFTER]);
      return null;
    }

    if (!is_array($decoded)) {
      $this->log("KP HANDLER RESPONSE IS NOT ARRAY! CHECK IT:\n" . print_r($result, true), [OutputInterface::BREAK_AFTER]);
      return null;
    }

    if (!isset($decoded['postback_data'])) {
      $this->log("KP HANDLER RESPONSE HAS NO KEY postback_data! CHECK IT:\n" . print_r($result, true), [OutputInterface::BREAK_AFTER]);
      return null;
    }

    $data = ArrayHelper::getValue($decoded, 'postback_data', []);

    if (empty($data)) {
      $this->log("KP HANDLER postback_data IS EMPTY! CHECK IT:\n" . print_r($result, true), [OutputInterface::BREAK_AFTER]);
      return null;
    }

    if (!is_array($data)) {
      $this->log("KP HANDLER postback_data IS NOT ARRAY! CHECK IT:\n" . print_r($result, true), [OutputInterface::BREAK_AFTER]);
      return null;
    }

    $models = KpResult::getModels($data);

    return (int) count(array_filter($models, function (KpResult $model) {
      return (int)$model->status === KpResult::STATUS_OK;
    }));
  }

  /**
   * Вернуть число с погрешностью в пределах $this->cfg->inaccuracyPercent.
   * @param float $amount Число, которое надо сделать менее точным в пределах $this->cfg->inaccuracyPercent
   * @return float
   */
  protected function randomizeWithInacurracy($amount)
  {
    if (!$this->cfg->inaccuracyPercent) {
      return $amount;
    }

    $delta = $amount * $this->cfg->inaccuracyPercent / 100;

    // умножим на 100, чтобы в итоге можно было после рандома получить дробное число, а не только int
    $mDelta = $delta * 100;
    $mAmount = $amount * 100;

    return mt_rand($mAmount - $mDelta, $mAmount + $mDelta) / 100;
  }
}
