<?php

namespace mcms\promo\models;

use Yii;

/**
 * This is the model class for table "external_providers".
 *
 * @property integer $id
 * @property integer $external_id
 * @property string $name
 * @property string $local_name
 * @property string $url
 * @property integer $status
 * @property integer $provider_id
 * @property integer $sync_at
 * @property integer $country_id
 *
 * @property Country $country
 */
class ExternalProvider extends \yii\db\ActiveRecord
{
  const STATUS_INACTIVE = 0;
  const STATUS_ACTIVE = 1;

  const DEFAULT_LOCAL_NAME = 'Provider';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'external_providers';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name', 'external_id'], 'required'],
      [['name', 'local_name'], 'string', 'max' => 100],
      [['url'], 'string', 'max' => 255],
      [['status', 'provider_id', 'sync_at', 'external_id', 'country_id',], 'integer'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'local_name' => 'Name',
      'url' => 'Url',
      'status' => 'Status',
      'provider_id' => 'Provider Id',
    ];
  }

  /**
   * @return array
   */
  public static function getExternalProvidersMap()
  {
    $models = self::find()
      ->andWhere(['status' => self::STATUS_ACTIVE])
      ->orderBy('id')
      ->indexBy('id')
      ->all();

    return array_map(function ($model) {
      /** @var ExternalProvider $model */
      return $model->getDisplayValue();
    }, $models);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCountry()
  {
    return $this->hasOne(Country::class, ['id' => 'country_id']);
  }

  /**
   * @return string|null
   */
  public function getCountryLink()
  {
    return $this->country ? $this->country->getViewLink() : null;
  }

  /**
   * Имя провайдера для отображения в гриде
   * @return string
   */
  public function getDisplayValue()
  {
    return $this->local_name ?:  self::DEFAULT_LOCAL_NAME . '_' . $this->id;
  }
}
