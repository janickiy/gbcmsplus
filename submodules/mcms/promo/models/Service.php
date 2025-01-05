<?php

namespace mcms\promo\models;

use Yii;

/**
 * This is the model class for table "services".
 *
 * @property integer $id
 * @property integer $external_id
 * @property string $name
 * @property string $url
 * @property integer $provider_id
 * @property integer $status
 * @property integer $sync_at
 */
class Service extends \yii\db\ActiveRecord
{
  const STATUS_INACTIVE = 0;
  const STATUS_ACTIVE = 1;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'services';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['external_id', 'provider_id', 'sync_at'], 'integer'],
      [['name', 'url', 'provider_id', 'status', 'sync_at'], 'required'],
      [['name', 'url'], 'string', 'max' => 255],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'external_id' => 'External ID',
      'name' => 'Name',
      'url' => 'Url',
      'provider_id' => 'Provider ID',
      'status' => 'Status',
      'sync_at' => 'Sync At',
    ];
  }

  /**
   * @return array
   */
  public static function getServicesMap()
  {
    return self::find()
      ->select('name')
      ->andWhere(['status' => self::STATUS_ACTIVE])
      ->orderBy('id')
      ->indexBy('id')
      ->column();
  }
}
