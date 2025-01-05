<?php

namespace mcms\statistic\components\postbacks;

use mcms\common\helpers\curl\Curl;
use mcms\common\helpers\curl\CurlHelperInterface;
use mcms\common\helpers\curl\FakeCurl;
use mcms\statistic\components\api\ModuleSettings;
use mcms\statistic\components\events\PostbackEvent;
use mcms\statistic\models\Postback;
use mcms\statistic\Module;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Sender отправляет постбеки
 * @package mcms\statistic\components\postbacks
 */
class Sender extends Component
{
  const TYPE_SUBSCRIPTION = 1;
  const TYPE_REBILL = 2;
  const TYPE_ONETIME_SUBSCRIPTION = 3;
  const TYPE_SUBSCRIPTION_OFF = 4;
  const TYPE_SUBSCRIPTION_SELL = 5;
  const TYPE_COMPLAIN = 6;

  /**
   * @var Module
   */
  protected $module;

  /**
   * @var
   */
  public $maxAttempts;

  /**
   * @var
   */
  public $timeFrom;

  /**
   * @var
   */
  public $timeTo;

  /**
   * @var
   */
  public $startTime;

  /**
   * Перевадать номер телефона в постбеках
   * @var bool
   */
  public $phoneTransferEnabled;

  /**
   * Передавать хэш номера телефона в постбеках
   * @var bool
   */
  public $hashPhoneTransferEnabled;

  /**
   * Соль хэша номера телеофна
   * @var string
   */
  public $hashSalt;

  /**
   * @var bool
   */
  public $duplicatePostbackUrl;

  /**
   * @var PostbackEvent
   */
  protected $event;

  /**
   * Объект для работы с данными
   * @var PostbackFetcherInterface|Object|DbFetcher
   */
  public $fetcher;

  /**
   * @var int
   */
  protected $batchSize = 100;

  /**
   * @var array эти значения записываются в GET параметр, например type=sub
   */
  protected static $typeNames = [
    self::TYPE_SUBSCRIPTION => 'on',
    self::TYPE_REBILL => 'rebill',
    self::TYPE_ONETIME_SUBSCRIPTION => 'sell',
    self::TYPE_SUBSCRIPTION_OFF => 'off',
    self::TYPE_SUBSCRIPTION_SELL => 'sell',
    self::TYPE_COMPLAIN => 'complain'
  ];

  /**
   * Тип постбеков
   * @var int
   */
  public $type;

  /**
   * Выводить лог
   * @var bool
   */
  public $showLog = false;

  /**
   * Несохраненные данные, которые не удалось отправить
   * @var array
   */
  protected $failed = [];

  /**
   * @var CurlHelperInterface|Object
   */
  protected $curlClass;

  /**
   * @var array
   */
  protected $hitInformation = [];

  /**
   * @var bool
   */
  public $isDummyExec = false;

  /**
   * Initializes the object.
   * This method is invoked at the end of the constructor after the object is initialized with the
   * given configuration.
   */
  public function init()
  {
    parent::init();

    $this->event = new PostbackEvent();

    /** @var Module $module */
    $module = Yii::$app->getModule('statistic');

    /** @var ModuleSettings $moduleSettingsApi */
    $moduleSettingsApi = $module->api('moduleSettings');

    $this->phoneTransferEnabled === null && $this->phoneTransferEnabled = $moduleSettingsApi->isPostbackTransferPhone();
    $this->hashPhoneTransferEnabled === null && $this->hashPhoneTransferEnabled  = $moduleSettingsApi->isPostbackHashPhone();
    $this->hashSalt === null && $this->hashSalt = $moduleSettingsApi->getHashSalt();
    $this->duplicatePostbackUrl === null && $this->duplicatePostbackUrl = $moduleSettingsApi->getDuplicatePostbackUrl();

    $this->maxAttempts === null && $this->maxAttempts = $module->settings->getValueByKey(Module::SETTINGS_POSTBACK_MAX_ATTEMPTS) ?: 3;

    $days = $module->settings->getValueByKey(Module::SETTINGS_POSTBACK_MAX_DAYS) ?: 3;
    if ($this->timeFrom === null) {
      $this->timeFrom = strtotime('-' . $days . ' days');
    }
    $this->timeTo === null && $this->timeTo = time();

    $this->fetcher->hasProperty('maxAttempts') && $this->fetcher->maxAttempts = $this->maxAttempts;

    $this->curlClass = $this->isDummyExec ? FakeCurl::class : Curl::class;
  }

  /**
   * Обрабатывает постбеки
   */
  public function run()
  {
    $this->begin();

    if ($this->fetcher === null) {
      throw new InvalidParamException('fetcher is not set');
    }

    if ($this->fetcher->isDuplicatePostback && empty($this->duplicatePostbackUrl)) {
      throw new InvalidParamException('isDuplicatePostback=1 but duplicatePostbackUrl is not set');
    }

    $count = $this->fetcher->getCount();
    $this->stdout('Number of postbacks for send: ' . $count);

    $this->event->count = $count;

    if (!$count) {
      $this->end(false);

      return;
    }
    foreach ($this->fetcher->batch($this->batchSize) as $rows) {
      $batchData = [];

      foreach ($rows as $row) {
        $dataParams = $this->getDataParams($row);

        $isComplain = $this->type == self::TYPE_COMPLAIN;
        $url = $this->getPostbackUrl($row, $dataParams, $isComplain);

        // Если не получили URL для постбека и это жалоба не отправляем. В другом случае возможна отправка дубля
        if ($isComplain && !$url) {
          continue;
        }

        $row['fail_attempt'] = (int)ArrayHelper::getValue($row, 'fail_attempt', 0);
        $isDuplicate = $row['is_duplicate'] ? (int)$row['is_duplicate'] : null;
        $row['is_duplicate'] = $isDuplicate;
        // отправляем дубль постбека, если необходимо
        $shouldSendDuplicatePb = $this->isShouldSendDuplicatePb($row);
        if ($shouldSendDuplicatePb) {
          $httpStatus = $this->sendDuplicatePostback($dataParams);
          $status = $httpStatus === 200 ? 1 : 0;

          // если не удалось отправить и было мало попыток, добавляем в неудачные
          if (!$status) {
            $row['fail_attempt']++;
            $this->failed[] = $row;
          }

          $batchData[] = [
            ArrayHelper::getValue($row, 'hit_id'), //hit_id
            $this->type == self::TYPE_SUBSCRIPTION ? ArrayHelper::getValue($row, 'id') : null, // subscription_id
            $this->type == self::TYPE_REBILL ? ArrayHelper::getValue($row, 'id') : null, // subscription_rebill_id
            $this->type == self::TYPE_ONETIME_SUBSCRIPTION ? ArrayHelper::getValue($row, 'id') : null, // onetime_subscription_id
            $this->type == self::TYPE_SUBSCRIPTION_OFF ? ArrayHelper::getValue($row, 'id') : null, // subscription_off_id
            $this->type == self::TYPE_SUBSCRIPTION_SELL ? ArrayHelper::getValue($row, 'id') : null, // sold_subscription_id
            $this->type == self::TYPE_COMPLAIN ? ArrayHelper::getValue($row, 'id') : null, // complain_id
            ArrayHelper::getValue($row, 'source_id'), // source_id
            $status, // status
            (int)$row['fail_attempt'], // errors. Если не приводить к инту, то на NULL ругаться будет, т.к. поле NOT NULL
            $httpStatus, // status_code
            $this->duplicatePostbackUrl, // url
            Postback::DUPLICATE, //дубль постбека
            json_encode($dataParams), // data
            time(), // time
            time() // last_time
          ];
        };

        $shouldSendOriginalPb = $this->isShouldSendOriginalPb($url, $row);
        if (!$isDuplicate && $shouldSendOriginalPb) {
          $curl = $this->getCurl($url);
          $curl->verifyCertificate = false;
          $curl->getResult();

          $httpStatus = ArrayHelper::getValue($curl->curlInfo, 'http_code');
          $status = $httpStatus === 200 ? 1 : 0;

          // если не удалось отправить и было мало попыток, добавляем в неудачные
          if (!$status) {
            $row['fail_attempt']++;
            $this->failed[] = $row;
          }

          $batchData[] = [
            ArrayHelper::getValue($row, 'hit_id'), //hit_id
            $this->type == self::TYPE_SUBSCRIPTION ? ArrayHelper::getValue($row, 'id') : null, // subscription_id
            $this->type == self::TYPE_REBILL ? ArrayHelper::getValue($row, 'id') : null, // subscription_rebill_id
            $this->type == self::TYPE_ONETIME_SUBSCRIPTION ? ArrayHelper::getValue($row, 'id') : null, // onetime_subscription_id
            $this->type == self::TYPE_SUBSCRIPTION_OFF ? ArrayHelper::getValue($row, 'id') : null, // subscription_off_id
            $this->type == self::TYPE_SUBSCRIPTION_SELL ? ArrayHelper::getValue($row, 'id') : null, // sold_subscription_id
            $this->type == self::TYPE_COMPLAIN ? ArrayHelper::getValue($row, 'id') : null, // complain_id
            ArrayHelper::getValue($row, 'source_id'), // source_id
            $status, // status
            (int)$row['fail_attempt'], // errors. Если не приводить к инту, то на NULL ругаться будет, т.к. поле NOT NULL
            $httpStatus, // status_code
            $url, // url
            Postback::ORIGINAL, //дубль постбека
            json_encode($dataParams), // data
            time(), // time
            time() // last_time
          ];
        }
      }

      $this->insert($batchData);
    }

    $this->end(true);
  }

  /**
   * Проверяет, должен ли постбек быть отправлен
   *
   * @param $failAttempt
   * @return bool
   */
  protected function isMaxAttemptsNotReached($failAttempt)
  {
    return $this->maxAttempts > $failAttempt;
  }

  /**
   * @param array $row
   * @return bool
   */
  protected function isShouldSendDuplicatePb($row)
  {
    return $this->isDuplicatePostback()
      && $this->isMaxAttemptsNotReached($row['fail_attempt'])
      && ($row['is_duplicate'] === null || $row['is_duplicate'] === 1);
  }


  /**
   * @param string $url
   * @param array $row
   * @return bool
   */
  protected function isShouldSendOriginalPb($url, $row)
  {
    $shouldSendOriginalPb = $this->isMaxAttemptsNotReached($row['fail_attempt'])
      && ($row['is_duplicate'] === null || $row['is_duplicate'] === 0);

    if (!$url) {
      $shouldSendOriginalPb = false;
    }

    if ($this->type === self::TYPE_SUBSCRIPTION && !ArrayHelper::getValue($row, 'is_notify_subscribe')) {
      $shouldSendOriginalPb = false;
    }

    if ($this->type === self::TYPE_REBILL && !ArrayHelper::getValue($row, 'is_notify_rebill')) {
      $shouldSendOriginalPb = false;
    }

    if ($this->type === self::TYPE_ONETIME_SUBSCRIPTION && !ArrayHelper::getValue($row, 'is_notify_cpa')) {
      $shouldSendOriginalPb = false;
    }

    if ($this->type === self::TYPE_SUBSCRIPTION_OFF && !ArrayHelper::getValue($row, 'is_notify_unsubscribe')) {
      $shouldSendOriginalPb = false;
    }

    if ($this->type === self::TYPE_SUBSCRIPTION_SELL && !ArrayHelper::getValue($row, 'is_notify_cpa')) {
      $shouldSendOriginalPb = false;
    }

    return $shouldSendOriginalPb;
  }

  /**
   * Отправляет данные в базу
   * @param $batchData
   */
  protected function insert($batchData)
  {
    if (!$batchData) {
      return;
    }

    $sql = Yii::$app->db->createCommand()->batchInsert('postbacks', [
      'hit_id',
      'subscription_id',
      'subscription_rebill_id',
      'onetime_subscription_id',
      'subscription_off_id',
      'sold_subscription_id',
      'complain_id',
      'source_id',
      'status',
      'errors',
      'status_code',
      'url',
      'is_duplicate',
      'data',
      'time',
      'last_time'
    ], $batchData)->getSql();

    $command = Yii::$app->db->createCommand(
      "$sql ON DUPLICATE KEY UPDATE 
      errors = VALUES(errors), 
      status = VALUES(status), 
      status_code = VALUES(status_code),
      url = VALUES(url),
      last_time = VALUES(last_time), 
      data = VALUES(data)"
    );

    $command->execute();
    $this->stdout(count($batchData) . ' postbacks were saved');
  }

  /**
   * Действия перед отправкой постбеков
   */
  protected function begin()
  {
    $this->startTime = time();

    $this->event->type = self::$typeNames[$this->type];
    $this->event->startTime = $this->startTime;
    $this->event->timeFrom = $this->timeFrom;
    $this->event->maxAttempts = $this->maxAttempts;

    $this->stdout('Postback processing started');
    $this->stdout('Type: ' . self::$typeNames[$this->type]);
  }

  /**
   * Действия после отправки постбеков
   * @param $success
   */
  protected function end($success)
  {
    $endTime = time();
    $this->stdout('Processing of postbacks completed in  ' . ($endTime - $this->startTime) . ' seconds');
    $this->stdout('Number of failed postbacks:' . count($this->failed));
    $this->event->success = $success;
    $this->event->endTime = $endTime;
    $this->event->trigger();
  }

  /**
   * Возвращает неудачные постбеки
   * @return array
   */
  public function getFailed()
  {
    return $this->failed;
  }

  /**
   * @param $row
   * @param $params
   * @param bool $isComplain для жалоб отправляем на complains_postback_url
   * @return string
   */
  protected function getPostbackUrl($row, $params, $isComplain = false)
  {
    $postbackUrl = $row['use_global_postback_url'] ? $row['global_postback_url'] : $row['postback_url'];
    if ($isComplain) {
      $postbackUrl = $row['use_complains_global_postback_url'] ? $row['complains_postback_url'] : '';
    }

    $hitInfo = $this->getHitInformation(ArrayHelper::getValue($row, 'hit_id'));
    $getParams = ArrayHelper::getValue($hitInfo, 'get_params', '');

    $url = $this->replaceParams($postbackUrl, $params);

    if ($getParams && $row['send_all_get_params_to_pb']) {
      $delimiter = mb_strpos($url, '?') === false ? '?' : '&';

      $url .= $delimiter . $getParams;
    }

    return $url;
  }

  /**
   * @param $row
   * @return array
   */
  protected function getDataParams($row)
  {
    //TRICKY получаем из hit_params метки т.к ыearch_subscriptions кроном может не обновиться при отправке через ребит
    $hitInfo = $this->getHitInformation(ArrayHelper::getValue($row, 'hit_id'));

    $hasSum = in_array($this->type, [self::TYPE_REBILL, self::TYPE_ONETIME_SUBSCRIPTION, self::TYPE_SUBSCRIPTION_SELL], true);

    $params = [
      '{user_id}' => urlencode(ArrayHelper::getValue($row, 'user_id')),
      '{stream_id}' => urlencode(ArrayHelper::getValue($row, 'stream_id')),
      '{link_id}' => urlencode(ArrayHelper::getValue($row, 'source_id')),
      '{link_name}' => urlencode(ArrayHelper::getValue($row, 'source_name')),
      '{link_hash}' => urlencode(ArrayHelper::getValue($row, 'source_hash')),
      '{subid1}' => urlencode(ArrayHelper::getValue($hitInfo, 'subid1')),
      '{subid2}' => urlencode(ArrayHelper::getValue($hitInfo, 'subid2')),
      '{cid}' => urlencode(ArrayHelper::getValue($hitInfo, 'cid')),
      '{notice_time}' => urlencode(time()),
      '{notice_date}' => urlencode(date('Y-m-d H:i:s', time())),
      '{action_time}' => urlencode($this->getActionTime($row)),
      '{action_date}' => urlencode(date('Y-m-d H:i:s', $this->getActionTime($row))),
      '{type}' => $this->getTypeName(),
      '{subscription_id}' => urlencode(ArrayHelper::getValue($row, 'subscription_id')),
      '{operator_id}' => urlencode(ArrayHelper::getValue($row, 'operator_id')),
      '{landing_id}' => urlencode(ArrayHelper::getValue($row, 'landing_id')),
      '{description}' => urlencode(ArrayHelper::getValue($row, 'description')),
      '{sum_rub}' => $hasSum ? urlencode($this->getSum($row, 'rub')) : '',
      '{sum_usd}' => $hasSum ? urlencode($this->getSum($row, 'usd')) : '',
      '{sum_eur}' => $hasSum ? urlencode($this->getSum($row, 'eur')) : '',
      '{rebill_id}' => $this->type === self::TYPE_REBILL ? urlencode(ArrayHelper::getValue($row, 'id')) : '',
    ];

    if ($this->phoneTransferEnabled) {
      $params['{msisdn}'] = urlencode(ArrayHelper::getValue($row, 'phone'));
    }
    if ($this->hashPhoneTransferEnabled) {
      $options = [
        'cost' => 11,
        'salt' => $this->hashSalt,
      ];
      $params['{abonent_id}'] = password_hash(ArrayHelper::getValue($row, 'phone'), PASSWORD_BCRYPT, $options);
    }

    if ($this->isDuplicatePostback()
      && $currency = ArrayHelper::getValue($row, 'currency')
    ) {
      $params['{sum}'] = urlencode($this->getSum($row, $currency));
    }

    return $params;
  }

  /**
   * Информация по хиту
   * @param $hitId
   * @return array|bool
   */
  protected function getHitInformation($hitId)
  {
    if (array_key_exists($hitId, $this->hitInformation)) {
      return $this->hitInformation[$hitId];
    }

    $this->hitInformation[$hitId] = (new Query())
      ->select([
        'subid1',
        'subid2',
        'get_params',
      ])
      ->from('hit_params')
      ->where(['hit_id' => $hitId])
      ->one();

    // Вытаскиваем cid из get_params, чтобы вставить в постбек
    parse_str($this->hitInformation[$hitId]['get_params'], $getParams);
    $this->hitInformation[$hitId]['cid'] = ArrayHelper::getValue($getParams, 'cid');

    return $this->hitInformation[$hitId];
  }

  /**
   * @param $row
   * @return int
   */
  protected function getActionTime($row)
  {
    return (int)ArrayHelper::getValue($row, 'time');
  }

  /**
   * @return string
   */
  protected function getTypeName()
  {
    return self::$typeNames[$this->type];
  }

  /**
   * @param $row
   * @param $currency
   * @return float|string
   */
  protected function getSum($row, $currency)
  {
    return (float)ArrayHelper::getValue($row, 'profit_' . $currency);
  }

  /**
   * @param string $string
   */
  public function stdout($string)
  {
    $this->event->stdout .= $string . "\n";

    if ($this->showLog) {
      Yii::info($string . "\n");
    }
  }

  /**
   * @return bool
   */
  private function isDuplicatePostback()
  {
    return $this->fetcher->isDuplicatePostback && $this->type !== self::TYPE_COMPLAIN;
  }

  /**
   * @param array $dataParams
   * @return string http code
   */
  protected function sendDuplicatePostback(array $dataParams)
  {
    $url = $this->replaceParams($this->duplicatePostbackUrl, $dataParams);
    $curl = $this->getCurl($url);
    $curl->verifyCertificate = false;
    $curl->getResult();

    return ArrayHelper::getValue($curl->curlInfo, 'http_code');
  }

  /**
   * @param $url
   * @param $params
   * @return string
   */
  protected function replaceParams($url, $params)
  {
    return strtr($url, $params);
  }

  /**
   * @param $url
   * @return mixed
   */
  protected function getCurl($url)
  {
    $curlClass = $this->curlClass;

    return new $curlClass(['url' => $url]);
  }
}
