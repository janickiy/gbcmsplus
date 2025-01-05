<?php

namespace mcms\support\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class SupportCategorySearch extends Model
{
  public $name;
  public $role;

  public function rules()
  {
    return [];
  }

  public function search(array $params)
  {
    $query = SupportCategory::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    $this->load($params);

    return $dataProvider;
  }
}