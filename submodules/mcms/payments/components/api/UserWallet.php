<?php

namespace mcms\payments\components\api;

use InvalidArgumentException;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserWallet as UserWalletModel;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

class UserWallet extends ApiResult
{
  private $walletId;
  private $walletType;
  /** @var $walletNewAccount string JSON с новыми данными аккаунта редактируемого кошелька */
  private $walletNewAccount;
  /** @var $walletOldAccount string JSON со старыми данными аккаунта редактируемого кошелька */
  private $walletOldAccount;
  private $userId;
  private $currency;
  private $isNew;

  /**
   * @inheritdoc
   */
  function init($params = [])
  {
    $this->walletId = ArrayHelper::getValue($params, 'walletId');
    $this->walletType = ArrayHelper::getValue($params, 'wallet_type');
    $this->userId = ArrayHelper::getValue($params, 'user_id');
    $this->currency = ArrayHelper::getValue($params, 'currency');
    $this->walletNewAccount = ArrayHelper::getValue($params, 'wallet_new_account');
    $this->walletOldAccount = ArrayHelper::getValue($params, 'wallet_old_account');;
    $this->isNew = ArrayHelper::getValue($params, 'new', false) == true;
  }

  /**
   * @return UserWalletModel|UserWalletModel[]
   */
  public function getResult()
  {
    $userWallet = $this->walletId
      ? UserWalletModel::findOne(['id' => $this->walletId, 'user_id' => $this->userId])
      : UserWalletModel::find()->andWhere([
        'wallet_type' => $this->walletType,
        'user_id' => $this->userId,
        'currency' => $this->currency,
      ])->all();
    if (!$userWallet || $this->isNew) {
      $userWallet = new UserWalletModel([
        'wallet_type' => $this->walletType,
        'user_id' => $this->userId,
        'currency' => $this->currency,
      ]);
    }
    return $userWallet;
  }

  /**
   * Если передать wallet_type и user_id получаем кошельки, сгруппированные по реквизитам
   * В поле currency будут все валюты через запятую
   * @return UserWalletModel|UserWalletModel[]
   */
  public function getGroupResult()
  {
    $userWallet = UserWalletModel::find()->select(['*', new Expression('GROUP_CONCAT(currency SEPARATOR ",") as currency')])->andWhere([
        'wallet_type' => $this->walletType,
        'user_id' => $this->userId,
      ])
      ->andFilterWhere(['currency' => $this->currency])
      ->groupBy('wallet_account');

    $userWallet = $this->walletId
      ? $userWallet->orderBy(new Expression('FIELD(id, :walletId) DESC', [':walletId' => $this->walletId]))->all()
      : $userWallet->all();
    if (!$userWallet || $this->isNew) {
      $userWallet = new UserWalletModel([
        'wallet_type' => $this->walletType,
        'user_id' => $this->userId,
        'currency' => $this->currency,
      ]);
    }

    return $userWallet;
  }

  /**
   * Создать кошелек
   * @param $currency string
   * @return array ['result' => true|false, 'model' => mcms\payments\models\UserWallet]
   */
  public function createWallet($currency)
  {
    $userWallet = new UserWalletModel([
      'wallet_account' => $this->walletNewAccount,
      'wallet_type' => $this->walletType,
      'user_id' => $this->userId,
      'currency' => $currency,
    ]);

    return ['result' => $userWallet->save(), 'model' => $userWallet];
  }

  // TODO Переименовать. Это удаление не всех кошельков, а только кошельков одной группы
  /**
   * Удалить все кошельки по wallet_account, wallet_type, user_id
   * @param null|string[] $currencies Валюты кошельков группы, которые нужно удалить
   */
  public function deleteGroupWallets($currencies = null)
  {
    if ($currencies !== null && empty($currencies)) return;

    $condition = [
      'wallet_account' => $this->walletOldAccount,
      'wallet_type' => $this->walletType,
      'user_id' => $this->userId,
    ];
    if ($currencies) $condition['currency'] = $currencies;

    UserWalletModel::deleteAll($condition);
  }

  /**
   * @return UserWalletModel[]
   */
  public function getAll()
  {
    return UserWalletModel::find()
      ->andFilterWhere([
        'wallet_type' => $this->walletType,
        'currency' => $this->currency,
        'user_id' => $this->userId,
      ])->all();
  }

  /**
   * @param string|null $currency
   * @return UserWalletModel[]
   */
  public function getUserWallets($currency = null)
  {
    return UserWalletModel::findByCurrentUser()->andFilterWhere(['currency' => $currency])
      ->innerJoin(Wallet::tableName() . ' w', UserWalletModel::tableName() . '.wallet_type=w.id')
      ->andWhere(
        new Expression('IF(' . UserWalletModel::tableName() . '.currency = "rub", w.is_rub, IF(' . UserWalletModel::tableName() . '.currency = "usd", w.is_usd, w.is_eur)) = 1')
      )
      ->orderBy(new Expression("FIELD (currency, 'rub', 'usd', 'eur')"))
      ->all();
  }

  /**
   * @return bool|int
   */
  public function delete()
  {
    if ($this->isLocked()) return false;

    return UserWalletModel::deleteAll(['id' => $this->walletId, 'user_id' => $this->userId]);
  }

  /**
   * TRICKY Файл не должен удаляться из ФС, так как нам нужна история изменений кошелька
   * @param $field
   * @return bool
   */
  public function deleteFile($field)
  {
    if ($this->isLocked()) return false;

    if (!$this->userId || !$this->walletId) {
      throw new InvalidArgumentException('Не заполнены обязательные свойства');
    }

    /**
     * @var UserWalletModel $wallet
     */
    $wallet = UserWalletModel::find()->where([
      'user_id' => $this->userId,
      'id' => $this->walletId,
    ])->one();

    $account = json_decode($wallet->wallet_account, true);
    unset($account[$field]);
    $wallet->wallet_account = json_encode($account);

    return $wallet->save();
  }

  /**
   * @param ActiveRecord $model
   * @param $column
   * @return \yii\db\ActiveQuery
   */
  public function hasOne(ActiveRecord $model, $column)
  {
    return $this->hasOneRelation($model, UserWalletModel::class, ['user_id' => $column]);
  }

  /**
   * @return bool
   */
  private function isLocked()
  {
    $userPaymentSettings = Yii::$app->getModule('payments')
      ->api('userSettingsData', ['userId' => $this->userId])
      ->getResult();

    return !($userPaymentSettings->canChangeWallet($this->walletId));
  }

  /**
   * Найти кошельки группы
   * @return ActiveQuery
   */
  public function findWalletsGroup()
  {
    return UserWalletModel::findByUserAndType($this->userId, $this->walletType)
      ->andWhere(['wallet_account' => $this->walletOldAccount]);
  }
}