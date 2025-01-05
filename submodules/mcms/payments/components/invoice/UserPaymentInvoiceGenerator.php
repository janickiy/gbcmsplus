<?php

namespace mcms\payments\components\invoice;


use kartik\mpdf\Pdf;
use mcms\common\output\OutputInterface;
use mcms\common\traits\LogTrait;
use mcms\partners\components\mainStat\FormModel;
use mcms\partners\components\mainStat\Row;
use mcms\payments\models\Company;
use mcms\payments\models\PartnerCompany;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserBalancesGroupedByDay;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\promo\models\Country;
use mcms\statistic\components\mainStat\DataProvider;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\mainStat\mysql\Fetch;
use Yii;
use yii\base\Object;
use yii\console\Application;
use yii\db\Query;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Class UserPaymentInvoiceGenerator
 * @package mcms\payments\components
 */
class UserPaymentInvoiceGenerator extends Object
{
  use LogTrait;

  /**
   * @var UserPayment
   */
  public $userPayment;

  /**
   * @var string
   */
  public $positiveView = '@mcms/payments/components/invoice/views/index';

  /**
   * @var string
   */
  public $negativeView = '@mcms/payments/components/invoice/views/negative';

  /**
   * @var string
   */
  public $uploadPath = '@protectedUploadPath/payments/generated-invoices/';

  /**
   * @var PartnerCompany
   */
  protected $partnerCompany;

  /**
   * @var Company
   */
  protected $resellerCompany;

  /**
   * @var UserPaymentSetting
   */
  protected $userPaymentSettings;

  /**
   * @var bool
   */
  protected $isInitialized = false;

  /**
   * @return array
   */
  public static function getProfitableInvoiceTypes()
  {
    return [
      UserBalanceInvoice::TYPE_CONVERT_INCREASE,
      UserBalanceInvoice::TYPE_CONVERT_DECREASE,
      UserBalanceInvoice::TYPE_COMPENSATION
    ];
  }

  /**
   * UserPaymentInvoiceGenerator constructor.
   * @param UserPayment $userPayment
   * @param array $config
   */
  public function __construct(UserPayment $userPayment, array $config = [])
  {
    $this->userPayment = $userPayment;

    parent::__construct($config);
  }

  /**
   * @inheritdoc
   */
  public function init()
  {
    $this->userPaymentSettings = $this->userPayment->userPaymentSetting;

    if (!$this->userPaymentSettings) {
      $message = 'Failed to initialize invoice generator. User Payment Settings not defined';

      $this->log(
        $message,
        [OutputInterface::BREAK_AFTER, Console::FG_RED]
      );
      Yii::error($message, __METHOD__);

      return;
    }

    $this->partnerCompany = $this->userPaymentSettings->partnerCompany;

    if (!$this->partnerCompany) {
      $this->log(
        'Failed to initialize invoice generator. Partner company not defined',
        [OutputInterface::BREAK_AFTER, Console::FG_RED]
      );
      return;
    }

    $this->resellerCompany = $this->partnerCompany->resellerCompany;

    if (!$this->resellerCompany) {
      $this->log(
        'Failed to initialize invoice generator. Reseller company not defined',
        [OutputInterface::BREAK_AFTER, Console::FG_RED]
      );
      return;
    }

    if (Yii::$app instanceof Application) {
      Yii::$app->language = 'en';
    }

    $this->isInitialized = true;
  }

  /**
   * @return bool
   */
  public function run()
  {
    if ($this->isInitialized === false) {
      $this->log(
        'Failed to run invoice generator',
        [OutputInterface::BREAK_AFTER, Console::FG_RED]
      );
      return false;
    }

    $countries = Country::getDropdownItems();

    $balancesByCountry = $this->mergeBalances(
      $this->getBalancesGroupByCountry(),
      $this->getPositiveInvoicesByCountry()
    );

    $this->log(
      'Balances prepared for ' . count($balancesByCountry) . ' countries',
      [OutputInterface::BREAK_AFTER]
    );

    $typeLabels = UserBalanceInvoice::getTypes();

    $content = Yii::$app->controller->renderPartial($this->positiveView, [
      'userPayment' => $this->userPayment,
      'partnerCompany' => $this->partnerCompany,
      'resellerCompany' => $this->resellerCompany,
      'statDataProvider' => $this->getStatDataProvider(),
      'balancesByCountry' => $balancesByCountry,
      'compensations' => $this->getPositiveCompensations(),
      'typeLabels' => $typeLabels,
      'countries' => $countries,
    ]);

    $filePath = Yii::getAlias($this->uploadPath);
    FileHelper::createDirectory($filePath);

    $filename = implode('-', [$this->userPayment->id, 'billing']) .  '.pdf';
    $fullPath = implode('', [$filePath, $filename]);

    if (!$this->generate($fullPath, $content)) {
      return false;
    }

    $this->log(
      'Positive invoice file created',
      [OutputInterface::BREAK_AFTER]
    );

    $this->userPayment->generated_invoice_file_positive = $filename;

    // генерация отрицательного инвойса

    $negativeBalances = $this->getNegativeInvoicesByCountry();
    if (!$negativeBalances) {
      $this->log(
        'There are no negative operations',
        [OutputInterface::BREAK_AFTER, Console::FG_RED]
      );

      return $this->save();
    }

    $content = Yii::$app->controller->renderPartial($this->negativeView, [
      'userPayment' => $this->userPayment,
      'partnerCompany' => $this->partnerCompany,
      'resellerCompany' => $this->resellerCompany,
      'negativeBalances' => $negativeBalances,
      'typeLabels' => $typeLabels,
    ]);

    $filename = implode('-', [$this->userPayment->id, 'credit_note']) .  '.pdf';
    $fullPath = implode('', [$filePath, $filename]);

    if (!$this->generate($fullPath, $content)) {
      return false;
    }

    $this->log(
      'Negative invoice file created',
      [OutputInterface::BREAK_AFTER]
    );

    $this->userPayment->generated_invoice_file_negative = $filename;

    return $this->save();
  }

  /**
   * @return array
   */
  protected function getBalancesGroupByCountry()
  {
    $userBalances = UserBalancesGroupedByDay::getProfitsByCountry(
      $this->userPayment->user_id,
      $this->userPayment->from_date,
      $this->userPayment->to_date,
      $this->userPayment->invoice_currency
    );

    return $userBalances;
  }

  /**
   * @return array
   */
  public function getPositiveInvoicesByCountry()
  {
    return UserBalanceInvoice::find()
      ->select([
        'SUM(amount) as amount',
      ])
      ->andWhere([
        'BETWEEN',
        'date',
        Yii::$app->formatter->asDate($this->userPayment->from_date, 'php:Y-m-d'),
        Yii::$app->formatter->asDate($this->userPayment->to_date, 'php:Y-m-d')
      ])
      ->andWhere([
        'user_id' => $this->userPayment->user_id,
        'currency' => $this->userPayment->invoice_currency,
        'type' => array_diff(static::getProfitableInvoiceTypes(), [UserBalanceInvoice::TYPE_COMPENSATION]),
      ])
      ->groupBy('country_id')
      ->indexBy('country_id')
      ->column();
  }

  /**
   * @return array
   */
  public function getPositiveCompensations()
  {
    return (new Query())
      ->select([
        'type',
        'amount',
        'date',
      ])
      ->from(UserBalanceInvoice::tableName())
      ->andWhere([
        'BETWEEN',
        'date',
        Yii::$app->formatter->asDate($this->userPayment->from_date, 'php:Y-m-d'),
        Yii::$app->formatter->asDate($this->userPayment->to_date, 'php:Y-m-d')
      ])
      ->andWhere([
        'user_id' => $this->userPayment->user_id,
        'currency' => $this->userPayment->invoice_currency,
        'type' => UserBalanceInvoice::TYPE_COMPENSATION,
      ])
      ->all();
  }

  /**
   * @return array
   */
  public function getNegativeInvoicesByCountry()
  {
    return (new Query())
      ->select([
        'type',
        'ABS(amount) amount',
        'date',
      ])
      ->from(UserBalanceInvoice::tableName())
      ->andWhere([
        'BETWEEN',
        'date',
        Yii::$app->formatter->asDate($this->userPayment->from_date, 'php:Y-m-d'),
        Yii::$app->formatter->asDate($this->userPayment->to_date, 'php:Y-m-d')
      ])
      ->andWhere([
        'user_id' => $this->userPayment->user_id,
        'currency' => $this->userPayment->invoice_currency,
      ])
      ->andWhere(['not in', 'type', static::getProfitableInvoiceTypes()])
      ->all();
  }

  /**
   * @return DataProvider
   */
  protected function getStatDataProvider()
  {
    $formModel = new FormModel([
      'dateTo' => $this->userPayment->to_date,
      'dateFrom' => $this->userPayment->from_date,
      'users' => $this->userPayment->user_id,
      'groups' => [Group::BY_COUNTRIES],
      'viewerId' => $this->userPayment->user_id,
    ]);

    $fetch = Yii::createObject(
      ['class' => Fetch::class, 'rowClass' => Row::class],
      [$formModel]
    );

    return $fetch->getDataProvider();
  }

  /**
   * @param string $filename
   * @param string $content
   * @return bool
   */
  protected function generate($filename, $content)
  {
    $pdf = new Pdf([
      // set to use core fonts only
      'mode' => Pdf::MODE_CORE,
      // A4 paper format
      'format' => Pdf::FORMAT_A4,
      // portrait orientation
      'orientation' => Pdf::ORIENT_PORTRAIT,
      // stream to browser inline
      'destination' => Pdf::DEST_FILE,
      'filename' => $filename,
      // your html content input
      'content' => $content,
      // format content from your own css file if needed or use the
      // enhanced bootstrap css built by Krajee for mPDF formatting
      'cssFile' => '@mcms/payments/components/invoice/src/invoice.css',
      // any css to be embedded if required
//      'cssInline' => '.kv-heading-1{font-size:18px}',
      // set mPDF properties on the fly
      'options' => ['title' => null],
      'marginLeft' => 25,
      'marginRight' => 25,
      'defaultFontSize' => 10,
      // call mPDF methods on the fly
      'methods' => [
//        'SetHeader'=>['Krajee Report Header'],
//        'SetFooter'=>['{PAGENO}'],
      ]
    ]);

    try {
      $pdf->render();
    } catch (\Exception $e) {
      $message = 'Invoice PDF generation failed: ' . $e->getMessage();

      Yii::error($message, __METHOD__);
      $this->log($message, [OutputInterface::BREAK_AFTER, Console::BOLD, Console::FG_RED]);

      return false;
    }

    return true;
  }

  /**
   * @return bool
   */
  protected function save()
  {
    $this->log(
      'Invoice generation completed successfully',
      [OutputInterface::BREAK_AFTER, Console::BOLD, Console::FG_GREEN]
    );

    return $this->userPayment->save();
  }

  /**
   * @param array $balance1
   * @param array $balance2
   * @return array
   */
  protected function mergeBalances($balance1, $balance2)
  {
    foreach ($balance1 as $key => $amount) {
      $balance2[$key] = isset($balance2[$key]) ? $balance2[$key] + $amount : $amount;
    }

    return $balance2;
  }
}