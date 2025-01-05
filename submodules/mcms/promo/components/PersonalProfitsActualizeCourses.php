<?php

namespace mcms\promo\components;

use mcms\common\RunnableInterface;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\payments\components\exchanger\CurrencyCourses;
use mcms\promo\components\events\personal_profit\PersonalProfitsActualizedCourses;
use mcms\promo\models\PartnerProgram;
use mcms\promo\models\PartnerProgramItem;
use mcms\promo\models\PersonalProfit;
use rgk\exchange\components\Currencies;
use RuntimeException;
use Yii;
use yii\base\Object;

/**
 * Если в персональных профитах указан оператор и указан фикс ЦПА,
 * то берём актуальный курс валют и валюту страны данного оператора.
 * И исходя из фикс. ЦПА для этой валюты расчитываем остальные две суммы по самому актуальному курсу.
 */
class PersonalProfitsActualizeCourses extends Object implements RunnableInterface
{

  /** @var Currencies */
  public $courses;
  /** @var int */
  public $partnerProgramId;

  public function init()
  {
    parent::init();

    if (!$this->courses) {
      $this->courses = PartnerCurrenciesProvider::getInstance()->getCurrencies();
    }
  }

  public function run()
  {
    if (!$this->courses) {
      throw new RuntimeException('Нет актуальных курсов валют для выполнения апдейта профитов');
    }

    $event = new PersonalProfitsActualizedCourses();
    $event->exchangeCourses = $this->courses;
    $event->programId = $this->partnerProgramId;
    $event->trigger();

    // решили просто делать апдейт двух таблиц: профитов и программ
    // но если нужно обновить только для конкретной программы, то обновляем только её итемсы,
    // а personal_profits обновится уже через очереди.
    if (!$this->partnerProgramId) {
      $this->makeUpdate(PersonalProfit::tableName());
      $this->makeUpdate(PartnerProgramItem::tableName());
      return;
    }
    $this->makeUpdate(PartnerProgramItem::tableName());
    $userIds = PartnerProgram::findOne((int)$this->partnerProgramId)->getAutoSyncUserIds();
    foreach ($userIds as $userId) {
      PartnerProgramSync::runAsync($userId);
    }
  }

  /**
   * Выполняет апдейт в БД
   * @param $tableName
   * @throws \yii\db\Exception
   */
  protected function makeUpdate($tableName)
  {
    $additionalConditions = '';
    if ($this->partnerProgramId) {
      $additionalConditions = 'AND pp.partner_program_id = ' . (int)$this->partnerProgramId;
    }

    $usdRub = $this->courses->getCurrency('usd')->getToRub();
    $eurRub = $this->courses->getCurrency('eur')->getToRub();
    $rubUsd = $this->courses->getCurrency('rub')->getToUsd();
    $eurUsd = $this->courses->getCurrency('eur')->getToUsd();
    $usdEur = $this->courses->getCurrency('usd')->getToEur();
    $rubEur = $this->courses->getCurrency('rub')->getToEur();

    //Для локальных валют кроме rub usd eur проводим актуализацию относительно валюты eur
    Yii::$app->db->createCommand("
      UPDATE $tableName pp
        -- меняем только те строки у которых прописан оператор
        INNER JOIN operators o ON o.id = pp.operator_id
        INNER JOIN countries c ON o.country_id = c.id
        SET 
          cpa_profit_rub = IF(
            cpa_profit_rub IS NULL,
            NULL,
            IF(cpa_profit_eur IS NULL, cpa_profit_rub, cpa_profit_eur * :course_eur_rub)
          ),
          cpa_profit_usd = IF(
              cpa_profit_usd IS NULL,
              NULL,
              IF(cpa_profit_eur IS NULL, cpa_profit_usd, cpa_profit_eur * :course_eur_usd)
          ),
          cpa_profit_eur = IF(
              cpa_profit_eur IS NULL,
              NULL,
              cpa_profit_eur
          ),
          pp.updated_at = UNIX_TIMESTAMP()
        WHERE
          c.currency = 'eur' AND c.local_currency NOT IN ('rub', 'usd', 'eur')
           $additionalConditions
        ;
    ")
      ->bindParam(':course_eur_rub', $eurRub)
      ->bindParam(':course_eur_usd', $eurUsd)
      ->execute();


    //Для локальных валют rub usd eur проводим актуализацию относительно локальной валюты
    Yii::$app->db->createCommand("
      UPDATE $tableName pp
        -- меняем только те строки у которых прописан оператор
        INNER JOIN operators o ON o.id = pp.operator_id
        INNER JOIN countries c ON o.country_id = c.id
        SET 
          cpa_profit_rub = IF(
            cpa_profit_rub IS NULL,
            NULL,
            IF(
                c.local_currency = 'rub',
                cpa_profit_rub,
                IF(
                    c.local_currency = 'usd',
                    IF(cpa_profit_usd IS NULL, cpa_profit_rub, cpa_profit_usd * :course_usd_rub),
                    IF(cpa_profit_eur IS NULL, cpa_profit_rub, cpa_profit_eur * :course_eur_rub)
                )
            )
          ),
          cpa_profit_usd = IF(
              cpa_profit_usd IS NULL,
              NULL,
              IF(
                  c.local_currency = 'usd',
                  cpa_profit_usd,
                  IF(
                      c.local_currency = 'rub',
                      IF(cpa_profit_rub IS NULL, cpa_profit_usd, cpa_profit_rub * :course_rub_usd),
                      IF(cpa_profit_eur IS NULL, cpa_profit_usd, cpa_profit_eur * :course_eur_usd)
                  )
              )
          ),
          cpa_profit_eur = IF(
              cpa_profit_eur IS NULL,
              NULL,
              IF(
                  c.local_currency = 'eur',
                  cpa_profit_eur,
                  IF(
                      c.local_currency = 'usd',
                      IF(cpa_profit_usd IS NULL, cpa_profit_eur, cpa_profit_usd * :course_usd_eur),
                      IF(cpa_profit_rub IS NULL, cpa_profit_eur, cpa_profit_rub * :course_rub_eur)
                  )
              )
          ),
          pp.updated_at = UNIX_TIMESTAMP()
        WHERE
          c.local_currency IN ('rub', 'usd', 'eur') $additionalConditions
        ;
    ")
      ->bindParam(':course_usd_rub', $usdRub)
      ->bindParam(':course_eur_rub', $eurRub)
      ->bindParam(':course_rub_usd', $rubUsd)
      ->bindParam(':course_eur_usd', $eurUsd)
      ->bindParam(':course_usd_eur', $usdEur)
      ->bindParam(':course_rub_eur', $rubEur)
      ->execute();
  }
}
