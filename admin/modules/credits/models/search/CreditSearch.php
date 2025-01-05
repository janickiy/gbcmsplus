<?php

namespace admin\modules\credits\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use admin\modules\credits\models\Credit;
use yii\db\Expression;

/**
 * CreditSearch represents the model behind the search form about `admin\modules\credits\models\Credit`.
 */
class CreditSearch extends Model
{

    const DATE_RANGE_DELIMITER = ' - ';

    /** @var  int */
    public $id;
    /** @var  float */
    public $fromAmount;
    /** @var  float */
    public $toAmount;
    /** @var string */
    public $currency;
    /** @var  int|int[] */
    public $status;
    /** @var  float */
    public $percent;
    /** @var string */
    public $createdDateRange;
    /** @var string */
    public $closedDateRange;
    /** @var  float */
    public $fromDebtSum;
    /** @var  float */
    public $toDebtSum;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['fromAmount', 'toAmount', 'percent'], 'number'],
            [['currency', 'createdDateRange', 'closedDateRange', 'fromDebtSum', 'toDebtSum'], 'safe'],
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
        $query = Credit::find();

        $query->withTransactionsSum();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    // TRICKY запрошено -> активен -> выплачен -> отказан. И все это по дате
                    'default' => [
                        'desc' => [
                            new Expression('FIELD(status,:requested,:active,:done,:declined) ASC', [
                                ':requested' => Credit::STATUS_REQUESTED,
                                ':active' => Credit::STATUS_ACTIVE,
                                ':done' => Credit::STATUS_DONE,
                                ':declined' => Credit::STATUS_DECLINED,
                            ]),
                            new Expression(Credit::tableName() . '.created_at DESC'),
                        ],
                    ],
                    'id',
                    'amount',
                    'currency',
                    'percent',
                    'status',
                    'created_at',
                    'closed_at',
                    'debtSum'
                ],
                'defaultOrder' => [
                    'default' => SORT_DESC,
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }


        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'percent' => $this->percent,
        ]);

        if (!empty($this->createdDateRange) && strpos($this->createdDateRange, '-') !== false) {
            list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->createdDateRange);
            $query->andFilterWhere([
                'between',
                Credit::tableName() . '.created_at',
                strtotime($startDate),
                strtotime($endDate . ' +1day') - 1
            ]);
        }

        if (!empty($this->closedDateRange) && strpos($this->closedDateRange, '-') !== false) {
            list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->closedDateRange);
            $query->andFilterWhere([
                'between',
                Credit::tableName() . '.closed_at',
                strtotime($startDate),
                strtotime($endDate . ' +1day') - 1
            ]);
        }

        $query->andFilterWhere(['like', 'currency', $this->currency]);

        if ($this->fromAmount) {
            $query->andFilterWhere(['>=', 'amount', $this->fromAmount]);
        }

        if ($this->toAmount) {
            $query->andFilterWhere(['<=', 'amount', $this->toAmount]);
        }

        if ($this->fromDebtSum) {
            $query->andFilterWhere(['>=', 'debt', $this->fromDebtSum]);
        }

        if ($this->toDebtSum) {
            $query->andFilterWhere(['<=', 'debt', $this->toDebtSum]);
        }

        return $dataProvider;
    }
}
