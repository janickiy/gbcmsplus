<?php

namespace mcms\statistic\dbfix;

use Yii;
use console\components\Migration;
use yii\db\Query;

/*
 * php yii db-fix/run-command update_mobleaders_profit --sm=statistic
 */

class update_mobleaders_profit extends Migration
{
  private $date = '2018-08-30';

  public function up()
  {
    Yii::$app->db->createCommand('CREATE TABLE IF NOT EXISTS tmp_subscription_rebills LIKE subscription_rebills')->execute();

    Yii::$app->db->createCommand('CREATE TABLE IF NOT EXISTS tmp_onetime_subscriptions LIKE onetime_subscriptions')->execute();;

    $providerId = (new Query)->select('id')->from('providers')
      ->where('code=:code', [
        ':code' => 'mobleaders'
      ])->scalar();

    $rebills = (new Query)->select('sr.id, profit_rub, profit_usd, profit_eur, time')->from('subscription_rebills sr')
      ->where('sr.date >= :date AND sr.provider_id=:provider_id AND sr.is_cpa=0', [
        ':date' => $this->date,
        ':provider_id' => $providerId
      ]);

    $onetimes = (new Query)->select('os.id, profit_rub, profit_usd, profit_eur, time')->from('onetime_subscriptions os')
      ->where('os.date >= :date AND os.provider_id=:provider_id AND os.is_visible_to_partner=1', [
        ':date' => $this->date,
        ':provider_id' => $providerId
      ]);


    foreach ($rebills->each() as $rebill) {
      $profitEur = $rebill['profit_eur'];
      $rebillTime = $rebill['time'];
      $rebillId = $rebill['id'];
      
      list($toRub, $toUsd) = $this->getCourseByTime($rebillTime);

      $newProfitUsd = $profitEur * $toUsd;
      $newProfitRub = $profitEur * $toRub;

      echo "Rebill ID: " . $rebillId . ' old profit_usd: ' . $rebill['profit_usd'] . ' new profit_usd: ' . $newProfitUsd 
        . '; old profit_rub: ' . $rebill['profit_rub'] . ' new profit_rub: ' . $rebill['profit_rub'] . PHP_EOL;

      Yii::$app->db->createCommand('INSERT INTO tmp_subscription_rebills (SELECT * FROM subscription_rebills WHERE id=:id)', [':id' => $rebillId])->execute();
      
      (new Query)->createCommand()->update(
        'subscription_rebills',
        ['profit_usd' => $newProfitUsd, 'profit_rub' => $newProfitRub], 
        ['id' => $rebillId]
      )->execute();
    }


    foreach ($onetimes->each() as $onetime) {
      $profitEur = $onetime['profit_eur'];
      $onetimeTime = $onetime['time'];
      $onetimeId = $onetime['id'];

      list($toRub, $toUsd) = $this->getCourseByTime($onetimeTime);

      $newProfitUsd = $profitEur * $toUsd;
      $newProfitRub = $profitEur * $toRub;

      echo "Onetime ID: " . $onetimeId . ' old profit_usd: ' . $onetime['profit_usd'] . ' new profit_usd: ' . $newProfitUsd
        . '; old profit_rub: ' . $onetime['profit_rub'] . ' new profit_rub: ' . $onetime['profit_rub'] . PHP_EOL;

      Yii::$app->db->createCommand('INSERT INTO tmp_onetime_subscriptions (SELECT * FROM onetime_subscriptions WHERE id=:id)', [':id' => $onetimeId])->execute();

      (new Query)->createCommand()->update(
        'onetime_subscriptions',
        ['profit_usd' => $newProfitUsd, 'profit_rub' => $newProfitRub],
        ['id' => $onetimeId]
      )->execute();
    }

  }

  /**
   * @param $time
   * @return array
   */
  public function getCourseByTime($time)
  {
    $course = (new Query)->from('currency_courses_log')
      ->where('currency_id = :currency_id AND updated_at <= :date', [
        ':currency_id' => 3, //евро
        ':date' => $time,
      ])->orderBy(['updated_at' => SORT_DESC])->limit(1)->one();

    $customToRub = $course['custom_to_rub'];
    $customToUsd = $course['custom_to_usd'];

    $originalToRub = $course['to_rub'];
    $originalToUsd = $course['to_usd'];

    $partnerPercentRub = $course['partner_percent_rub'];
    $partnerPercentUsd = $course['partner_percent_usd'];

    $toRub = $customToRub && $this->isCustomCourseProfitable($customToRub, $originalToRub, $partnerPercentRub)
      ? $customToRub
      : $originalToRub * (100 - $partnerPercentRub) / 100;

    $toUsd =  $customToUsd && $this->isCustomCourseProfitable($customToUsd, $originalToUsd, $partnerPercentUsd)
      ? $customToUsd
      : $originalToUsd * (100 - $partnerPercentUsd) / 100;

    return [$toRub, $toUsd];
  }

  /**
   * Выгоден ли кастомный курс
   * @param $customCourse
   * @param $originalCourse
   * @param $partnerPercent
   * @return bool
   */
  public function isCustomCourseProfitable($customCourse, $originalCourse, $partnerPercent)
  {

    // Если кастомный курс не задан - все ок
    if ($customCourse === null) {
      return true;
    }
    // Оригинальный курс с учетом комиссии
    $withPercent = $originalCourse * (100 - $partnerPercent) / 100;

    // Если кастомный курс не выше оригинального с процентами - все ок
    if ($withPercent >= $customCourse) {
      return true;
    }

    // Если кастомный курс не выше оригинального с процентами - все ок, иначе курс не выгодный
    return $withPercent >= $customCourse;
  }
}
