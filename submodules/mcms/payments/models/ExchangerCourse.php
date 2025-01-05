<?php

namespace mcms\payments\models;

use mcms\payments\components\exchanger\CurrencyCourses;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class ExchangerCourse
 * @package mcms\payments\models
 * @property float $usd_rur
 * @property float $rur_usd
 * @property float $usd_eur
 * @property float $eur_usd
 * @property float $eur_rur
 * @property float $rur_eur
 * @property float $usd_rur_real
 * @property float $rur_usd_real
 * @property float $usd_eur_real
 * @property float $eur_usd_real
 * @property float $eur_rur_real
 * @property float $rur_eur_real
 * @property float $usd_rur_partner
 * @property float $rur_usd_partner
 * @property float $usd_eur_partner
 * @property float $eur_usd_partner
 * @property float $eur_rur_partner
 * @property float $rur_eur_partner
 * @property int $created_at
 */
class ExchangerCourse extends ActiveRecord
{
  const CACHE_KEY = 'payments.exchange_course_model';
  const CACHE_LIFETIME = 3600 * 4;

  public static function tableName()
  {
    return 'exchanger_courses';
  }

  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
        'updatedAtAttribute' => false
      ],
    ];
  }

  public function rules()
  {
    return [
      [['usd_rur', 'rur_usd', 'usd_eur', 'eur_usd', 'eur_rur', 'rur_eur', 'usd_rur_real', 'rur_usd_real',
        'usd_eur_real', 'eur_usd_real', 'eur_rur_real', 'rur_eur_real', 'usd_rur_partner',
        'rur_usd_partner', 'usd_eur_partner', 'eur_usd_partner', 'eur_rur_partner', 'rur_eur_partner'], 'required'],
      [['usd_rur', 'rur_usd', 'usd_eur', 'eur_usd', 'eur_rur', 'rur_eur', 'usd_rur_real', 'rur_usd_real',
        'usd_eur_real', 'eur_usd_real', 'eur_rur_real', 'rur_eur_real', 'usd_rur_partner',
        'rur_usd_partner', 'usd_eur_partner', 'eur_usd_partner', 'eur_rur_partner', 'rur_eur_partner'], 'double'],
    ];
  }

  /**
   * @return CurrencyCourses|null
   */
  public static function getCurrencyCourses()
  {
    $model = self::getLastExchangeCourse();

    return $model === null
      ? null
      : CurrencyCourses::load($model);
  }

  /**
   * @return CurrencyCourses|null
   */
  public static function getPartnerCurrencyCourses()
  {
    $model = self::getLastExchangeCourse();

    return $model === null
      ? null
      : CurrencyCourses::loadPartner($model);
  }

  /**
   * Получить последние курсы из бд
   * @return ExchangerCourse|null
   */
  private static function getLastExchangeCourse()
  {
    if (!$model = Yii::$app->cache->get(self::CACHE_KEY)) {
      $model = self::find()->orderBy(['created_at' => SORT_DESC])->one();
      Yii::$app->cache->set(self::CACHE_KEY, $model, self::CACHE_LIFETIME);
    }

    return $model;
  }

  public static function invalidateCache()
  {
    Yii::$app->cache->delete(self::CACHE_KEY);
  }

  public static function storeCurrencyCourses(CurrencyCourses $currencyCourses)
  {
    $instance = new self([
      'rur_usd' => $currencyCourses->rur_usd,
      'rur_eur' => $currencyCourses->rur_eur,
      'usd_rur' => $currencyCourses->usd_rur,
      'usd_eur' => $currencyCourses->usd_eur,
      'eur_usd' => $currencyCourses->eur_usd,
      'eur_rur' => $currencyCourses->eur_rur,
      'rur_usd_real' => $currencyCourses->rur_usd_real,
      'rur_eur_real' => $currencyCourses->rur_eur_real,
      'usd_rur_real' => $currencyCourses->usd_rur_real,
      'usd_eur_real' => $currencyCourses->usd_eur_real,
      'eur_usd_real' => $currencyCourses->eur_usd_real,
      'eur_rur_real' => $currencyCourses->eur_rur_real,
      'rur_usd_partner' => $currencyCourses->rur_usd_partner,
      'rur_eur_partner' => $currencyCourses->rur_eur_partner,
      'usd_rur_partner' => $currencyCourses->usd_rur_partner,
      'usd_eur_partner' => $currencyCourses->usd_eur_partner,
      'eur_usd_partner' => $currencyCourses->eur_usd_partner,
      'eur_rur_partner' => $currencyCourses->eur_rur_partner,
    ]);
    $instance->save();
    return $instance;
  }

}