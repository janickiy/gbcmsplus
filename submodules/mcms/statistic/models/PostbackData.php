<?php

namespace mcms\statistic\models;

use Yii;
use yii\data\ArrayDataProvider;

/**
 * This is the model class for table "postback_data".
 *
 * @property string $id
 * @property string $handler_code
 * @property integer $provider_id
 * @property string $data
 * @property integer $time
 * @property integer $is_handled
 */
class PostbackData extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'postback_data';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['handler_code', 'time'], 'required'],
      [['provider_id', 'time', 'is_handled'], 'integer'],
      [['data'], 'string'],
      [['handler_code'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'handler_code' => 'Handler',
      'provider_id' => 'Provider ID',
      'data' => 'All Fields',
      'time' => 'Time',
      'is_handled' => 'Is handled',
      'hitId' => 'Hit ID',
      'currency' => 'Currency code',
    ];
  }

  /**
   * @return ArrayDataProvider
   */
  public function getDataProvider()
  {
    return new ArrayDataProvider(['models' => json_decode($this->data)]);
  }
}
