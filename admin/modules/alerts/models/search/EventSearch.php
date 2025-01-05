<?php

namespace admin\modules\alerts\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use admin\modules\alerts\models\Event;

/**
 * EventSearch represents the model behind the search form about `admin\modules\alerts\models\Event`.
 */
class EventSearch extends Event
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'priority', 'metric', 'is_active'], 'integer'],
            [['name'], 'safe'],
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
        $query = Event::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'priority' => $this->priority,
            'metric' => $this->metric,
            'is_active' => $this->is_active,
        ])
            ->andFilterWhere(['like', self::tableName() . '.' . 'name', $this->name]);;

        return $dataProvider;
    }
}
