<?php

namespace mcms\payments\commands;

use mcms\payments\components\InvoicesImport;
use mcms\payments\components\mgmp\send\ApiMgmpSender;
use mcms\payments\lib\mgmp\InvoicesTypeCaster;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Class InvoicesController
 * @package mcms\payments\commands
 */
class InvoicesController extends Controller
{

  public $dateFrom;

  const BATCH_SIZE = 1000;

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return array_merge(
      parent::options($actionID),
      ['dateFrom']
    );
  }

  /**
   * Импорт инвойсов с привязкой к выплатам и реселлеру
   * @return bool|int
   */
  public function actionImportFromMgmp()
  {
    $dateFrom = $this->dateFrom ?: Yii::$app->formatter->asDate("- 1 days", 'php:Y-m-d');

    $updatedAtFrom = Yii::$app->formatter->asTimestamp($dateFrom);

    $this->stdout("dateFrom={$dateFrom};updatedAtFrom=$updatedAtFrom;");

    /** @var ApiMgmpSender $sender */
    $sender = Yii::createObject('mcms\payments\components\mgmp\send\MgmpSenderInterface');
    $result = $sender->requestPaymentsInvoices($updatedAtFrom);
    if (!is_array($result) || !$result['success'] || !isset($result['data']) || !is_array($result['data'])) {
      return $this->error('Response is not correct: ' . print_r($result, true));
    }

    $this->stdout(count($result['data']) . " INVOICES FETCHED");

    foreach ($result['data'] as $item) {
      $this->stdout("###### id=" . $item['id']);

      $searchParams = [
        'user_id' => UserPayment::getResellerId(),
        'mgmp_id' => $item['id']
      ];
      $model = UserBalanceInvoice::find()->andWhere($searchParams)->one() ?: new UserBalanceInvoice($searchParams);

      $model->scenario = UserBalanceInvoice::SCENARIO_MGMP_IMPORT;

      $this->stdout("-- is_new=" . $model->isNewRecord);

      if (!$model->isNewRecord && $model->updated_at >= (int) $item['updated_at']) {
        $this->stdout("-- ignored by updated_at");
        continue;
      }

      $model->amount = ArrayHelper::getValue($item, 'amount', 0);
      $model->currency = ArrayHelper::getValue($item, 'currency', 0);
      $model->description = ArrayHelper::getValue($item, 'comment', 0);
      $model->type = InvoicesTypeCaster::mgmp2mcms(ArrayHelper::getValue($item, 'type', 0));
      $model->created_at = ArrayHelper::getValue($item, 'created_at', 0);
      $model->updated_at = ArrayHelper::getValue($item, 'updated_at', 0);
      $model->date = ArrayHelper::getValue($item, 'date', 0);

      $file = ArrayHelper::getValue($item, 'file');

      if ($file) {
        $fileContent = $sender->requestInvoiceFile($model->mgmp_id);

        if (!$fileContent) {
          $this->error('File content is empty');
          continue;
        }

        $dirAlias = '@protectedUploadPath' . DIRECTORY_SEPARATOR . 'invoice_files' . DIRECTORY_SEPARATOR . date('Y-m-d') . DIRECTORY_SEPARATOR;

        $dirPath = Yii::getAlias($dirAlias);
        FileHelper::createDirectory($dirPath);

        $fileName = Yii::$app->security->generateRandomString(5) . basename($file);

        $fileAlias = $dirAlias . $fileName;

        file_put_contents($dirPath . $fileName, $fileContent);

        $model->file = $fileAlias;
      } else {
        $model->file = null;
      }

      if ($model->save()) {
        $this->stdout("-- saved with id={$model->id}");
        continue;
      }

      $this->error('-- model not saved, errors:' . print_r($model->getErrors(), true));
    }

    return $this->stdout("finished");
  }

  /**
   * Импорт инвойсов
   */
  public function actionImport()
  {
    $this->stdout("Import invoices: ", false);
    if ((new InvoicesImport)->execute()) {
      $this->stdout("success\n");
    } else {
      $this->stderr("error\n");
    }

    $this->stdout("Import finished!\n");
  }

  /**
   * @param string $message
   * @param bool $breakAfter
   * @param bool $breakBefore
   * @return bool|int
   */
  public function stdout($message, $breakAfter = true, $breakBefore = false)
  {
    return parent::stdout(($breakBefore ? PHP_EOL : '') . $message . ($breakAfter ? PHP_EOL : ''), Console::FG_GREEN);
  }

  /**
   * @param $errorText
   * @return int
   */
  private function error($errorText)
  {
    $this->stdout($errorText);
    Yii::error($errorText, __METHOD__);
    return Controller::EXIT_CODE_ERROR;
  }
}