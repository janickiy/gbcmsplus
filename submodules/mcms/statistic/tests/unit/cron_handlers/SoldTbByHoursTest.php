<?php
namespace mcms\statistic\tests\unit\cron_handlers;

use mcms\common\codeception\TestCase;
use mcms\statistic\components\cron\CronParams;
use mcms\statistic\components\cron\handlers\SellTbHitsGrouped;
use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\mainStat\mysql\Row;
use yii\db\Query;
use yii\helpers\ArrayHelper as ArHlp;
use Yii;

/**
 * тестируем крон и модель статы для продажи ТБ
 *
 * TODO какие тесты ещё остались по этой стате:
 * - гр по месяцам
 * - гр по неделям
 * - гр по источникам
 * - гр по ссылкам
 * - гр по потокам
 * - гр по платформам
 * - гр по операторам
 * - гр по странам
 * - гр по партнерам
 * - гр по методам оплаты
 * - гр по менеджерам
 * - фильтр по датам
 * - фильтр по источникам
 * - фильтр по потокам
 * - фильтр по платформам
 * - фильтр по операторам
 * - фильтр по странам
 * - фильтр по партнерам
 * - фильтр по методам оплаты
 * - фильтр по фейковости
 */
class SoldTbByHoursTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.sources',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('TRUNCATE TABLE hits')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE sell_tb_hits')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE sell_tb_hits_grouped')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE sold_trafficback')->execute();
    $this->loginAsReseller();
  }

  /**
   * запускаем крон
   * @param string $dateFrom
   */
  protected function handleCron($dateFrom)
  {
    $handler = new SellTbHitsGrouped([
      'params' => new CronParams([
        'fromTime' => strtotime($dateFrom),
      ]),
    ]);
    // группируем стату
    $handler->run();
  }

  /**
   * импортим файл sql
   * @param string $fileName
   */
  protected function importSql($fileName)
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/sold_tb_by_hours/' . $fileName);
    Yii::$app->db->createCommand($sql)->execute();
  }

  public function testGroupByDates()
  {
    $this->importSql('group_dates.sql');
    $this->handleCron('2018-01-17');

    $formModel = new FormModel([
      'currency' => 'rub',
      'groups' => [Group::BY_DATES],
      'dateFrom' => '2018-01-17',
      'dateTo' => '2018-01-19',
    ]);

    /* @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);
    $statisticData = $fetch->getDataProvider()->allModels;

    /* @var Row $statDate1 */
    $statDate1 = $statisticData['2018-01-17'];
    $this->assertEquals(2, $statDate1->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(2, $statDate1->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(240, $statDate1->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statDate2 */
    $statDate2 = $statisticData['2018-01-18'];
    $this->assertEquals(1, $statDate2->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statDate2->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, $statDate2->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statDate3 */
    $statDate3 = $statisticData['2018-01-19'];
    $this->assertEquals(1, $statDate3->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statDate3->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, $statDate3->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statFooter */
    $statFooter = $fetch->getDataProvider()->footerRow;
    $this->assertEquals(4, $statFooter->getSellTbAccepted(), 'Итого: Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(4, $statFooter->getSoldTb(), 'Итого: Кол-во хитов которые сконвертились');
    $this->assertEquals(480, $statFooter->getSoldTbProfit(), 'Итого: Сколько профита получилось');
  }

  public function testGroupByHours()
  {
    $this->importSql('group_hours.sql');
    $this->handleCron('2018-01-17');

    $formModel = new FormModel([
      'currency' => 'rub',
      'groups' => [Group::BY_HOURS],
      'dateFrom' => '2018-01-17',
      'dateTo' => '2018-01-19',
    ]);

    /* @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);
    $statisticData = $fetch->getDataProvider()->allModels;

    /* @var Row $statDate1 */
    $statDate1 = $statisticData['6'];
    $this->assertEquals(1, $statDate1->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statDate1->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, $statDate1->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statDate2 */
    $statDate2 = $statisticData['9'];
    $this->assertEquals(1, $statDate2->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statDate2->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, $statDate2->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statDate3 */
    $statDate3 = $statisticData['16'];
    $this->assertEquals(2, $statDate3->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(2, $statDate3->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(240, $statDate3->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statFooter */
    $statFooter = $fetch->getDataProvider()->footerRow;
    $this->assertEquals(4, $statFooter->getSellTbAccepted(), 'Итого: Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(4, $statFooter->getSoldTb(), 'Итого: Кол-во хитов которые сконвертились');
    $this->assertEquals(480, $statFooter->getSoldTbProfit(), 'Итого: Сколько профита получилось');
  }

  public function testGroupByLandings()
  {
    $this->importSql('group_landings.sql');
    $this->handleCron('2018-01-17');

    $lands = (new Query())
      ->select(['landing_id'], 'DISTINCT')
      ->from('sell_tb_hits_grouped')
      ->column();

    $this->assertCount(1, $lands, 'Лендинг только нулевой может быть');
    $this->assertEquals(0, reset($lands), 'Лендинг только нулевой может быть');
  }

  public function testGroupByProviders()
  {
    $this->importSql('group_landings.sql');
    $this->handleCron('2018-01-17');

    $providers = (new Query())
      ->select(['provider_id'], 'DISTINCT')
      ->from('sell_tb_hits_grouped')
      ->column();

    $this->assertCount(1, $providers, 'Провайдер только нулевой может быть');
    $this->assertEquals(0, reset($providers), 'Провайдер только нулевой может быть');
  }

  public function testGroupByCategories()
  {
    $this->importSql('group_categories.sql');
    $this->handleCron('2018-01-17');

    list($hits, $sold) = $this->getFromDb('category_id');

    $this->assertCount(3, $hits, '3 категории в хитах');
    $this->assertCount(3, $sold, '3 категории в продажах');

    $this->assertEquals(2, ArHlp::getValue($hits['0'], 'sell_tb_accepted'), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(2, ArHlp::getValue($sold['0'], 'count_sold_tb'), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(240, ArHlp::getValue($sold['0'], 'sold_tb_reseller_profit_rub'), 'Сколько профита получилось');
    $this->assertEquals(4, ArHlp::getValue($sold['0'], 'sold_tb_reseller_profit_usd'), 'Сколько профита получилось');
    $this->assertEquals(3.6, ArHlp::getValue($sold['0'], 'sold_tb_reseller_profit_eur'), 'Сколько профита получилось');

    $this->assertEquals(1, ArHlp::getValue($hits['1'], 'sell_tb_accepted'), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, ArHlp::getValue($sold['1'], 'count_sold_tb'), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, ArHlp::getValue($sold['1'], 'sold_tb_reseller_profit_rub'), 'Сколько профита получилось');
    $this->assertEquals(2, ArHlp::getValue($sold['1'], 'sold_tb_reseller_profit_usd'), 'Сколько профита получилось');
    $this->assertEquals(1.8, ArHlp::getValue($sold['1'], 'sold_tb_reseller_profit_eur'), 'Сколько профита получилось');

    $this->assertEquals(1, ArHlp::getValue($hits['2'], 'sell_tb_accepted'), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, ArHlp::getValue($sold['2'], 'count_sold_tb'), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, ArHlp::getValue($sold['2'], 'sold_tb_reseller_profit_rub'), 'Сколько профита получилось');
    $this->assertEquals(2, ArHlp::getValue($sold['2'], 'sold_tb_reseller_profit_usd'), 'Сколько профита получилось');
    $this->assertEquals(1.8, ArHlp::getValue($sold['2'], 'sold_tb_reseller_profit_eur'), 'Сколько профита получилось');
  }

  public function testGroupByTbProviders()
  {
    $this->importSql('group_tb_providers.sql');
    $this->handleCron('2018-01-17');

    list($hits, $sold) = $this->getFromDb('tb_provider_id');

    $this->assertCount(3, $hits, '3 ТБ провайдера в хитах');
    $this->assertCount(3, $sold, '3 ТБ провайдера в продажах');

    $this->assertEquals(2, ArHlp::getValue($hits['1'], 'sell_tb_accepted'), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(2, ArHlp::getValue($sold['1'], 'count_sold_tb'), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(240, ArHlp::getValue($sold['1'], 'sold_tb_reseller_profit_rub'), 'Сколько профита получилось');
    $this->assertEquals(4, ArHlp::getValue($sold['1'], 'sold_tb_reseller_profit_usd'), 'Сколько профита получилось');
    $this->assertEquals(3.6, ArHlp::getValue($sold['1'], 'sold_tb_reseller_profit_eur'), 'Сколько профита получилось');

    $this->assertEquals(1, ArHlp::getValue($hits['3'], 'sell_tb_accepted'), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, ArHlp::getValue($sold['3'], 'count_sold_tb'), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, ArHlp::getValue($sold['3'], 'sold_tb_reseller_profit_rub'), 'Сколько профита получилось');
    $this->assertEquals(2, ArHlp::getValue($sold['3'], 'sold_tb_reseller_profit_usd'), 'Сколько профита получилось');
    $this->assertEquals(1.8, ArHlp::getValue($sold['3'], 'sold_tb_reseller_profit_eur'), 'Сколько профита получилось');

    $this->assertEquals(1, ArHlp::getValue($hits['4'], 'sell_tb_accepted'), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, ArHlp::getValue($sold['4'], 'count_sold_tb'), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(120, ArHlp::getValue($sold['4'], 'sold_tb_reseller_profit_rub'), 'Сколько профита получилось');
    $this->assertEquals(2, ArHlp::getValue($sold['4'], 'sold_tb_reseller_profit_usd'), 'Сколько профита получилось');
    $this->assertEquals(1.8, ArHlp::getValue($sold['4'], 'sold_tb_reseller_profit_eur'), 'Сколько профита получилось');
  }

  public function testFilterByCurrencies()
  {
    $this->importSql('filter_currencies.sql');
    $this->handleCron('2018-01-17');

    /**
     * RUB
     */
    $formModel = new FormModel([
      'currency' => 'rub',
      'groups' => [Group::BY_DATES],
      'dateFrom' => '2018-01-17',
      'dateTo' => '2018-01-19',
    ]);

    /* @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);
    $statisticData = $fetch->getDataProvider()->allModels;


    /* @var Row $statDate1 */
    $statDate1 = $statisticData['2018-01-17'];
    $this->assertEquals(4, $statDate1->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(2, $statDate1->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(240, $statDate1->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statFooter */
    $statFooter = $fetch->getDataProvider()->footerRow;
    $this->assertEquals(4, $statFooter->getSellTbAccepted(), 'Итого: Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(2, $statFooter->getSoldTb(), 'Итого: Кол-во хитов которые сконвертились');
    $this->assertEquals(240, $statFooter->getSoldTbProfit(), 'Итого: Сколько профита получилось');

    /**
     * USD
     */
    $formModel = new FormModel([
      'currency' => 'usd',
      'groups' => [Group::BY_DATES],
      'dateFrom' => '2018-01-17',
      'dateTo' => '2018-01-19',
    ]);

    /* @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);
    $statisticData = $fetch->getDataProvider()->allModels;

    /* @var Row $statDate1 */
    $statDate1 = $statisticData['2018-01-17'];
    $this->assertEquals(4, $statDate1->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statDate1->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(2, $statDate1->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statFooter */
    $statFooter = $fetch->getDataProvider()->footerRow;
    $this->assertEquals(4, $statFooter->getSellTbAccepted(), 'Итого: Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statFooter->getSoldTb(), 'Итого: Кол-во хитов которые сконвертились');
    $this->assertEquals(2, $statFooter->getSoldTbProfit(), 'Итого: Сколько профита получилось');

    /**
     * EUR
     */
    $formModel = new FormModel([
      'currency' => 'eur',
      'groups' => [Group::BY_DATES],
      'dateFrom' => '2018-01-17',
      'dateTo' => '2018-01-19',
    ]);

    /* @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);
    $statisticData = $fetch->getDataProvider()->allModels;

    /* @var Row $statDate1 */
    $statDate1 = $statisticData['2018-01-17'];
    $this->assertEquals(4, $statDate1->getSellTbAccepted(), 'Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statDate1->getSoldTb(), 'Кол-во хитов которые сконвертились');
    $this->assertEquals(1.8, $statDate1->getSoldTbProfit(), 'Сколько профита получилось');
    /* @var Row $statFooter */
    $statFooter = $fetch->getDataProvider()->footerRow;
    $this->assertEquals(4, $statFooter->getSellTbAccepted(), 'Итого: Кол-во хитов которые ушли на ТБ провайдера');
    $this->assertEquals(1, $statFooter->getSoldTb(), 'Итого: Кол-во хитов которые сконвертились');
    $this->assertEquals(1.8, $statFooter->getSoldTbProfit(), 'Итого: Сколько профита получилось');
  }

  /**
   * Из БД, а не из модели
   * @param $group
   * @return array
   */
  protected function getFromDb($group)
  {
    $hits = (new Query())
      ->select([
        $group,
        'sell_tb_accepted' => 'SUM(count_hits)',
      ])
      ->from('sell_tb_hits_grouped')
      ->groupBy($group)
      ->indexBy($group)
      ->all();

    $sold = (new Query())
      ->select([
        $group,
        'count_sold_tb' => 'COUNT(1)',
        'sold_tb_reseller_profit_rub' => 'SUM(reseller_profit_rub)',
        'sold_tb_reseller_profit_usd' => 'SUM(reseller_profit_usd)',
        'sold_tb_reseller_profit_eur' => 'SUM(reseller_profit_eur)',
      ])
      ->from('sold_trafficback')
      ->groupBy($group)
      ->indexBy($group)
      ->all();

    return [$hits, $sold];
  }

}