<?php
namespace mcms\promo\tests\unit\models;

use mcms\common\codeception\TestCase;
use mcms\promo\models\Domain;
use yii\db\Query;

/**
 * Class DomainTest
 * @package mcms\promo\tests\unit\models
 */
class DomainTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users', 'promo.domains'
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    $this->loginAsRoot();
  }


  public function testUrlFormat()
  {
    // Без http:// и слэша в конце
    $domain = new Domain([
      'scenario' => Domain::SCENARIO_PARTNER_PARK,
      'user_id' => 101,
      'created_by' => 101,
      'url' => 'new_domain.ru'
    ]);
    $this->assertTrue($domain->save(), 'Без http:// и слэша в конце; Модель сохранилась');
    $this->assertEquals('http://new_domain.ru/', $domain->url, 'Без http:// и слэша в конце');
    $this->assertEquals('http://new_domain.ru/', $this->getDomainUrlById($domain->id), 'Без http:// и слэша в конце из бд');

    // Без http://
    $domain = new Domain([
      'scenario' => Domain::SCENARIO_PARTNER_PARK,
      'user_id' => 101,
      'created_by' => 101,
      'url' => 'new_domain1.ru/'
    ]);

    $this->assertTrue($domain->save(), 'Без http://; Модель сохранилась');
    $this->assertEquals('http://new_domain1.ru/', $domain->url, 'Без http://');
    $this->assertEquals('http://new_domain1.ru/', $this->getDomainUrlById($domain->id), 'Без http:// из бд');

    // Без слэша в конце
    $domain = new Domain([
      'scenario' => Domain::SCENARIO_PARTNER_PARK,
      'user_id' => 101,
      'created_by' => 101,
      'url' => 'http://new_domain2.ru'
    ]);
    $this->assertTrue($domain->save(), 'Без слэша в конце; Модель сохранилась');
    $this->assertEquals('http://new_domain2.ru/', $domain->url, 'Без слэша в конце');
    $this->assertEquals('http://new_domain2.ru/', $this->getDomainUrlById($domain->id), 'Без слэша в конце из бд');

    // С http:// и слэшем в конце
    $domain = new Domain([
      'scenario' => Domain::SCENARIO_PARTNER_PARK,
      'user_id' => 101,
      'created_by' => 101,
      'url' => 'http://new_domain3.ru/'
    ]);
    $this->assertTrue($domain->save(), 'С http:// и слэшем в конце; Модель сохранилась');
    $this->assertEquals('http://new_domain3.ru/', $domain->url, 'С http:// и слэшем в конце');
    $this->assertEquals('http://new_domain3.ru/', $this->getDomainUrlById($domain->id), 'С http:// и слэшем в конце из бд');

    // С https://
    $domain = new Domain([
      'scenario' => Domain::SCENARIO_PARTNER_PARK,
      'user_id' => 101,
      'created_by' => 101,
      'url' => 'https://new_domain4.ru/'
    ]);
    $this->assertTrue($domain->save(), 'С https://; Модель сохранилась');
    $this->assertEquals('https://new_domain4.ru/', $domain->url, 'С https://');
    $this->assertEquals('https://new_domain4.ru/', $this->getDomainUrlById($domain->id), 'С https:// из бд');

    $domain = new Domain([
      'scenario' => Domain::SCENARIO_PARTNER_PARK,
      'user_id' => 101,
      'created_by' => 101,
      'url' => ' http://new_domain6.ru/ '
    ]);

    $this->assertTrue($domain->save(), 'https://new_domain6.ru/ ; Модель сохранилась');
    $this->assertEquals('http://new_domain6.ru/', $domain->url, 'https://new_domain6.ru/');
    $this->assertEquals('http://new_domain6.ru/', $this->getDomainUrlById($domain->id), 'https://new_domain6.ru/ из бд');

    $domain = new Domain([
      'scenario' => Domain::SCENARIO_PARTNER_PARK,
      'user_id' => 101,
      'created_by' => 101,
      'url' => 'https://new_domain7.ru /'
    ]);
    $this->assertFalse($domain->save(), 'https://new_domain7.ru /; Модель не сохранилась');
    $this->assertFalse($this->getDomainUrlById($domain->id), 'https://new_domain7.ru /; из бд');

    $domain = new Domain([
      'url' => 'abs.com'
    ]);

    $domain->validate();
    $this->assertEquals('http://abs.com', $domain->url, 'Автоматическое добавление http если не было добавлено пользователем');
    $this->assertEquals('abs.com', $domain->domain_name, 'Поле domain name не должно содержать http');

    $domain = new Domain([
      'url' => 'system_domain2.ru',
      'user_id' => 101,
      'created_by' => 101,
      'type' => Domain::TYPE_NORMAL,
      'status' => Domain::STATUS_INACTIVE
    ]);

    $domain->validate();
    $this->assertArrayHasKey('url', $domain->getErrors(), 'Проверка на уникальность домена');
  }

  /**
   * @param $id
   * @return bool|string
   */
  private function getDomainUrlById($id)
  {
    return (new Query())->select('url')->from(Domain::tableName())->where(['id' => $id])->scalar();
  }

}