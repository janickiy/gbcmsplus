<?php
namespace mcms\payments\models\paysystems;

use yii\data\ActiveDataProvider;

class PaySystemApiSearch extends PaySystemApi
{
  public function rules()
  {
    return [];
  }

  /**
   * @param array $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = static::find();
    $this->load($params);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      $query->where('0=1');
      return $dataProvider;
    }

    return $dataProvider;
  }
}