<?php

namespace mcms\loyalty\models;

use mcms\common\traits\Translate;
use rgk\utils\components\CurrenciesValues;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Модель для работы с бонусами на стороне MCMS
 * @property integer $id
 * @property integer $external_id id записи на MGMP
 * @property integer $external_invoice_id ID инвойса на MGMP
 * @property string $amount_usd Сумма бонуса в долларах
 * @property string $comment Комментарий, который будет отображен реселлеру в компенсации
 * @property string|null $type Код типа бонуса.
 * Максимальная длина VARCHAR(32)
 * Используется для отображения информации о рассчете бонуса в интерфейсе в соответствии с типом бонуса
 * @see AbstractBonusRule::getCode()
 * @property string $details_json Детали рассчета в JSON
 * @see LoyaltyBonusDetails
 * @see getDetails
 * @see setDetails
 * @property string $decline_reason Причина отклонения бонуса
 * @property integer $status Статус бонуса. Одобрен/отклонен
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property string $statusName
 */
class LoyaltyBonus extends \yii\db\ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'loyalty.bonuses.';

  /** @const int[] Все возможные статусы */
  const STATUSES = [
    self::STATUS_AWAITING,
    self::STATUS_APPROVED,
    self::STATUS_DECLINED,
  ];
  /** @const int В ожидании решения */
  const STATUS_AWAITING = 0;
  /** @const int Одобрен */
  const STATUS_APPROVED = 1;
  /** @const int Отклонен */
  const STATUS_DECLINED = 2;

  /** @var array Мап статусов */
  private static $mappedStatuses;

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
    return 'loyalty_bonuses';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['amount_usd', 'status'], 'required'],
      [['external_invoice_id', 'status', 'created_at', 'updated_at', 'external_id'], 'integer'],
      [['amount_usd'], 'number'],
      [['comment', 'decline_reason', 'type', 'details_json'], 'string'],
      ['status', 'in', 'range' => static::STATUSES],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels(array_keys($this->getAttributes()));
  }

  /**
   * Типы
   * @return array
   */
  public static function typeNameList()
  {
    return [
      [
        'id' => TurnoverRule::getCode(),
        'name' => static::t('type_turnover'),
      ],
      [
        'id' => GrowRule::getCode(),
        'name' => static::t('type_grow'),
      ],
    ];
  }

  /**
   * Название типа
   * @return string
   */
  public function getTypeName()
  {
    $types = ArrayHelper::map(self::typeNameList(), 'id', 'name');
    return ArrayHelper::getValue($types, $this->type);
  }

  /**
   * Статусы
   * @return array
   */
  public static function statusNameList()
  {
    return [
      [
        'id' => static::STATUS_AWAITING,
        'name' => static::t('status_awaiting'),
      ],
      [
        'id' => self::STATUS_APPROVED,
        'name' => static::t('status_approved'),
      ],
      [
        'id' => self::STATUS_DECLINED,
        'name' => static::t('status_declined'),
      ],
    ];
  }

  /**
   * Название статуса
   * @return string
   */
  public function getStatusName()
  {
    return ArrayHelper::getValue(self::getMappedStatuses(), $this->status);
  }

  /**
   * Мап статусов
   * @return array
   */
  private static function getMappedStatuses()
  {
    if (!self::$mappedStatuses) {
      self::$mappedStatuses = ArrayHelper::map(self::statusNameList(), 'id', 'name');
    }

    return self::$mappedStatuses;
  }

  /**
   * Доступна ли детальная информация
   * @return bool
   */
  public function isAvailableDetails()
  {
    return !empty($this->details_json);
  }

  /**
   * Детали рассчета бонуса
   * @return LoyaltyBonusDetails
   * @throws \yii\base\InvalidParamException
   */
  public function getDetails()
  {
    if (!$this->isAvailableDetails()) return new LoyaltyBonusDetails;

    $loyaltyBonusDetails = new LoyaltyBonusDetails;

    $detailsArray = Json::decode($this->details_json);
    // Установка сделана через setAttributes, что бы скрипт не падал при отсутствии аттрибутов
    $loyaltyBonusDetails->setAttributes($detailsArray, false);

    $turnoverLastMonth = ArrayHelper::getValue($detailsArray, 'turnoverLastMonth', []);
    $turnoverBeforeLastMonth = ArrayHelper::getValue($detailsArray, 'turnoverBeforeLastMonth', []);
    $turnoverThreeMonthAgo = ArrayHelper::getValue($detailsArray, 'turnoverThreeMonthAgo', []);

    $loyaltyBonusDetails->turnoverLastMonth = CurrenciesValues::createByValues($turnoverLastMonth);
    $loyaltyBonusDetails->turnoverBeforeLastMonth = CurrenciesValues::createByValues($turnoverBeforeLastMonth);
    $loyaltyBonusDetails->turnoverThreeMonthAgo = CurrenciesValues::createByValues($turnoverThreeMonthAgo);

    return $loyaltyBonusDetails;
  }

  /**
   * Детали рассчета бонуса
   * @param LoyaltyBonusDetails $details
   */
  public function setDetails(LoyaltyBonusDetails $details)
  {
    $this->details_json = Json::encode($details);
  }

  /**
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'details_json' => false,
    ];
  }
}
