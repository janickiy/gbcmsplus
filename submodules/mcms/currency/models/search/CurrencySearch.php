<?php

namespace mcms\currency\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\currency\models\Currency;

/**
 * CurrencySearch represents the model behind the search form about `mcms\promo\models\Currency`.
 */
class CurrencySearch extends Currency
{
  public $countryId;
  public $customCourseType;

  //фильтр по заполенным кастомным курсам
  const CUSTOM_COURSE_FILLED = 'filled';
  //фильтр по незаполенным кастомным курсам
  const CUSTOM_COURSE_NOT_FILLED = 'not_filled';
  //фильтр по кастомным курсам с алертами
  const CUSTOM_COURSE_ALERT = 'alert';

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'created_at', 'updated_at', 'countryId'], 'integer'],
      [['name', 'code', 'symbol', 'customCourseType'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = Currency::find()->with('countries');

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_ASC,
        ]
      ],
    ]);

    $this->load($params);


    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
    ]);

    if ($this->countryId) {
      $query->innerJoin('countries c', 'c.local_currency = ' . self::tableName() . '.code');
      $query->andWhere(['c.id' => $this->countryId]);
    }

    if ($this->customCourseType === self::CUSTOM_COURSE_FILLED) {
      $query->andWhere('custom_to_eur iS NOT NULL OR custom_to_rub IS NOT NULL OR custom_to_usd IS NOT NULL');
    }

    if ($this->customCourseType === self::CUSTOM_COURSE_NOT_FILLED) {
      $query->andWhere('custom_to_eur iS NULL AND custom_to_rub IS NULL AND custom_to_usd IS NULL');
    }

    if ($this->customCourseType === self::CUSTOM_COURSE_ALERT) {
      //TRICKY в фильтрации по алертам скрываются строки чтобы не дублировать логику, поэтому пагинацию скрываю
      $dataProvider->pagination = false;
    }

    $query->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'symbol', $this->symbol])
      ->andFilterWhere(['like', 'code', $this->code]);

    return $dataProvider;
  }

  /**
   *
   * @return array
   */
  public function getCustomCourseTypes()
  {
    return [
      self::CUSTOM_COURSE_FILLED => Yii::_t('currency.main.custom_course_filled'),
      self::CUSTOM_COURSE_NOT_FILLED => Yii::_t('currency.main.custom_course_not_filled'),
      self::CUSTOM_COURSE_ALERT => Yii::_t('currency.main.custom_course_alert'),
    ];
  }

}
