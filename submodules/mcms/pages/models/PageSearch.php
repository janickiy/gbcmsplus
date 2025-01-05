<?php

namespace mcms\pages\models;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * PageSearch represents the model behind the search form about `mcms\pages\models\Page`.
 */
class PageSearch extends Page
{

  const SCENARIO_SEARCH = 'search';

  public $cr_from;
  public $cr_to;
  public $up_from;
  public $up_to;

  public $category;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['seo_title', 'seo_keywords'], 'string', 'max' => 500],
      [['name', 'text', 'seo_description','url','code'], 'string'],
      [['noindex', 'is_disabled'], 'boolean'],
      [['cr_from', 'cr_to', 'up_from', 'up_from'], 'date', 'format' => 'php:Y-m-d'],
      [['category'], 'safe']
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_SEARCH => ['name', 'text', 'seo_title', 'seo_keywords', 'seo_description', 'noindex', 'is_disabled',
        'created_at', 'updated_at', 'cr_from', 'cr_to', 'up_from', 'up_from', 'url', 'code', 'page_category_id', 'category']
    ]);
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params, $withoutFormName = false)
  {
    $query = Page::find();

    $query->joinWith(['category']);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['id' => SORT_DESC]
      ]
    ]);

    $params = $withoutFormName ? [$this->formName() => $params] : $params;

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }
    $query->andFilterWhere([
      Page::tableName() . '.id' => $this->id,
      Page::tableName() . '.noindex' => $this->noindex,
      Page::tableName() . '.is_disabled' => $this->is_disabled,
      Page::tableName() . '.code' => $this->code,
      Page::tableName() . '.url' => $this->url,
      Page::tableName() . '.page_category_id' => $this->page_category_id
    ]);
    $query->andFilterWhere(['like', Page::tableName() . '.name', $this->name]);
    $query->andFilterWhere(['>=', 'created_at', !empty($this->cr_from) ? strtotime($this->cr_from . ' 00:00:00') : null]);
    $query->andFilterWhere(['<=', 'created_at', !empty($this->cr_to) ? strtotime($this->cr_to . ' 23:59:59') : null]);
    $query->andFilterWhere(['>=', 'updated_at', !empty($this->up_from) ? strtotime($this->up_from . ' 00:00:00') : null]);
    $query->andFilterWhere(['<=', 'updated_at', !empty($this->up_to) ? strtotime($this->up_to . ' 23:59:59') : null]);

    if (!empty($this->category)) {
      $query->andFilterWhere([
        Category::tableName() . '.code' => ArrayHelper::getValue($this->category, 'code'),
      ]);
    }


    return $dataProvider;
  }

}
