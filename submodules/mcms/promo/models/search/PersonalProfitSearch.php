<?php

namespace mcms\promo\models\search;

use mcms\promo\components\UsersHelper;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\Provider;
use mcms\promo\Module;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\PersonalProfit;
use yii\db\Expression;

/**
 * PersonalProfitSearch represents the model behind the search form about `mcms\promo\models\PersonalProfit`.
 */
class PersonalProfitSearch extends PersonalProfit
{
  /**
   * @var array массив id стран для фильтра в гриде
   */
  public $countryId;

  /**
   * @var int id провайдера для фильтра
   */
  public $provider_id;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['countryId', 'each', 'rule' => ['integer']],
      [['user_id', 'operator_id', 'landing_id', 'provider_id', 'created_by', 'created_at', 'updated_at'], 'integer'],
      [['rebill_percent', 'buyout_percent', 'cpa_profit_rub', 'cpa_profit_eur', 'cpa_profit_usd'], 'number'],
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
    $query = PersonalProfit::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query
    ]);

    $dataProvider->getSort()->attributes = array_merge($dataProvider->getSort()->attributes, [
      'countryId' => [
        'asc' => [Operator::tableName() . '.country_id' => SORT_ASC],
        'desc' => [Operator::tableName() .'.country_id' => SORT_DESC],
      ]
    ]);

    $query->joinWith(['landing', 'provider', 'operator', 'country'], true, 'LEFT JOIN');

    /*
     * Прячем лендинги, заблокированные для реселлера
     */
    if (!Yii::$app->user->can(Module::PERMISSION_CAN_VIEW_BLOCKED_LANDINGS)) {
      $query->andWhere([
        'or',
        ['landing_id' => 0],
        ['<>', Landing::tableName() . '.' . 'status', Landing::STATUS_BLOCKED]
      ]);
    }

    $notAvailableUserIds = UsersHelper::getCurrentUserNotAvailableUsers();

    if (count($notAvailableUserIds) > 0) {
      $query->andFilterWhere(['not in', 'user_id', $notAvailableUserIds]);
      $query->orWhere(['user_id' => 0]);
    }

    /** запрещаем пользователям просматривать строки, которые относятся к ним и где не заполнена цена за выкуп */
    $query->andWhere([
      'or',
      ['<>', 'user_id', Yii::$app->user->id],
      ['user_id' => 0],
      new Expression('buyout_percent IS NOT NULL'),
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'user_id' => $this->user_id,
      'operator_id' => $this->operator_id,
      'landing_id' => $this->landing_id,
      'rebill_percent' => $this->rebill_percent,
      'buyout_percent' => $this->buyout_percent,
      'cpa_profit_rub' => $this->cpa_profit_rub,
      'cpa_profit_eur' => $this->cpa_profit_eur,
      'cpa_profit_usd' => $this->cpa_profit_usd,
      'created_by' => $this->created_by,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      Country::tableName() . '.id' => $this->countryId,
      Provider::tableName() . '.id' => $this->provider_id,
    ]);

    return $dataProvider;
  }
}