<?php

namespace mcms\statistic\models;

use mcms\common\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "complains".
 *
 * @property string $id
 * @property string $hit_id
 * @property string $trans_id
 * @property string $time
 * @property string $date
 * @property integer $hour
 * @property string $landing_id
 * @property string $source_id
 * @property string $operator_id
 * @property string $platform_id
 * @property integer $landing_pay_type_id
 * @property string $provider_id
 * @property string $country_id
 * @property string $stream_id
 * @property string $user_id
 * @property string $description
 * @property string $label1
 * @property string $label2
 * @property string $phone
 * @property string $created_at
 * @property string $updated_at
 * @property integer $type
 */
class Complain extends \yii\db\ActiveRecord
{
  const TYPE_TEXT = 1;
  const TYPE_CALL = 2; // звонок в КЦ RGK
  const TYPE_AUTO_24 = 3; // отписка за 24ч с времени подписки
  const TYPE_AUTO_MOMENT = 4; // отписка за 15мин с времени подписки (моментальная отписка типа)
  const TYPE_AUTO_DUPLICATE = 5; // дубликат подписки на того же абонента
  const TYPE_CALL_MNO = 6; // звонок в КЦ оператора

  const TYPE_AUTO_FAKE_TRANS_ID_PREFIX = 'autocomplain_';
  /** Кол-во секунд за которое считаем отписку 24 */
  const AUTO_24_DEFAULT_DEADLINE = 24 * 60 * 60;
  /** Кол-во секунд за которое считаем отписку моментальной */
  const AUTO_MOMENT_DEFAULT_DEADLINE = 15 * 60;
  /** Кол-во секунд за которое считаем дубликат абонента */
  const AUTO_DUPLICATE_DEFAULT_DEADLINE = 24 * 60 * 60;
  /** Интервал для задержки отправки постбека от 1 часа */
  const COMPLAIN_DELAY_FROM_SEC = 3600;
  /** Интервал для задержки отправки постбека до 3 часов */
  const COMPLAIN_DELAY_TO_SEC = 3 * 3600;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'complains';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['hit_id', 'trans_id', 'time', 'date', 'hour', 'phone', 'created_at', 'updated_at'], 'required'],
      [['hit_id', 'time', 'hour', 'landing_id', 'source_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'provider_id', 'country_id', 'stream_id', 'source_id', 'user_id', 'created_at', 'updated_at', 'type'], 'integer'],
      [['date'], 'safe'],
      [['trans_id'], 'string', 'max' => 64],
      [['description', 'label1', 'label2'], 'string', 'max' => 512],
      [['phone'], 'string', 'max' => 16],
      [['hit_id', 'type'], 'unique', 'targetAttribute' => ['hit_id', 'type'], 'message' => 'The combination of Hit ID and Type has already been taken.'],
    ];
  }

  /**
   * Получить хиты, на которые надо жалобу из-за быстрой отписки.
   * @param $hitIds
   * @return array Пример: [123 => true, 2412 => false].
   * Ключ - hit_id, Значение - is_moment (тип жалобы, иначе auto24)
   */
  public static function getInstantOffsHitIds($hitIds)
  {
    if (empty($hitIds))  {
      return [];
    }

    $hits = implode(',', $hitIds);

    $rows = Yii::$app->db->createCommand("SELECT off.hit_id, IF(off.time <= (s.time + :deltaMoment), 1, 0) as is_moment FROM subscription_offs off
      INNER JOIN subscriptions s ON s.hit_id = off.hit_id
      WHERE off.hit_id IN ($hits) AND (off.time <= (s.time + :delta24) OR off.time <= (s.time + :deltaMoment));
      ", [
        ':deltaMoment' => self::AUTO_MOMENT_DEFAULT_DEADLINE,
        ':delta24' => self::AUTO_24_DEFAULT_DEADLINE,
      ]);

    $result = [];
    foreach ($rows->queryAll() as $row) {
      $result[ArrayHelper::getValue($row, 'hit_id')] = (bool)ArrayHelper::getValue($row, 'is_moment');
    }

    return $result;
  }

  /**
   * Добавление жалобы
   * @param $complainHit integer id хита
   * @param $hitInfo array информация по хиту
   * @param $isMoment bool моментальная ли жалоба
   */
  public static function add($complainHit, $hitInfo, $isMoment)
  {
    $sql = Yii::$app->db->createCommand()->insert(
      'complains',
      [
        'hit_id' => $complainHit,
        'trans_id' => Complain::TYPE_AUTO_FAKE_TRANS_ID_PREFIX . microtime(1),
        'time' => time(),
        'date' => date('Y-m-d'),
        'hour' => date('H'),
        'type' => $isMoment ? Complain::TYPE_AUTO_MOMENT : Complain::TYPE_AUTO_24,
        'landing_id' => $hitInfo['landing_id'],
        'source_id' => $hitInfo['source_id'],
        'operator_id' => $hitInfo['operator_id'],
        'platform_id' => $hitInfo['platform_id'],
        'landing_pay_type_id' => $hitInfo['landing_pay_type_id'],
        'provider_id' => $hitInfo['provider_id'],
        'country_id' => $hitInfo['country_id'],
        'stream_id' => $hitInfo['stream_id'],
        'user_id' => $hitInfo['user_id'],
        'phone' => ArrayHelper::getValue($hitInfo, 'phone', '') ?: '',
        'created_at' => time(),
        'updated_at' => time(),
      ]
    )->rawSql;

    $sql .= ' ON DUPLICATE KEY UPDATE 
        updated_at = VALUES(updated_at), 
        description = VALUES(description), 
        time = VALUES(time),
        date = VALUES(date),
        hour = VALUES(hour),
        trans_id = VALUES(trans_id);';

    Yii::$app->db->createCommand($sql)->execute();
  }

  /**
   * Возвращает время задержки отправки постебека по жалобе
   * @return int количество секунд
   */
  public static function getPostbackDelay()
  {
    return rand(self::COMPLAIN_DELAY_FROM_SEC, self::COMPLAIN_DELAY_TO_SEC);
  }

  /**
   * @return array
   */
  public static function getTypes()
  {
    return [
      self::TYPE_TEXT => Yii::_t('statistic.statistic.complain_type_text'),
      self::TYPE_CALL => Yii::_t('statistic.statistic.complain_type_call'),
      self::TYPE_AUTO_24 => Yii::_t('statistic.statistic.complain_type_auto_24'),
      self::TYPE_AUTO_MOMENT => Yii::_t('statistic.statistic.complain_type_auto_moment'),
      self::TYPE_AUTO_DUPLICATE => Yii::_t('statistic.statistic.complain_type_auto_duplicate'),
      self::TYPE_CALL_MNO => Yii::_t('statistic.statistic.complain_type_call_mno'),
    ];
  }

}
