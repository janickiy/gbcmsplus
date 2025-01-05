<?php

namespace admin\modules\credits\models\search;

use admin\modules\credits\models\CreditTransaction;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CreditTransactionSearch represents the model behind the search form about `admin\modules\credits\models\CreditTransaction`.
 */
class CreditTransactionSearch extends Model
{

    const DATE_RANGE_DELIMITER = ' - ';

    /** @var  int */
    public $id;
    /** @var  int|int[] */
    public $creditId;
    /** @var  float */
    public $fromAmount;
    /** @var  float */
    public $toAmount;
    /** @var string */
    public $createdDateRange;
    /** @var  int|int[] */
    public $type;
    /** @var  bool */
    public $paysAndFeesOnly;
    /** @var  string */
    public $internalComment;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['id', 'createdDateRange', 'type', 'internalComment'], 'safe'],
            [['fromAmount', 'toAmount'], 'number'],
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
        $query = CreditTransaction::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'default' => [
                        'desc' => ['created_at' => SORT_DESC],
                    ],
                    'id',
                    'amount',
                    'comment',
                    'type',
                    'created_at',
                ],
                'defaultOrder' => [
                    'default' => SORT_DESC,
                ],
            ]
        ]);

        $query->with('credit');

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'credit_id' => $this->creditId,
            'type' => $this->type,
        ]);

        if ($this->paysAndFeesOnly) {
            $query->andWhere(['type' => [
                CreditTransaction::TYPE_MANUAL_PAYMENT,
                CreditTransaction::TYPE_BALANCE_PAYMENT,
                CreditTransaction::TYPE_MONTHLY_FEE,
            ]]);
        }

        if (!empty($this->createdDateRange) && strpos($this->createdDateRange, '-') !== false) {
            list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->createdDateRange);
            $query->andFilterWhere([
                'between',
                CreditTransaction::tableName() . '.created_at',
                strtotime($startDate),
                strtotime($endDate . ' +1day') - 1
            ]);
        }

        if ($this->fromAmount) {
            $query->andFilterWhere(['>=', 'amount', $this->fromAmount]);
        }

        if ($this->toAmount) {
            $query->andFilterWhere(['<=', 'amount', $this->toAmount]);
        }

        return $dataProvider;
    }
}
