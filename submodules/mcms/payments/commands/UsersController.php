<?php

namespace mcms\payments\commands;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\api\UserSettingsData;
use mcms\payments\components\UserBalance;
use mcms\user\models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\Console;
use mcms\payments\models\UserPaymentSetting;

class UsersController extends Controller
{

  public function actionGetUserPaymentSettings($userId)
  {
    $settings = Yii::$app->getModule('payments')->api('userSettingsData', [
      'userId' => $userId
    ])->getResult();
    echo json_encode($settings);
  }

  public function actionExportBalances()
  {
    /** @var ActiveDataProvider $dataProvider */
    $dataProvider = Yii::$app->getModule('users')
      ->api('user', [])
      ->setResultTypeDataProvider()
      ->search([], true, 0, true, ['partner']);
    $dataProvider->sort = ['defaultOrder' => ['id' => SORT_ASC]];

    $spreadsheet = new Spreadsheet();
    $spreadsheet->setActiveSheetIndex(0);
    $worksheet = $spreadsheet->getActiveSheet();

    $headers = [1=>'id',2=>'email',3=>'created',4=>'online',5=>'balance',6=>'currency'];
    foreach ($headers as $key => $value) {
      $worksheet->setCellValueByColumnAndRow($key, 1, $value);
    }

    $i = 3;
    foreach ($dataProvider->getModels() as $model) {
      /** @var User $model */

      $userPaymentSetting = UserPaymentSetting::fetch($model->id);
      $currency = $userPaymentSetting->getCurrentCurrency();
      $balance = new UserBalance([
        'userId' => $model->id,
        'currency' => $currency
      ]);

      $amount = $balance->getMain();

      if ($amount <= 0) {
        continue;
      }

      $row = [
        1=>$model->id,
        2=>$model->email,
        3=>Yii::$app->formatter->asDatetime($model->created_at),
        4=>Yii::$app->formatter->asDatetime($model->online_at),
        5=>Yii::$app->formatter->asDecimal($amount, 2),
        6=>$currency,
      ];

      foreach ($row as $key => $value) {
        $worksheet->setCellValueByColumnAndRow($key, $i, $value);
      }

      if ($i > 7) {
        break;
      }
      $i++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save(Yii::getAlias("@runtime/export-balances.xlsx"));
  }

  public function actionExportWriteOff()
  {
    $query = (new Query)
      ->select([
        'ui.user_id',
        'ui.amount',
        'ui.currency',
        'u.email',
        'u.online_at',
      ])
      ->from('user_balance_invoices ui')
      ->innerJoin('users u', 'u.id = ui.user_id')
      ->andWhere([
        'ui.type' => 12,
      ])
      ->andWhere(['>', 'ui.date', '2019-01-01']);

    $spreadsheet = new Spreadsheet();
    $spreadsheet->setActiveSheetIndex(0);
    $worksheet = $spreadsheet->getActiveSheet();

    $headers = [1=>'id',2=>'email',3=>'online',4=>'amount',5=>'currency'];
    foreach ($headers as $key => $value) {
      $worksheet->setCellValueByColumnAndRow($key, 1, $value);
    }

    $count = (clone $query)->count();

    $i = 3;
    foreach ($query->all() as $number => $row) {
      $row = [
        1=>$row['user_id'],
        2=>$row['email'],
        3=>Yii::$app->formatter->asDatetime($row['online_at']),
        4=>Yii::$app->formatter->asDecimal($row['amount'], 2),
        5=>$row['currency'],
      ];

      foreach ($row as $key => $value) {
        $worksheet->setCellValueByColumnAndRow($key, $i, $value);
      }

      echo "Обработано $number из $count\n";

      $i++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save(Yii::getAlias("@runtime/export-write-offs.xlsx"));
  }

  public function actionWriteOffBalances()
  {
    $time = strtotime("-6 months -1 day");
    $date = date("Y-m-d H:i:s", $time);

    /** @var ActiveDataProvider $dataProvider */
    $dataProvider = Yii::$app->getModule('users')
      ->api('user', [])
      ->setResultTypeDataProvider()
      ->search([], true, 0, true, ['partner']);
    $dataProvider->sort = ['defaultOrder' => ['id' => SORT_ASC]];
    $dataProvider->query->andWhere(['status' => 10]);
    $dataProvider->query->andWhere(['<', 'online_at', $time]);
    $dataProvider->prepare(); // добавляем сортировку по id

    $balances = [
      'rub' => 0,
      'usd' => 0,
      'eur' => 0,
    ];

    $dataProvider->query->limit(null);

    $i = 0;
    $processed = 0;
    $all = (clone $dataProvider->query)->count();

    foreach ($dataProvider->query->each() as $model) {
      /** @var User $model */

      $processed++;
//
//      $lastDate = date("Y-m-d H:i:s", $model->online_at);
//      echo "{$model->id} - Последний заход $lastDate\n";

      $userPaymentSetting = UserPaymentSetting::fetch($model->id);
      $currency = $userPaymentSetting->getCurrentCurrency();
      $balance = new UserBalance([
        'userId' => $model->id,
        'currency' => $currency
      ]);

      $amount = $balance->getMain();

      if ($amount <= 0) {
        continue;
      }
      $writeAmount = -$amount;

      $this->writeOff($model->id, $writeAmount, $currency, $date, $time);
//      echo "Юзер {$model->id}, списано {$writeAmount} {$currency}\n";

      $balances[$currency] += $amount;

      $i++;

      echo "Обработано $processed из $all, списаний $i, rub {$balances['rub']}, usd {$balances['usd']}, eur {$balances['eur']}\n";
    }

    echo "Всего списаний {$i}\n";
    echo "Списано rub {$balances['rub']}\n";
    echo "Списано usd {$balances['usd']}\n";
    echo "Списано eur {$balances['eur']}\n";
  }

  /**
   * @param $userId
   * @param $amount
   * @param $currency
   */
  protected function writeOff($userId, $amount, $currency, $date, $time)
  {
    Yii::$app->db
      ->createCommand()
      ->insert('user_balance_invoices', [
        'user_id' => $userId,
        'country_id' => 0,
        'currency' => $currency,
        'amount' => $amount,
        'description' => '',
        'created_at' => $time,
        'updated_at' => $time,
        'date' => $date,
        'created_by' => 1,
        'type' => 12,
      ])
      ->execute();
  }
}