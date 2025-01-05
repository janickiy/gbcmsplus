<?php

namespace mcms\promo\models\search;

use mcms\promo\components\LandingOperatorPrices;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Operator;
use mcms\user\models\User;
use mcms\user\Module;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Модель для грида /admin/promo/landings/payouts/
 */
class UserOperatorLandingSearch extends Model
{
  public $user_id;
  public $country_id;
  public $operator_id;
  public $landing_id;
  public $is_active;

  protected static $landingOperator;
  protected static $landingOperatorPrices;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'country_id', 'operator_id', 'landing_id', 'is_active'], 'integer'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'landing_id' => Yii::_t('promo.landings.operator-attribute-landing_id'),
      'operator_id' => Yii::_t('promo.landings.operator-attribute-operator_id'),
      'user_id' => Yii::_t('promo.landing_operator_price.user_id'),
      'country_id' => Yii::_t('promo.landing_operator_price.country_id'),
      'is_active' => Yii::_t('promo.landing_operator_price.is_active'),
    ];
  }

  /**
   * @param $params
   * @return ActiveDataProvider
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function search($params)
  {
    $this->load($params);

    /** @var Module $userModule */
    $userModule = Yii::$app->getModule('users');

    $query = (new Query())->select([
      'user_id' => 'u.id',
      'lo.operator_id',
      'o.country_id',
      'lo.landing_id',
      'is_active' => 'IF(uol.user_id IS NOT NULL, 1, 0)',
    ])->from(LandingOperator::tableName() . ' lo')
      ->leftJoin([Operator::tableName() . ' o'], 'o.id = lo.operator_id')
      ->leftJoin(User::tableName() . ' u', '1=1')->andWhere([
        'u.status' => User::STATUS_ACTIVE
      ])
      ->leftJoin('auth_assignment aa', 'aa.user_id = u.id')->andWhere([
        'aa.item_name' => $userModule::PARTNER_ROLE
      ])
      ->leftJoin(['uol' => 'users_operator_landings'], 'uol.user_id=u.id and uol.operator_id=lo.operator_id and uol.landing_id=lo.landing_id');


    if (!$this->validate()) {
      $query->where('0=1');
      return new ActiveDataProvider([
        'query' => $query
      ]);
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'lo.operator_id' => $this->operator_id,
      'lo.landing_id' => $this->landing_id,
      'u.id' => $this->user_id,
      'o.country_id' => $this->country_id,
    ]);

    if ($this->is_active === '0') {
      $query->andWhere(['IS', 'uol.user_id', new Expression('NULL')]);
    }
    if ($this->is_active === '1') {
      $query->andWhere(['IS NOT', 'uol.user_id', new Expression('NULL')]);
    }

    return new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'attributes' => [
          'user_id',
          'operator_id',
          'country_id',
          'landing_id',
          'is_active',
        ],
      ]
    ]);
  }

  /**
   * @param $id
   * @return string
   */
  public function getUserLink($id)
  {
    return User::findOne($id)->getViewLink();
  }

  /**
   * @param $id
   * @return string
   */
  public function getOperatorLink($id)
  {
    return Operator::findOne($id)->getViewLink();
  }

  /**
   * @param $id
   * @return string
   */
  public function getLandingLink($id)
  {
    return Landing::findOne($id)->getViewLink();
  }

  /**
   * @param $id
   * @return string
   */
  public function getCountryLink($id)
  {
    return Country::findOne($id)->getViewLink();
  }

  /**
   * @param array $model
   * @return LandingOperatorPrices
   */
  private function getLandingOperatorPrices(array $model)
  {
    $key = $model['landing_id'] . '_' . $model['operator_id'];
    if (empty(self::$landingOperatorPrices[$key])) {
      self::$landingOperatorPrices[$key] = LandingOperatorPrices::create($this->getLandingOperator($model), $model['user_id']);
    }

    return self::$landingOperatorPrices[$key];
  }

  /**
   * @param array $model
   * @return LandingOperator
   */
  private function getLandingOperator(array $model)
  {
    $key = $model['landing_id'] . '_' . $model['operator_id'];
    if (empty(self::$landingOperator[$key])) {
      self::$landingOperator[$key] = LandingOperator::findOne([
        'landing_id' => $model['landing_id'],
        'operator_id' => $model['operator_id'],
      ]);
    }

    return self::$landingOperator[$key];
  }

  /**
   * @param array $model
   * @param $currency
   * @return float
   */
  public function getCpaPrice(array $model, $currency)
  {
    return $this->getLandingOperatorPrices($model)->getCpaPrice($currency);
  }

  /**
   * @param $model
   * @param $currency
   * @return null|float
   */
  public function getRebillPrice($model, $currency)
  {
    if ($this->getLandingOperator($model)->isOnetime) {
      return null;
    }
    return $this->getLandingOperatorPrices($model)->getRebillPrice($currency);
  }
}
