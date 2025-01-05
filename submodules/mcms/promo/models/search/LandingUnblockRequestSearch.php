<?php

namespace mcms\promo\models\search;

use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Operator;
use mcms\promo\Module;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\LandingUnblockRequest;
use yii\db\Expression;

/**
 * LandingUnblockRequestSearch represents the model behind the search form about `mcms\promo\models\LandingUnblockRequest`.
 */
class LandingUnblockRequestSearch extends LandingUnblockRequest
{
  const SCENARIO_ADMIN = 'admin';
  /*
  * @var string
  */
  public $orderByFieldStatus;

  /**
   * @var int[] Страны, к которым относится лендинг (для фильтров)
   */
  public $countries;
  /**
   * @var int[] Операторы, к которым относится лендинг (для фильтров)
   */
  public $operators;

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['id', 'status', 'landing_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
      [['traffic_type', 'description', 'createdFrom', 'createdTo', 'countries', 'operators'], 'safe'],
    ];
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'status', 'landing_id', 'user_id', 'description', 'createdFrom', 'createdTo', 'countries', 'operators'],
    ]);
  }

  /**
   * @param $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = LandingUnblockRequest::find();

    $query->joinWith(['landing']);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
    ]);

    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_MODERATION;
    }

    $this->load($params);

    if (!$this->validate()) return $dataProvider;

    $query->andFilterWhere([
      self::tableName() . '.' . 'id' => $this->id,
      self::tableName() . '.' . 'status' => $this->status,
      self::tableName() . '.' . 'landing_id' => $this->landing_id,
      self::tableName() . '.' . 'user_id' => $this->user_id,
    ]);

    $query->andFilterWhere(['like', self::tableName() . '.' . 'traffic_type', $this->traffic_type])
      ->andFilterWhere(['like', self::tableName() . '.' . 'description', $this->description]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    /*
     * Прячем заявки на лендинги, которые заблокированы для реселлера
     */
    if (!Yii::$app->user->can(Module::PERMISSION_CAN_VIEW_BLOCKED_LANDINGS)) {
      $query->andFilterWhere(['<>', Landing::tableName() . '.' . 'status', Landing::STATUS_BLOCKED]);
    }

    if ($this->operators) {
      $query->joinWith(['landing.landingOperator']);
      $query->andFilterWhere([LandingOperator::tableName() . '.operator_id' => $this->operators]);
    }
    if ($this->countries) {
      $query->joinWith(['landing.landingOperator.operator']);
      $query->andFilterWhere([Operator::tableName() . '.country_id' => $this->countries]);
    }

    if ($this->orderByFieldStatus !== null) {
      $query->orderBy([new Expression(
        'FIELD (' . self::tableName() . '.status, :orderByFieldStatus) DESC',
        [':orderByFieldStatus' => $this->orderByFieldStatus]
      )]);
    }

    // Скрытие элементов недоступных пользователей
    Yii::$app->user->identity->filterUsersItems($query, $this, 'user_id');

    return $dataProvider;
  }
}
