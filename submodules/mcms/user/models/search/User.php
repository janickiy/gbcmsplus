<?php

namespace mcms\user\models\search;

use mcms\payments\models\UserWallet;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\user\components\api\NotAvailableUserIds;
use mcms\user\Module;
use Yii;
use mcms\user\models\Role;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class User extends \mcms\user\models\User
{

    public $id;
    public $username;
    public $email;
    public $status;

    public $walletString;
    public $contactString;

    /**
     * Поиск по username, email, id пользователя
     * @var string
     */
    public $queryName;

    const SELECT2_LIMIT = 10;
    const USER_ONLINE = 'online';
    const USER_OFFLINE = 'offline';
    const SCENARIO_PARTNER_REFERRAL_SEARCH = 'partner_referral_search';

    const PERMISSION_VIEW_ROOT_USER = 'CanViewRootUser';
    const PERMISSION_VIEW_ADMIN_USER = 'CanViewAdminUser';
    const PERMISSION_VIEW_PARTNER_USER = 'CanViewPartnerUser';
    const PERMISSION_VIEW_RESELLER_USER = 'CanViewResellerUser';
    const PERMISSION_VIEW_MANAGER_USER = 'CanViewManagerUser';


    /**
     * Если указать массив roles, то выборка пользователей
     * будет отфильтрована по наименованию ролей.
     * Например чтобы получить только реселлеров $roles = ['reseller']
     *
     * @var array
     */

    public $namesRoles;
    public $online;

    public $createdFrom;
    public $createdTo;
    public $onlineFrom;
    public $onlineTo;
    public $ignoreIds;
    public $sourceIds;
    public $streamIds;
    public $skipCurrentUser = false;
    /**
     * @var bool Должен ли быть доступ к пользователям, к которым не привязан менеджер.
     * Параметр сделан для того, что бы менеджер мог редактировать пользователей без привязанного менеджера в списке пользователей
     * и не мог управлять их записями, например тикетами, лендами и прочим
     */
    public $canViewUserWithoutManager = false;

    /*
     * @var array
     */
    public $orderByFieldStatus;

    public function init()
    {
        parent::init();
        if ($this->scenario === self::SCENARIO_PARTNER_REFERRAL_SEARCH && $this->createdFrom === null && $this->createdTo === null) {
            $this->createdFrom = Yii::$app->formatter->asDate('now -7 days', 'dd.MM.yyyy');
            $this->createdTo = Yii::$app->formatter->asDate('now', 'dd.MM.yyyy');
        }
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_PARTNER_REFERRAL_SEARCH => ['createdFrom', 'createdTo']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'username', 'status', 'email', 'namesRoles', 'createdFrom', 'createdTo', 'onlineFrom', 'onlineTo', 'online', 'queryName', 'contactString', 'walletString',], 'safe'],
            [['status', 'manager_id'], 'integer'],
            ['skipCurrentUser', 'boolean'],
            [['createdFrom', 'createdTo'], 'date', 'format' => 'dd.MM.yyyy', 'on' => self::SCENARIO_PARTNER_REFERRAL_SEARCH],
            [['ignoreIds', 'sourceIds', 'streamIds'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'walletString' => Yii::_t('users.filter.wallet_string'),
            'contactString' => Yii::_t('users.filter.contact_string'),
        ]);
    }

    public function search(array $params)
    {
        $query = \mcms\user\models\User::find();

        $query->joinWith(['roles']);
        $query->joinWith('userPromoSettings');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        $sortAttributes = [
            'attributes' =>
                [
                    'id',
                    'email',
                    'namesRoles' => [
                        'asc' => ['auth_assignment.item_name' => SORT_ASC],
                        'desc' => ['auth_assignment.item_name' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'status',
                    'created_at',
                    'online_at',
                    'online' => [
                        'asc' => ['is_online' => SORT_ASC],
                        'desc' => ['is_online' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                ],
        ];


        if ($this->orderByFieldStatus) {
            $sortAttributes['attributes']['defaultSort'] = [
                'desc' => [new Expression('FIELD (' . self::tableName() . '.status, ' .
                    implode(',', $this->orderByFieldStatus) . ') DESC, created_at DESC')]
            ];
            $sortAttributes['defaultOrder'] = ['defaultSort' => SORT_DESC];
        }

        $dataProvider->setSort(
            $sortAttributes
        );

        $this->load($params);

        $query->andFilterWhere([self::tableName() . '.' . 'id' => $this->id]);

        if ($this->ignoreIds) {
            $query->andFilterWhere(['NOT IN', self::tableName() . '.' . 'id', $this->ignoreIds]);
        }
        $query->andFilterWhere(['like', self::tableName() . '.' . 'username', $this->username]);
        $query->andFilterWhere(['like', self::tableName() . '.' . 'email', $this->email]);
        $query->andFilterWhere(['=', self::tableName() . '.' . 'status', $this->status]);
        $query->andFilterWhere(['=', self::tableName() . '.' . 'manager_id', $this->manager_id]);

        if (!$this->isShowInactiveUsers()) {
            $query->andWhere(['not in', 'status', [User::STATUS_INACTIVE, User::STATUS_DELETED]]);
        }

        $query->andFilterWhere(['in', Role::tableName() . '.' . 'name', $this->namesRoles]);

        if ($this->createdFrom) {
            $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime($this->createdFrom)]);
        }
        if ($this->createdTo) {
            $query->andFilterWhere(['<', self::tableName() . '.' . 'created_at', strtotime('tomorrow', strtotime($this->createdTo))]);
        }
        if ($this->onlineFrom) {
            $query->andFilterWhere(['>=', self::tableName() . '.' . 'online_at', strtotime($this->onlineFrom)]);
        }
        if ($this->onlineTo) {
            $query->andFilterWhere(['<', self::tableName() . '.' . 'online_at', strtotime('tomorrow', strtotime($this->onlineTo))]);
        }
        if ($this->online == self::USER_ONLINE) {
            $query->andFilterWhere(['<=', '(UNIX_TIMESTAMP() - ' . self::tableName() . '.' . 'online_at)', self::ONLINE_LIFETIME]);
        }
        if ($this->online == self::USER_OFFLINE) {
            $query->andFilterWhere(['>=', '(UNIX_TIMESTAMP() - ' . self::tableName() . '.' . 'online_at)', self::ONLINE_LIFETIME]);
        }

        if ($this->sourceIds) {
            $query->leftJoin(Source::tableName(), Source::tableName() . '.user_id=' . self::tableName() . '.id');
            $query->andFilterWhere([Source::tableName() . '.id' => $this->sourceIds]);
        }

        if ($this->streamIds) {
            $query->leftJoin(Stream::tableName(), Stream::tableName() . '.user_id=' . self::tableName() . '.id');
            $query->andFilterWhere([Stream::tableName() . '.id' => $this->streamIds]);
        }

        if ($this->queryName) {
            $query
                ->andWhere([
                    'or',
                    ['like', self::tableName() . '.' . 'username', $this->queryName],
                    ['like', self::tableName() . '.' . 'email', $this->queryName],
                    ['like', self::tableName() . '.' . 'id', $this->queryName],
                ]);

            if ($this->id && !is_array($this->id)) {
                $query->andWhere(['!=', self::tableName() . '.' . 'id', $this->queryName]);
            }
        }

        /*
         * Прячем юзеров, которые в ч/с для реселлера.
         */
        $ignoreUserIds = (new NotAvailableUserIds([
            'userId' => Yii::$app->user->id,
            'skipCurrentUser' => $this->skipCurrentUser,
        ]))->getResult();
        if (count($ignoreUserIds) > 0) {
            $query->andFilterWhere(['not in', self::tableName() . '.' . 'id', $ignoreUserIds]);
        }

        // Скрытие непривязанных пользователей
        if (isset(Yii::$app->user->identity)) {
            Yii::$app->user->identity->filterUsers($query, $this->canViewUserWithoutManager);
        }

        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('users');
        if (!Yii::$app->user->can(self::PERMISSION_VIEW_MANAGER_USER)) {
            $query->andFilterWhere(['not in', Role::tableName() . '.' . 'name', $userModule->getManagerRoles()]);
        }

        if ($this->walletString) {
            // сделано так, потому что при сборке релейшена вызывается метот find, где стоит is_deleted без алиаса
            // из-за этого запрос ломается
            $walletUserIds = UserWallet::find(false)
                ->select('user_id')
                ->andWhere(['like', 'wallet_account', $this->walletString])
                ->column();

            $query->andWhere(['id' => $walletUserIds]);
        }

        if ($this->contactString) {
            $query
                ->innerJoinWith('contacts contacts')
                ->andWhere(['like', 'contacts.data', $this->contactString]);
        }

        return $dataProvider;
    }

    public function searchReferrals(array $params, $userId)
    {
        $dataProvider = $this->search($params);
        $query = $dataProvider->query;

        $query->addSelect(self::tableName() . '.*');
        Yii::$app->getModule('users')->api('referrals')->joinByUser($query, self::tableName(), self::FIELD_ID, $userId);

        return $dataProvider;
    }

    public static function filterStatuses()
    {
        $statuses = \mcms\user\models\User::$availableStatuses;

        foreach ($statuses as &$status) {
            $status = Yii::_t($status);
        }
        return $statuses;
    }

    public function filterRoles()
    {
        return \mcms\user\models\Role::getDropdownListData();
    }

    public function filterOnline()
    {
        return [self::USER_ONLINE => Yii::_t('controllers.online'), self::USER_OFFLINE => Yii::_t('controllers.offline')];
    }

    /**
     * Проверяет, если активен ли хоть один фильтр - показываем неактивных юзеров
     * @return bool
     */
    protected function isShowInactiveUsers()
    {
        // сделано таким странным способом, чтобы в IDE подсвечивало использование указаных свойств
        switch (true) {
            case (bool)$this->id:
            case (bool)$this->username:
            case (bool)$this->email:
            case (bool)$this->status:
            case (bool)$this->contactString:
            case (bool)$this->walletString:
                return true;
        }

        return false;
    }

}
