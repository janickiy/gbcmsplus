<?php

namespace mcms\payments\models\search;

use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Поиск инвойсов партнера
 */
class UserBalanceInvoiceSearch extends UserBalanceInvoice
{
  const DATE_RANGE_DELIMITER = ' - ';

  public $fromAmount;
  public $toAmount;
  /** @var string */
  public $dateDateRange;
  public $userId;

  public static $allowedTypes = [
    UserBalanceInvoice::TYPE_COMPENSATION,
    UserBalanceInvoice::TYPE_PENALTY,
    UserBalanceInvoice::TYPE_CONVERT_INCREASE,
    UserBalanceInvoice::TYPE_CONVERT_DECREASE
  ];

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return [
      [['userId', 'type'], 'integer'],
      [['fromAmount', 'toAmount'], 'number'],
      ['toAmount', 'compare', 'compareAttribute' => 'fromAmount', 'operator' => '>=', 'skipOnEmpty' => true],
      [['id', 'type', 'currency', 'dateDateRange', 'fromAmount', 'toAmount'], 'safe'],
    ];
  }

  /**
   * @param $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = UserBalanceInvoice::find()->with('user');

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]]
    ]);

    if (array_key_exists('description', $dataProvider->getSort()->attributes)) {
      unset($dataProvider->getSort()->attributes['description']);
    }

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'user_id' => $this->userId,
      'type' => $this->type,
      'currency' => $this->currency,
    ]);

    if (!empty($this->dateDateRange) && strpos($this->dateDateRange, self::DATE_RANGE_DELIMITER) !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->dateDateRange);
      $query->andFilterWhere(['between', UserBalanceInvoice::tableName() . '.date', $startDate, $endDate]);
    }

    $query->andFilterWhere(['>=', 'amount', $this->fromAmount]);
    $query->andFilterWhere(['<=', 'amount', $this->toAmount]);
    $query->andWhere(['type' => array_keys(self::getTypes())]);
    $query->andWhere(['<>', 'user_id', UserPayment::getResellerId()]);

    return $dataProvider;
  }

  /**
   * @param null $type
   * @return array|mixed|string
   */
  public static function getTypes($type = null)
  {
    $typeList = array_filter(
      parent::getTypes(),
      function ($key) {
        return in_array($key, self::$allowedTypes, true);
      },
      ARRAY_FILTER_USE_KEY
    );
    return $type === null ? $typeList : ArrayHelper::getValue($typeList, $type);
  }
}
