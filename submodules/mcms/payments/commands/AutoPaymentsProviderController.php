<?php
/**
 * @copyright Copyright (c) 2023 VadimTs
 * @link https://tsvadim.dev/
 * Creator: VadimTs
 * Date: 20.12.2023
 */

namespace mcms\payments\commands;

use mcms\mcms\payments\components\AutoPaymentGenerator;
use mcms\payments\models\PartnerPaymentSettings;
use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\Json;

/**
 * Генератор автоматических заявок для выплаты партнёрам.
 */
class AutoPaymentsProviderController extends \yii\console\Controller
{
  /**
   * Запускать в полночь каждый день
   * @return int
   * @throws \mcms\payments\components\exceptions\UserBalanceException
   * @throws \yii\base\Exception
   * @throws \yii\base\InvalidConfigException
   */
  public function actionIndex()
  {
    //   if(!$this->confirm("Start autopayment?",true)){
    //     $this->stdout("Bye Bye".PHP_EOL);
    //     return ExitCode::OK;
    // }
    /** @var PartnerPaymentSettings $model */
    foreach ($this->getPaymentModels() as $model) {
      $this->stdout("Run partner {$model->id}" . PHP_EOL);
      /** @var AutoPaymentGenerator $autoPaymentGenerator */
      $autoPaymentGenerator = \Yii::createObject(AutoPaymentGenerator::class, [$model]);
      if (!$autoPaymentGenerator->pay()) {
        
        $this->stdout("Partner " . $model->id . " has errors: " . PHP_EOL . implode(PHP_EOL, $autoPaymentGenerator->getErrors()) . PHP_EOL);
        $model->updateAttributes(['message' => Json::encode($autoPaymentGenerator->getFirstError()), 'last_checked_at' => Yii::$app->formatter->asTimestamp('now')]);
      } else {
        $model->updateAttributes(['message' => NULL, 'last_checked_at' => Yii::$app->formatter->asTimestamp('now')]);
      }
      $this->stdout("Partner {$model->id} last_checked_at: " . \Yii::$app->formatter->asDatetime($model->last_checked_at, 'php:d-m-Y H:i:s') . PHP_EOL);
    }
    return ExitCode::OK;
  }
  
  /**
   * @return PartnerPaymentSettings[]
   */
  private function getPaymentModels()
  {
    return PartnerPaymentSettings::find()->each();
  }
}