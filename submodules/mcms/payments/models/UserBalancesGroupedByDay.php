<?php

namespace mcms\payments\models;

use mcms\common\traits\Translate;
use mcms\holds\components\PartnerCountryUnhold;
use mcms\payments\components\UserBalance;
use Yii;
use yii\db\BatchQueryResult;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "user_balances_grouped_by_day".
 *
 * @property integer $date
 * @property integer $user_id
 * @property integer $type
 * @property string $currency
 * @property string $profit_rub
 * @property string $profit_eur
 * @property string $profit_usd
 */
class UserBalancesGroupedByDay extends \yii\db\ActiveRecord
{
  use Translate;
  const LANG_PREFIX = 'payments.user-balances-grouped-by-day.';

  const TYPE_REBILL = 0;
  const TYPE_ONETIME = 1;
  const TYPE_BUYOUT = 2;
  const TYPE_REFERRAL = 3;
  const TYPE_SOLD_TB = 4;

  const HOLD_BALANCE = 'balance_hold';
  const UNHOLD_BALANCE = 'balance_unhold';

  const RUB = 'rub';
  const EUR = 'eur';
  const USD = 'usd';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_balances_grouped_by_day';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['date', 'user_id', 'type', 'profit_rub', 'profit_eur', 'profit_usd'], 'required'],
      [['user_id', 'type'], 'integer'],
      [['date'], 'string'],
      [['profit_rub', 'profit_eur', 'profit_usd'], 'number'],
    ];
  }

  public static function getCurrencyList()
  {
    return [self::RUB, self::EUR, self::USD];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'date' => self::translate('attribute-date'),
      'user_id' => self::translate('attribute-user-id'),
      'type' => self::translate('attribute-type'),
      'profit_rub' => self::translate('attribute-profit-rub'),
      'profit_eur' => self::translate('attribute-profit-eur'),
      'profit_usd' => self::translate('attribute-profit-usd'),
    ];
  }

  public function getTypes($type = null)
  {
    $typeList = [
      self::TYPE_REBILL => self::translate('type-rebill'),
      self::TYPE_ONETIME => self::translate('type-onetime'),
      self::TYPE_BUYOUT => self::translate('type-buyout'),
      self::TYPE_REFERRAL => self::translate('type-referral'),
      self::TYPE_SOLD_TB => self::translate('type-sold-tb'),
    ];
    return $type !== null ? ArrayHelper::getValue($typeList, $type) : $typeList;
  }

  /**
   * @param $userId
   * @param $currency
   * @param null|string $dateFrom
   * @param null|string $dateTo
   * @return float[]
   */
  public static function getProfit($userId, $currency, $dateFrom = null, $dateTo = null)
  {
    $currency = in_array($currency, self::getCurrencyList()) ? $currency : current(self::getCurrencyList());

    $result = (new Query())
      ->select([
        sprintf('SUM(IF((pcu.last_unhold_date IS NULL OR pcu.last_unhold_date < ' . self::tableName() . '.date) && ' . self::tableName() . '.country_id <> 0, profit_%s, 0)) as %s', $currency, self::HOLD_BALANCE),
        sprintf('SUM(IF(pcu.last_unhold_date >= ' . self::tableName() . '.date || ' . self::tableName() . '.country_id = 0, profit_%s, 0)) as %s', $currency, self::UNHOLD_BALANCE),
      ])
      ->from(self::tableName())
      // TODO: Заменить строку partner_country_unhold на PartnerCountryUnhold::tableName()
      ->leftJoin('partner_country_unhold pcu', self::tableName() . '.country_id=pcu.country_id AND ' . self::tableName() . '.user_id=pcu.user_id')
      ->andWhere([self::tableName() . '.user_id' => $userId])
      ->andWhere([self::tableName() . '.user_currency' => $currency])
      ->andFilterWhere(['>=', self::tableName() . '.date', $dateFrom])
      ->andFilterWhere(['<=', self::tableName() . '.date', $dateTo])
      ->one();

    // Делаем float и возвращаем
    return array_map(function($v) { return (float)$v; }, $result);
  }

  /**
   * Достает сумму балансов за сегодня без фильтрации по валюте для правильного отображения при смене валюты у пользователя
   * @param $userId
   * @param $currency
   * @return float
   */
  public static function getTodayProfit($userId, $currency)
  {
    $currency = in_array($currency, self::getCurrencyList()) ? $currency : current(self::getCurrencyList());

    return (float)(new Query())
      ->select('SUM(profit_' . $currency . ')')
      ->from(self::tableName())
      ->andWhere([self::tableName() . '.user_id' => $userId])
      ->andWhere(['=', self::tableName() . '.date', date('Y-m-d')])
      ->scalar();
  }

  /**
   * Возвращает профиты пользователя, находящиеся в холде, сгруппированные по дате и стране
   * Используется при конвертации, для создания соответствующих инвойсов
   * TRICKY: Аналогичный метод есть в @see \mcms\payments\models\UserBalanceInvoice::getHoldInvoices
   * и ещё тут похожий запрос: @see UserBalancesGroupedByDay::getProfit()
   * @param $userId
   * @param $currency
   * @return array
   */
  public static function getHoldProfit($userId, $currency)
  {
    $currency = in_array($currency, self::getCurrencyList()) ? $currency : current(self::getCurrencyList());

    $result = (new Query())
      ->select([
        sprintf('SUM(profit_%s) as amount', $currency),
        'bal.date',
        'bal.country_id',
      ])
      ->from(self::tableName() . ' bal')
      ->leftJoin(PartnerCountryUnhold::tableName() . ' pcu', 'bal.country_id=pcu.country_id AND bal.user_id=pcu.user_id')
      ->andWhere(['bal.user_id' => $userId])
      // TRICKY: страна 0 - это старые записи (до введения холдов), которые считаем расхолдированными
      ->andWhere(['<>', 'bal.country_id', 0])
      ->andWhere(['bal.user_currency' => $currency])
      ->andWhere([
        'or',
        'pcu.last_unhold_date < bal.date',
        'pcu.last_unhold_date IS NULL'
      ])
      ->groupBy(['bal.date', 'bal.country_id'])
      ->all();

    // Делаем amount float и возвращаем
    return array_map(function ($v) {
      $v['amount'] = (float)$v['amount'];
      return $v;
    }, $result);
  }

  /**
   * @param $userId
   * @param $dateTo
   * @param array $order
   * @return BatchQueryResult
   */
  public static function getProfitsByDate($userId, $dateTo, $order = [])
  {
    $query = self::find()
      ->select([
        'SUM(profit_rub) as profit_rub',
        'SUM(profit_eur) as profit_eur',
        'SUM(profit_usd) as profit_usd',
        'user_id',
      ])
      ->where(['<=', 'date', Yii::$app->formatter->asDate($dateTo, 'php:Y-m-d')])
      ->andWhere(['user_id' => $userId])
      ->groupBy('user_id')
    ;
    if ($order) {
      $query->orderBy($order);
    }
    return $query->indexBy('user_id')->all();
  }

  /**
   * @param int $userId
   * @param string $dateFrom
   * @param string $dateTo
   * @param string $currency
   * @return array
   */
  public static function getProfitsByCountry($userId, $dateFrom, $dateTo, $currency)
  {
    $query = (new Query())
      ->select([
        'SUM(profit_' . $currency . ') as profit',
      ])
      ->from(static::tableName())
      ->andWhere([
        'BETWEEN',
        'date',
        Yii::$app->formatter->asDate($dateFrom, 'php:Y-m-d'),
        Yii::$app->formatter->asDate($dateTo, 'php:Y-m-d')
      ])
      ->andWhere(['user_id' => $userId])
      ->groupBy('country_id');

    return $query->indexBy('country_id')->column();
  }

  /**
   * @param int|array $userId
   * @return BatchQueryResult
   */
  public static function getProfitList($userId)
  {
    $query = self::find()
      ->select([
        'profit_rub',
        'profit_eur',
        'profit_usd',
        'user_id',
        'date',
      ])
      ->andWhere(['user_id' => $userId])
    ;
    return $query->each();
  }

  /**
   * @param integer|array $userId
   * @param integer|null $dateFrom
   * @param integer|null $dateTo
   * @return BatchQueryResult
   */
  public static function getUsersProfitSumList($userId, $dateFrom = null, $dateTo = null)
  {
    $query = self::find()
      ->select([
        'SUM(profit_rub) as profit_rub',
        'SUM(profit_eur) as profit_eur',
        'SUM(profit_usd) as profit_usd',
        'user_id',
      ])
      ->andWhere(['user_id' => $userId])
      ->groupBy('user_id')
    ;
    $dateFrom && $query->andWhere(['>=', 'date', date('Y-m-d', $dateFrom)]);
    $dateTo && $query->andWhere(['<=', 'date', date('Y-m-d', $dateTo)]);

    return $query->each();
  }

  public function getTypeName()
  {
    return $this->getTypes($this->type);
  }

  public function getUser()
  {
    return Yii::$app->getModule('users')->api('user', ['getRelation' => true])->hasOne($this, 'user_id');
  }

  /**
   * @inheritDoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    (new UserBalance(['userId' => $this->user_id]))->invalidateCache();
    parent::afterSave($insert, $changedAttributes);
  }


}
