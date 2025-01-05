<?php

namespace mcms\statistic\models;

use mcms\promo\models\Provider;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\Json;

/**
 * This is the model class for table "postback_data_test".
 *
 * @property integer $id
 * @property integer $provider_id
 * @property string $requestData
 * @property string $responseData
 * @property integer $time
 * @property integer $status
 */
class PostbackDataTest extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'postback_data_test';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['provider_id', 'time', 'status'], 'integer'],
      [['requestData', 'responseData'], 'string'],
      [['time'], 'required'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('statistic.postback_data_test.id'),
      'provider_id' => Yii::_t('statistic.postback_data_test.provider_id'),
      'requestData' => Yii::_t('statistic.postback_data_test.requestData'),
      'responseData' => Yii::_t('statistic.postback_data_test.responseData'),
      'status' => Yii::_t('statistic.postback_data_test.status'),
      'time' => Yii::_t('statistic.postback_data_test.time'),
    ];
  }

  /**
   * @return ArrayDataProvider
   */
  public function getRequestDataProvider() : ArrayDataProvider
  {
    return new ArrayDataProvider(['models' => $this->getDecodedData($this->requestData)]);
  }
  /**
   * @return ArrayDataProvider
   */
  public function getResponseDataProvider() : ArrayDataProvider
  {
    return new ArrayDataProvider(['models' => $this->getDecodedData($this->responseData)]);
  }

  /**
   * @param string $data
   * @return array
   */
  protected function getDecodedData($data) : array
  {
    return array_map(function($value) {
      if (isset($value['errors'])) {
        // В $value['errors'] может быть NULL
        $value['errors'] = implode('<br>', (array)$value['errors']);
      }

      return $value;
    }, $data ? Json::decode($data) : []);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProvider()
  {
    return $this->hasOne(Provider::class, ['id' => 'provider_id']);
  }
}
