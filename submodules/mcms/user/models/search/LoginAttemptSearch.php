<?php

namespace mcms\user\models\search;

use mcms\user\models\LoginAttempt;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class LoginAttemptSearch
 * @package mcms\user\models\search
 */
class LoginAttemptSearch extends Model
{
    /** @var int */
    public $id;
    /** @var int */
    public $user_id;
    /** @var string */
    public $login;
    /** @var string */
    public $password;
    /** @var string */
    public $ip;
    /** @var string */
    public $user_agent;
    /** @var int */
    public $fail_reason;

    /** @var string */
    public $createdFrom;
    /** @var string */
    public $createdTo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'fail_reason',], 'integer'],
            [['login', 'password', 'ip', 'user_agent', 'createdFrom', 'createdTo'], 'string'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params)
    {
        $query = LoginAttempt::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'defaultOrder' => [
                'id' => SORT_DESC,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'fail_reason' => $this->fail_reason,
        ]);

        $query
            ->andFilterWhere(['like', 'login', $this->login])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'user_agent', $this->user_agent]);

        if ($this->createdFrom) {
            $query->andWhere(['>=', 'created_at', strtotime($this->createdFrom)]);
        }

        if ($this->createdTo) {
            $query->andWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
        }

        return $dataProvider;
    }
}
