<?php
namespace mcms\holds\tests\unit\partner_hold;

use mcms\common\codeception\TestCase;
use mcms\holds\components\RuleUnholdPlanner;
use mcms\holds\models\HoldProgramRule;
use mcms\holds\models\PartnerHold;
use mcms\holds\models\PartnerHoldSearch;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Проверка данных из таблицы http://modulecms.lc/admin/payments/users/profit/?id=3
 */
class PartnerHoldTest extends TestCase
{
  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_programs')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_program_rules')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_balances_grouped_by_day')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_balance_invoices')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE rule_unhold_plan')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_payment_settings')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE partner_country_unhold')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();
  }

  public function testPartnerHolds()
  {
    $sql = file_get_contents(__DIR__ . '/sql/data.sql');
    Yii::$app->db->createCommand($sql)->execute();

    // Заполняем rule_unhold_plan
    $ruleIds = [1, 2];
    foreach ($ruleIds as $ruleId) {
      $rule = HoldProgramRule::findOne((int)$ruleId);
      (new RuleUnholdPlanner(['rule' => $rule]))->run();
    }


    $searchModel = new PartnerHoldSearch(['userId' => 101]);
    $dataProvider = $searchModel->search([]);

    $this->assertEquals(7, count($dataProvider->allModels), 'Не верное количество записей');
    /** @var PartnerHold $model */
    foreach ($dataProvider->allModels as $model) {
      $compareData = $this->getData($model->date, $model->countryId, $model->userCurrency);
      $this->assertNotNull($compareData, sprintf('Не найдено данных для даты %s, страны #%d, валюты %s', $model->date, $model->countryId, $model->userCurrency));

      $this->assertEquals($compareData['holdProfit'], $model->holdProfit, sprintf('Не верная сумма для даты %s, страны #%d, валюты %s', $model->date, $model->countryId, $model->userCurrency));

      $this->assertEquals($compareData['unholdDate'], $model->getUnholdDate(), sprintf('Не верная дата расхолда для даты %s, страны #%d, валюты %s', $model->date, $model->countryId, $model->userCurrency));

      $this->assertEquals($compareData['rule'], $model->getRule(), sprintf('Не верное правило для даты %s, страны #%d, валюты %s', $model->date, $model->countryId, $model->userCurrency));
    }
  }

  /**
   * @param $date
   * @param $countryId
   * @param $currency
   * @return array
   */
  private function getData($date, $countryId, $currency)
  {
    $data = [
      '2019-03-05' => [
        1 => [
          'rub' => [
            'holdProfit' => 240,
            'unholdDate' => '2019-06-24',
            'rule' => sprintf('#%d %s (%s - %s)', 2, 'Вторая программа', '2019-02-05', '2019-04-18'),
          ],
          'usd' => [
            'holdProfit' => 6.9,
            'unholdDate' => '2019-06-24',
            'rule' => sprintf('#%d %s (%s - %s)', 2, 'Вторая программа', '2019-02-05', '2019-04-18'),
          ],
        ],
        2 => [
          'rub' => [
            'holdProfit' => 271,
            'unholdDate' => '2019-04-17',
            'rule' => sprintf('#%d %s (%s - %s)', 2, 'Вторая программа', '2019-01-01', '2019-03-31'),
          ],
        ],
      ],
      '2019-03-20' => [
        1 => [
          'rub' => [
            'holdProfit' => 1923,
            'unholdDate' => '2019-06-24',
            'rule' => sprintf('#%d %s (%s - %s)', 2, 'Вторая программа', '2019-02-05', '2019-04-18'),
          ],
          'usd' => [
            'holdProfit' => 36.5,
            'unholdDate' => '2019-06-24',
            'rule' => sprintf('#%d %s (%s - %s)', 2, 'Вторая программа', '2019-02-05', '2019-04-18'),
          ],
        ],
        2 => [
          'rub' => [
            'holdProfit' => 757,
            'unholdDate' => '2019-04-17',
            'rule' => sprintf('#%d %s (%s - %s)', 2, 'Вторая программа', '2019-01-01', '2019-03-31'),
          ],
        ],
      ],
      '2019-03-21' => [
        1 => [
          'rub' => [
            'holdProfit' => 130,
            'unholdDate' => '2019-06-24',
            'rule' => sprintf('#%d %s (%s - %s)', 2, 'Вторая программа', '2019-02-05', '2019-04-18'),
          ],
        ],
      ],
    ];
    return ArrayHelper::getValue($data, $date . '.' . $countryId . '.' . $currency);
  }


}