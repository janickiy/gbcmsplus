<?php
namespace mcms\promo\models\search;

use mcms\promo\models\RebillCorrectConditions;
use mcms\user\components\api\NotAvailableUserIds;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class RebillConditionsSearch extends RebillCorrectConditions
{
  /**
   * @var int|string
   */
  public $provider_id;

  /**
   * @return array
   */
  public static function getProvidersDropdown()
  {
    return self::find()
      ->select('p.name, p.id id')
      ->innerJoinWith('provider p')
      ->groupBy('p.id')
      ->indexBy('id')
      ->column();
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'partner_id', 'operator_id', 'landing_id', 'provider_id', 'created_by', 'created_at', 'updated_at'], 'integer'],
      ['percent', 'number'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
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
    $query = self::find()
      ->joinWith('provider p');

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
    ]);

    $query->joinWith(['landing', 'operator', 'partner'], true, 'LEFT JOIN');

    $this->load($params);
    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    $notAvailableUserIds = (new NotAvailableUserIds([
      'userId' => Yii::$app->user->id,
      'skipCurrentUser' => false,
    ]))->getResult();

    if (count($notAvailableUserIds) > 0) {
      $query->andFilterWhere(['not in', 'partner_id', $notAvailableUserIds]);
      $query->orWhere(['is', 'partner_id', null]);
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'partner_id' => $this->partner_id,
      'operator_id' => $this->operator_id,
      'landing_id' => $this->landing_id,
      'percent' => $this->percent,
      'created_by' => $this->created_by,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'p.id' => $this->provider_id,
    ]);

    return $dataProvider;
  }
}