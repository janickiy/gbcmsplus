<?php

namespace mcms\notifications\models\search;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\UsersHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use mcms\notifications\models\BrowserNotification;

/**
 * BrowserNotificationSearch represents the model behind the search form about `mcms\notifications\models\BrowserNotification`.
 */
class BrowserNotificationSearch extends BrowserNotification
{

  public $dateBegin;
  public $dateEnd;
  public $categoryId;
  public $categoriesId;
  public $typeId;
  public $updatedFrom;
  public $updatedTo;

  /**
   * Исключение уведомлений отправленных пользователями, которые недоступны для просмотра текущему пользователю.
   * Например реселлер не должен видеть уведомления партнеров созданные рутом.
   * Правило применяется только при просмотре чужих уведомлений
   * @param ActiveQuery $query
   * @param int $userId
   */
  public static function handleResellerQuery(ActiveQuery &$query, $userId)
  {
    if ($userId == Yii::$app->user->id) return;

    if(Yii::$app->getModule('users')) {
      $notAvailableUserIds = Yii::$app->getModule('users')->api('notAvailableUserIds', [
        'userId' => $userId,
        'skipCurrentUser' => true,
      ])->getResult();
      if (count($notAvailableUserIds)) {
        $query->andOnCondition([
          'or',
          ['not in', 'from_user_id', $notAvailableUserIds],
          ['from_user_id' => null],
          ['from_user_id' => $userId],
          ['user_id' => $userId],
        ]);
      }
    }
  }

  /**
   * Получение количества непрочитанных уведомлений
   * @param int $userId
   * @param array $categoriesId
   * @return integer
   */
  public static function getUnviewedCount($userId, $categoriesId)
  {
    $cacheKey = strtr(BrowserNotification::cacheKeys(self::CACHE_KEY_UNVIEWED_COUNT), ['{userId}' => Yii::$app->user->id]);
    if (($count = Yii::$app->cache->get($cacheKey)) !== false) {return $count;}

    $query = static::find()->andFilterWhere([
      'user_id' => $userId,
      'from_module_id' => $categoriesId,
      'is_hidden' => 0,
      'is_viewed' => 0,
    ]);
    static::handleResellerQuery($query, $userId);
    $count = $query->count();

    Yii::$app->cache->set($cacheKey, $count, 0, new TagDependency(['tags' => [
      BrowserNotification::cacheTags(self::CACHE_TAG_NOTIFICATIONS),
      strtr(BrowserNotification::cacheTags(self::CACHE_TAG_USER_NOTIFICATIONS), ['{userId}' => Yii::$app->user->id]),
    ]]));

    return $count;
  }

  /**
   * Получение количества уведомлений
   * @param int $userId
   * @param array $categoriesId
   * @return integer
   */
  public static function getTotalCount($userId, $categoriesId)
  {
    $cacheKey = strtr(BrowserNotification::cacheKeys(self::CACHE_KEY_FULL_COUNT), ['{userId}' => Yii::$app->user->id]);
    if (($count = Yii::$app->cache->get($cacheKey)) !== false) {return $count;}

    $query = static::find()->andFilterWhere([
      'user_id' => $userId,
      'from_module_id' => $categoriesId,
      'is_hidden' => 0,
    ]);
    static::handleResellerQuery($query, $userId);
    $count = $query->count();

    Yii::$app->cache->set($cacheKey, $count, 0, new TagDependency(['tags' => [
      BrowserNotification::cacheTags(self::CACHE_TAG_NOTIFICATIONS),
      strtr(BrowserNotification::cacheTags(self::CACHE_TAG_USER_NOTIFICATIONS), ['{userId}' => Yii::$app->user->id]),
    ]]));

    return $count;
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'user_id', 'from_user_id', 'is_viewed', 'is_important', 'is_news', 'from_module_id', 'created_at', 'updated_at', 'is_hidden'], 'integer'],
      ['categoriesId', 'each', 'rule' => ['integer']],
      ['categoryId', 'each', 'rule' => ['integer']],
      [['dateBegin', 'dateEnd', 'typeId'], 'string'],
      [['from', 'header', 'message', 'is_hidden', 'updatedFrom', 'updatedTo', 'notifications_delivery_id'], 'safe'],
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
    $query = BrowserNotification::find();


    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);
    $query->joinWith('user as u');
    $query->joinWith('fromUser as fu');
    $dataProvider->setSort([
      'attributes' =>
        [
          'user_id' => [
            'asc' => ['u.username' => SORT_ASC],
            'desc' => ['u.username' => SORT_DESC],
          ],
          'from_user_id' => [
            'asc' => ['fu.username' => SORT_ASC],
            'desc' => ['fu.username' => SORT_DESC],
          ],
          'header',
          'created_at',
          'is_important',
          'from_module_id',
          'is_viewed',
          'is_news',
          'updated_at',
        ],
      'defaultOrder' => [
        'created_at' => SORT_DESC
      ],
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
      'user_id' => $this->user_id,
      'is_viewed' => $this->is_viewed,
      'is_hidden' => $this->is_hidden,
      'is_news' => $this->is_news,
      'from_user_id' => $this->from_user_id,
      'from_module_id' => $this->from_module_id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'is_important' => $this->is_important,
      'notifications_delivery_id' => $this->notifications_delivery_id,
    ]);

    if ($this->updatedFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'updated_at', strtotime(str_replace('/', '-', $this->updatedFrom) . ' midnight')]);
    }
    if ($this->updatedTo) {
      $query->andFilterWhere(['<', self::tableName() . '.' . 'updated_at', strtotime(str_replace('/', '-', $this->updatedTo) . ' tomorrow') -1]);
    }

    $query->andFilterWhere(['like', 'from', $this->from])
      ->andFilterWhere(['like', 'header', $this->header])
      ->andFilterWhere(['like', 'message', $this->message]);

    if($this->dateBegin) {
      $query->andOnCondition(['>=', self::tableName() . '.' . 'created_at', strtotime(str_replace('/', '-', $this->dateBegin) . ' midnight')]);
    }

    if($this->dateEnd) {
      $query->andOnCondition(['<=', self::tableName() . '.' . 'created_at', strtotime(str_replace('/', '-', $this->dateEnd) . ' tomorrow') - 1]);
    }

    if(!empty($this->categoriesId)) {
      $query->andOnCondition(['in', 'from_module_id', $this->categoriesId]);
    }

    if(!empty($this->categoryId)) {
      $query->andOnCondition(['in', 'from_module_id', $this->categoryId]);
    }

    if($this->typeId == 'important') {
      $query->andOnCondition(['is_important' => 1]);
    }

    if($this->typeId == 'news') {
      $query->andOnCondition(['is_news' => 1]);
    }

    static::handleResellerQuery($query, Yii::$app->user->id);

    return $dataProvider;
  }

}
