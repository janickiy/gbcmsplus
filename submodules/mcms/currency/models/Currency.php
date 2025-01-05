<?php

namespace mcms\currency\models;

use mcms\common\traits\Translate;
use mcms\common\validators\AlphanumericalValidator;
use mcms\currency\components\events\CustomCourseBecameUnprofitable;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\models\Country;
use Yii;
use yii\behaviors\TimestampBehavior;
use mcms\common\multilang\MultiLangModel;
use yii\helpers\ArrayHelper;
use mcms\common\helpers\Link;

/**
 * This is the model class for table "currencies".
 *
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $symbol
 * @property float $to_rub
 * @property float $to_usd
 * @property float $to_eur
 * @property float $custom_to_rub
 * @property float $custom_to_usd
 * @property float $custom_to_eur
 * @property float $partner_percent_rub
 * @property float $partner_percent_usd
 * @property float $partner_percent_eur
 * @property Country[] $countries
 */
class Currency extends MultiLangModel
{

  use Translate;

  const LANG_PREFIX = 'currency.main.';
  const MAX_PERCENT = 20;
  const DEFAULT_PARTNER_PERCENT = 2;
  const DEFAULT_RESELLER_PERCENT = 2;
  const SCENARIO_SYNC = 'sync';


  /**
   * Ключ для кеширования выборки из таблицы currencies в микросервисах
   */
  const MS_CACHE_KEY = 'currencies_data_cache_key';


  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'currencies';
  }

  /**
   * @return array - список мультиязычных аттрибутов
   */
  public function getMultilangAttributes()
  {
    return [
      'name'
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [
        ['partner_percent_rub', 'partner_percent_usd', 'partner_percent_eur'],
        'default',
        'value' => self::DEFAULT_PARTNER_PERCENT,
        'on' => self::SCENARIO_SYNC
      ],
      [['partner_percent_rub', 'partner_percent_usd', 'partner_percent_eur'], 'required'],
      [['code'], 'required'],
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
      [['code'], 'string', 'max' => 10],
      [['code'], AlphanumericalValidator::class],
      [['symbol'], 'string', 'max' => 20],
      [['to_rub', 'to_usd', 'to_eur', 'custom_to_rub', 'custom_to_usd', 'custom_to_eur', 'partner_percent_rub', 'partner_percent_usd', 'partner_percent_eur'], 'double'],
      [['partner_percent_rub', 'partner_percent_usd', 'partner_percent_eur'], 'number', 'min' => 0, 'max' => self::MAX_PERCENT],
      ['code', 'unique'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return ArrayHelper::merge(parent::scenarios(), [
      self::SCENARIO_SYNC
    ]);
  }


  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'name',
      'code',
      'partner_percent_rub',
      'partner_percent_usd',
      'partner_percent_eur',
      'custom_to_rub',
      'custom_to_usd',
      'custom_to_eur',
    ]);
  }

  /**
   * При любом изменении курсов сбрасываем кеш в микросервисах и пишем лог
   * В синке игнорируем, чтобы писать лог только один раз (метод createoldCoursesLog() запускается в самом синке)
   *
   * Если после синка курс изменился и кастомный курс стал невыгоден реселлеру, триггерим соответствующее событие
   * @param bool $insert
   * @return bool
   */
  public function beforeSave($insert)
  {
    if ($this->getScenario() === self::SCENARIO_SYNC) {
      $this->checkBadCustomCourses();
    }

    if ($this->getScenario() !== self::SCENARIO_SYNC) {
      ApiHandlersHelper::clearCache(self::MS_CACHE_KEY);
      self::createoldCoursesLog();
    }

    return parent::beforeSave($insert);
  }

  /**
   * Проверяем кастомные курсы, на предмет выгодны ли они реселлеру
   */
  private function checkBadCustomCourses()
  {
    foreach (['rub', 'usd', 'eur'] as $currency) {
      // TRICKY: Танцы с бубном для проверки изменилось ли значение.
      // TRICKY: 2 проблемы: сравнение float (решена round()) и разные типы (решена приведением к float)

      // Если аттрибут не изменился проверку не делаем
      $oldValue = isset($this->oldAttributes['to_' . $currency])
        ? round((float)$this->oldAttributes['to_' . $currency], 9)
        : null;
      $newValue = round((float)$this->{'to_' . $currency}, 9);

      if ($oldValue === $newValue) {
        continue;
      }
      if ($this->isCustomCourseProfitable($currency)) {
        continue;
      }

      (new CustomCourseBecameUnprofitable($this, $currency))->trigger();
    }
  }

  /**
   * Выгоден ли кастомный курс
   * @param string $to валюта, курс конвертации в которую проверяем
   * @return bool
   */
  public function isCustomCourseProfitable($to)
  {
    // Кастомный курс
    $customCourseAttribute = 'custom_to_' . $to;
    $customCourse = $this->$customCourseAttribute === null
      ? null
      : (float)$this->$customCourseAttribute;

    // Оригинальный курс
    $originalCourseAttribute = 'to_' . $to;
    $originalCourse = (float)$this->$originalCourseAttribute;
    // Процент комиссии при конвертации
    $partnerPercent = (float)$this->{'partner_percent_' . $to};

    // Оригинальный курс с учетом комиссии
    $withPercent = $originalCourse * (100 - $partnerPercent) / 100;

    // Если кастомный курс не задан - все ок
    if ($customCourse === null) {
      return true;
    }
    // Если кастомный курс не выше оригинального с процентами - все ок
    if ($withPercent >= $customCourse) {
      return true;
    }

    // курс не выгодный
    return false;
  }

  /**
   * Логирование текущих значений курсов
   */
  public static function createoldCoursesLog()
  {
    $sql = '/** @lang MySQL */
        INSERT INTO currency_courses_log
            (`currency_id`, `to_rub`, `to_usd`, `to_eur`, `custom_to_rub`, `custom_to_usd`, `custom_to_eur`, `partner_percent_rub`, `partner_percent_usd`, `partner_percent_eur`, `created_at`, `updated_at`)
          SELECT 
            id, to_rub, to_usd, to_eur, `custom_to_rub`, `custom_to_usd`, `custom_to_eur`, partner_percent_rub, partner_percent_usd, partner_percent_eur, created_at, updated_at
          FROM currencies;';

    Yii::$app->db->createCommand($sql)->execute();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCountries()
  {
    return $this->hasMany(Country::class, ['local_currency' => 'code']);
  }

  /**
   * @param string $glue
   * @return string
   */
  public function getCountriesLinks($glue = ', ')
  {
    $linkList = [];
    foreach ($this->countries as $country) {
      $linkList[] = $country->getViewLink();
    }

    return implode($glue, $linkList);
  }

  /**
   * @return array
   */
  public static function getCountriesList()
  {
    return Country::getDropdownItems(Country::STATUS_ACTIVE);
  }

}
