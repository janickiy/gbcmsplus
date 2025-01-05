<?php

namespace mcms\payments\models\export;

use Yii;
use ZipArchive;
use yii\base\Object;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use mcms\common\traits\Translate;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\models\wallet\Wallet;

/**
 * UserPaymentExport генерирует архивы с выплатами
 * @property-read string $prefix
 * @property-read string $filename
 */
class UserPaymentExport extends Object
{
  use Translate;

  const LANG_PREFIX = 'payments.export.';
  const EXPORT_PATH = '@webroot/downloads/payments/export/';
  const EXPORT_URL = '@web/downloads/payments/export/';

  /**
   *
   * @var UserPaymentExportForm
   */
  private $_filename;
  public $exportForm;
  private $_startTime;
  private $_countExported;

  /**
   * Генерируем файлы выплат
   *
   * @throws InvalidParamException
   * @return string путь до архива
   */
  public function export()
  {
    if ($this->exportForm === null || !($this->exportForm instanceof UserPaymentExportForm)) {
      throw new InvalidParamException();
    }

    $this->_countExported = 0;
    $this->_startTime = microtime(true);

    $this->clearPrevArchives();
    $tempFiles = [];

    // Группируем платежки в ожидании по кошелькам и валютам
    foreach ($this->getQuery()->each() as $payment) {
      // Получение файла с выплатами
      $walletTypeId = $payment->wallet_type;
      if (!array_key_exists($walletTypeId, $tempFiles) || !array_key_exists($payment->currency, $tempFiles[$walletTypeId])) {
        $tempFiles[$walletTypeId][$payment->currency] = tmpfile();
      }

      // Получение кошелька и параметров
      $walletClass = Wallet::getWalletsClass($payment->wallet_type);
      list($params, $delimiter, $enclosure) = $walletClass::getExportRowParameters($payment);

      // Запись
      fputcsv($tempFiles[$walletTypeId][$payment->currency], $params, $delimiter, $enclosure);

      $this->_countExported++;
    }
;
    $this->recreatePath();
    $zipFile = new ZipArchive();
    $zipFile->open(Yii::getAlias(self::EXPORT_PATH . $this->filename), ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // Получаем содержимое временных файлов и добавляем их в архив
    foreach ($tempFiles as $walletTypeId => $tempFilesByCurrency) {
      foreach ($tempFilesByCurrency as $currency => $tempFile) {
        $walletClass = Wallet::getWalletsClass($walletTypeId);
        rewind($tempFile);
        $zipFile->addFromString($walletClass::getName('en') . '_' . $currency . '.csv', stream_get_contents($tempFile));
      }
    }

    $zipFile->close();

    (new \mcms\payments\components\events\PaymentsExported($this))->trigger();

    return Yii::getAlias(self::EXPORT_URL . $this->filename);
  }

  /**
   * Запрос для получения выплат
   * @return \yii\db\ActiveQuery
   */
  protected function getQuery()
  {
    return UserPayment::find()
      ->joinWith('userPaymentSetting')
      ->andFilterWhere([UserPayment::tableName() . '.status' => $this->exportForm->status_ids])
      ->andFilterWhere(['not in', UserPaymentSetting::tableName() . '.user_id', self::getNotAvailableUserIds()])
      ->andFilterWhere([UserPayment::tableName() . '.wallet_type' => $this->exportForm->wallet_ids]);
  }

  /**
   * Получение префикса названия файла
   */
  public function getPrefix()
  {
    return Yii::$app->user->identity->username . '_';
  }

  /**
   * Получение имени архива
   */
  public function getFilename()
  {
    return $this->_filename ? : ($this->_filename = strtr('{prefix}{date}_{random}.zip', [
      '{prefix}' => $this->prefix,
      '{date}' => Yii::$app->formatter->asDatetime('now', 'php:Y_m_d_H_i'),
      '{random}' => Yii::$app->security->generateRandomString(10)
    ]));
  }

  /**
   * Получение архивов пользователя
   * @return array
   */
  protected function getUserArchives()
  {
    $path = Yii::getAlias(self::EXPORT_PATH);
    if (!is_dir($path)) {
      return [];
    }

    $fullLength = mb_strlen($this->filename);
    $prefix = $this->prefix;
    $prefLength = mb_strlen($prefix);

    return FileHelper::findFiles($path, [
      'filter' => function($path) use ($fullLength, $prefix, $prefLength) {
        $fileName = basename($path);

        if (mb_strlen($fileName) !== $fullLength) {
          return false;
        }

        return mb_substr($fileName, 0, $prefLength) === $prefix;
      }
    ]);
  }

  /**
   * Если директории не существует, она создается заново
   */
  protected function recreatePath()
  {
    $path = Yii::getAlias(self::EXPORT_PATH);
    if (!is_dir($path)) {
      FileHelper::createDirectory($path);
    }
  }

  /**
   * Удаление предыдущих архивов пользователя
   */
  public function clearPrevArchives()
  {
    array_map(function($filename) {
        @unlink($filename);
        return '';
      }, $this->getUserArchives()
    );
  }

  /**
   * Получение предыдущего архива пользователя
   * @return string
   */
  public function getPrevLink()
  {
    $userArchives = $this->getUserArchives();
    return (!empty($userArchives)) ? Yii::getAlias(self::EXPORT_URL . basename($userArchives[0])) : false;
  }

  public function getReplacements()
  {
    return [
      'status_ids' => [
        'value' => $this->exportForm->status_ids ? implode(", ", array_map(function($id) {
          return UserPayment::getStatuses($id);
        }, $this->exportForm->status_ids)) : null,
        'helper' => [
          'label' => self::translate('attribute-status-ids')
        ]
      ],
      'wallet_ids' => [
        'value' => $this->exportForm->wallet_ids ? implode(", ", array_map(function($id) {
          return Wallet::getWallets($id);
        }, $this->exportForm->wallet_ids)) : null,
        'helper' => [
          'label' => self::translate('attribute-wallet-ids')
        ]
      ],
      'filename' => [
        'value' => $this->filename,
        'helper' => [
          'label' => self::translate('attribute-filename')
        ]
      ],
      'countExported' => [
        'value' => $this->_countExported,
        'helper' => [
          'label' => self::translate('attribute-count-exported')
        ]
      ],
      'memoryPeak' => [
        'value' => Yii::$app->formatter->asDecimal(memory_get_peak_usage(true) / (1024 * 1024)),
        'helper' => [
          'label' => self::translate('attribute-memory-peak')
        ]
      ],
      'time' => [
        'value' => (microtime(true) - $this->_startTime),
        'helper' => [
          'label' => self::translate('attribute-time')
        ]
      ],
    ];
  }

  /**
   * Возвращает недоступных пользователей
   * @return array
   */
  public static function getNotAvailableUserIds()
  {
    return Yii::$app->getModule('users')
      ->api('notAvailableUserIds', [
        'userId' => Yii::$app->user->id,
      ])
      ->getResult();
  }
}