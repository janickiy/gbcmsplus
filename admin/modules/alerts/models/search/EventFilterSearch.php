<?php

namespace admin\modules\alerts\models\search;


use yii\base\Model;
use yii\data\ActiveDataProvider;
use admin\modules\alerts\models\EventFilter;

/**
 * EventFilterSearch represents the model behind the search form about `admin\modules\alerts\models\EventFilter`.
 */
class EventFilterSearch extends EventFilter
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'event_id', 'type', 'value'], 'integer'],
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
        $query = EventFilter::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'event_id' => $this->event_id,
            'type' => $this->type,
            'value' => $this->value,
        ]);

        return $dataProvider;
    }
}
