<?php

namespace mcms\user\models\search;


use mcms\user\models\UserInvitation;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class UsersInvitationsSearch
 * @package mcms\user\models\search
 */
class UsersInvitationsSearch extends Model
{
    /** @var int */
    public $id;
    /** @var string */
    public $username;
    /** @var string */
    public $hash;
    /** @var string */
    public $contact;
    /** @var string */
    public $user_id;
    /** @var int */
    public $status;

    /** @var string */
    public $createdFrom;
    /** @var string */
    public $createdTo;

    /**
     * @var bool
     */
    public $strictSearch = true;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'status', 'strictSearch',], 'integer'],
            [['username', 'contact', 'hash', 'createdFrom', 'createdTo'], 'string'],
        ];
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return UserInvitation::getStatusesMap();
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params)
    {
        $query = UserInvitation::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'defaultOrder' => [
                'id' => SORT_ASC,
            ],
            'attributes' =>
                [
                    'id',
                    'status',
                    'username',
                    'created_at',
                    'updated_at',
                    'user_id',
                    'contact',
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
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'hash', $this->hash]);
        $query->andFilterWhere(['like', 'contact', $this->contact]);

        $this->strictSearch
            ? $query->andFilterWhere(['like', 'username', $this->username])
            : $query->orFilterWhere(['like', 'username', $this->username]);

        if ($this->createdFrom) {
            $query->andWhere(['>=', 'created_at', strtotime($this->createdFrom)]);
        }

        if ($this->createdTo) {
            $query->andWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
        }

        return $dataProvider;
    }
}