<?php
namespace mcms\user\tests\unit\models;

use mcms\common\codeception\TestCase;
use mcms\user\models\LoginLog;
use Yii;
use mcms\user\models\LoginForm;
use yii\debug\models\search\Log;

/**
 * Проверка сохранения ip в логе залогинки
 */
class LoginLogTest extends TestCase
{


  protected function _before()
  {
    Yii::$app->db->createCommand('delete from login_logs')->execute();
  }


  public function testIpV6Save()
  {
    $log = new LoginLog();
    $log->user_id = 1;
    $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36';
    $log->ip = '2001:470:1f0b:687:2dd8:ecc:74d9:3cfa';
    $log->save();
    expect('Сохранение правильного ipv6 без ошибок', $log->getErrors())->same([]);
    $loginLog = LoginLog::findOne(['ip' => '2001:470:1f0b:687:2dd8:ecc:74d9:3cfa']);
    expect('В бд есть запись с этим ip', $loginLog->ip)->same('2001:470:1f0b:687:2dd8:ecc:74d9:3cfa');

    $log = new LoginLog();
    $log->user_id = 1;
    $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36';
    $log->ip = '2001:470';
    $log->save();
    expect('Сохранение неправильного ipv6 с ошибками', $log->getErrors())->same(['ip' => ['Значение «ip» должно быть правильным IP адресом.']]);
    $loginLog = LoginLog::findOne(['ip' => '2001:470']);
    expect('В бд есть запись с этим ip', $loginLog)->same(null);
  }

  public function testIpV4Save()
  {
    $log = new LoginLog();
    $log->user_id = 1;
    $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36';
    $log->ip = '172.68.11.198';
    $log->save();
    expect('Сохранение правильного ipv4 без ошибок', $log->getErrors())->same([]);
    $loginLog = LoginLog::findOne(['ip' => '172.68.11.198']);
    expect('В бд есть запись с этим ip', $loginLog->ip)->same('172.68.11.198');

    $log = new LoginLog();
    $log->user_id = 1;
    $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36';
    $log->ip = '172.68.11';
    $log->save();
    expect('Сохранение неправильного ipv4 с ошибками', $log->getErrors())->same(['ip' => ['Значение «ip» должно быть правильным IP адресом.']]);
    $loginLog = LoginLog::findOne(['ip' => '172.68.11']);
    expect('В бд есть запись с этим ip', $loginLog)->same(null);
  }

}