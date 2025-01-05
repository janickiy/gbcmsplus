<?php

namespace mcms\logs\models;

use Yii;

/**
 * This is the model class for table "logs".
 *
 * @property integer $id
 * @property string $EventLabel
 * @property string $EventData
 * @property integer $created_at
 */
class Logs extends \kak\clickhouse\ActiveRecord
{

  /**
   * @return \kak\clickhouse\Connection
   */
  public static function getDb()
  {
    return Yii::$app->clickhouse;
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'system_logs';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['EventTime', 'default', 'value' => date("Y-m-d H:i:s")],
      [['EventLabel', 'EventTime'], 'required'],
      [['EventLabel'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'EventLabel' => Yii::_t('logs.main.attribute-label'),
      'EventData' => Yii::_t('logs.main.attribute-data'),
      'EventTime' => Yii::_t('logs.main.attribute-created_at')
    ];
  }

}