<?php

namespace mcms\payments\models;

use conquer\helpers\Json;
use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\payments\components\AvailableCurrencies;
use mcms\payments\models\queries\UserWalletsQuery;
use mcms\payments\models\wallet\AbstractWallet;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\models\wallet\WalletForm;
use mcms\payments\Module;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "user_wallets".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $currency
 * @property string $wallet_type
 * @property string $wallet_account
 * @property integer $is_verified
 * @property integer $is_deleted
 * @property integer $is_autopayments
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $user
 * @property Wallet $walletType
 */
class UserWallet extends \yii\db\ActiveRecord
{
  private $mainCurrencies;

  use Translate;
  const LANG_PREFIX = 'payments.user-payments.';
  const SCENARIO_DELETE = 'scenario_delete';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_wallets';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'wallet_type', 'wallet_account', 'currency'], 'required'],
      [['user_id', 'wallet_type', 'is_autopayments', 'is_verified',], 'integer'],
      [['wallet_account'], 'string'],
      [['wallet_account'], function ($attribute) {
        /** @var Wallet $walletAccountObject */
        $walletAccountObject = $this->walletType;
        if (!$walletAccountObject) {
          return false;
        }

        $availableCurrencies = (new AvailableCurrencies($this->user_id))->getCurrencies();

        if (!in_array($this->currency, $availableCurrencies, true)) {
          $this->addError($attribute, Yii::_t('payments.wallets.cant-add-wallet', ['currency' => $this->currency]));
          return false;
        }

        if (!$walletAccountObject->is_rub && $this->currency === 'rub') {
          $this->addError($attribute, Yii::_t('payments.wallets.cant-add-wallet', ['currency' => $this->currency]));
          return false;
        }
        if (!$walletAccountObject->is_usd && $this->currency === 'usd') {
          $this->addError($attribute, Yii::_t('payments.wallets.cant-add-wallet', ['currency' => $this->currency]));
          return false;
        }
        if (!$walletAccountObject->is_eur && $this->currency === 'eur') {
          $this->addError($attribute, Yii::_t('payments.wallets.cant-add-wallet', ['currency' => $this->currency]));
          return false;
        }

        return true;
      }],
      [['currency'], 'string', 'max' => 3],
      // Проверка существования кошелька с такими же реквизитами
      [
        'wallet_account',
        'unique',
        'targetAttribute' => ['user_id', 'currency', 'wallet_type', 'wallet_account'],
        'filter' => ['is_deleted' => false],
        'message' => Yii::_t('payments.settings.error_wallet_exists'),
      ],
      // Нельзя создавать кошельки одного типа с одной валютой
      [
        'wallet_account',
        'unique',
        'targetAttribute' => ['user_id', 'currency', 'wallet_type'],
        'filter' => ['is_deleted' => false],
        'message' => Yii::_t('payments.settings.error_wallet_multiple'),
      ],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
    ];
  }

  /**
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'wallet_account' => false,
    ];
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    $defaultAttributes = ['user_id', 'wallet_type', 'wallet_account', 'currency', 'is_autopayments'];
    if (Yii::$app->user->can(Module::PERMISSION_CAN_VERIFY_USER_WALLETS)) {
      $defaultAttributes[] = 'is_verified';
    }

    return [
      self::SCENARIO_DEFAULT => $defaultAttributes,
      self::SCENARIO_DELETE => [],
    ];
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => self::translate('attribute-user'),
      'currency' => self::translate('attribute-currency'),
      'wallet_type' => self::translate('attribute-wallet-type'),
      'wallet_account' => self::translate('attribute-wallet-account'),
      'is_autopayments' => self::translate('attribute-is_autopayments'),
      'is_verified' => self::translate('attribute-is_verified'),
      'created_at' => self::translate('attribute-created-at'),
      'updated_at' => self::translate('attribute-updated-at'),
    ];
  }

  /**
   * Получение платежных систем для дропдауна
   * @param string|null $currency
   * @param bool|null $activity Активность ПС @see Wallet::find()
   * @return array
   */
  public function getWalletDropDown($currency = null, $activity = null)
  {
    $currency = $currency ?: $this->currency;
    $allWalletsMap = ArrayHelper::map(Wallet::getByCurrency($currency), 'id', 'name');

    // Получение платежных систем в формате id => name отфильтрованных по активности
    $walletsMap = [];
    $activeWalletsIds = Wallet::find($activity)->select('id')->column();
    foreach ($activeWalletsIds as $activeId) {
      if (isset($allWalletsMap[$activeId])) $walletsMap[$activeId] = $allWalletsMap[$activeId];
    }

    $alreadySelectedWalletTypeIds = self::find()
      ->innerJoin('wallets', Wallet::tableName() . '.id=' . self::tableName() . '.wallet_type')
      ->where([
        'user_id' => $this->user_id,
        'currency' => $currency,
        'is_deleted' => 0,
      ])->indexBy('wallet_type')->all();

    return array_diff_key($walletsMap, $alreadySelectedWalletTypeIds);
  }

  /**
   * @param integer|array|null $type
   * @return array|string
   */
  public static function getWalletTypes($type = null)
  {
    if (is_array($type)) {
      return array_intersect_key(Wallet::getWallets(), array_flip($type));
    }
    return Wallet::getWallets($type);
  }

  /**
   * Название платежной системы
   * @return string
   */
  public function getWalletTypeLabel()
  {
    return self::getWalletTypes($this->wallet_type);
  }

  public function getCurrenciesDropdownItems()
  {
    if ($this->mainCurrencies !== null) {
      return $this->mainCurrencies;
    }

    return $this->mainCurrencies = Yii::$app->getModule('promo')->api('mainCurrencies', ['availablesOnly' => true])->setMapParams(['code', 'symbol'])->getMap();
  }

  /**
   * @return array ассоциативный массив реквизитов кошелька
   */
  public function getAccountAssoc($field = null)
  {
    $assoc = json_decode($this->wallet_account, true);
    return $field ? $assoc[$field] : $assoc;
  }

  /**
   * @return AbstractWallet|null заполненный данными из wallet_account
   */
  public function getAccountObject()
  {
    return $this->wallet_type
      ? Wallet::getObject($this->wallet_type, Json::decode($this->wallet_account), $this->user_id)
      : null;
  }

  /**
   * @return AbstractWallet|string|null
   */
  public function getAccountClass()
  {
    return $this->wallet_type
      ? Wallet::getWalletsClass($this->wallet_type)
      : null;
  }

  /**
   * Установить wallet_account с помощью объекта
   * @param AbstractWallet $account
   * @return bool
   */
  public function setAccount(AbstractWallet $account)
  {
    if (!$account->validate()) return false;

    $this->wallet_account = (string)$account;

    return true;
  }

  /**
   * Таблица с реквизитами
   * @param array $options
   * @return string
   */
  public function getWalletAccountInfo($options = [])
  {
    $wallet = Wallet::getObject($this->wallet_type, Json::decode($this->wallet_account));

    return Wallet::getAccountDetailView($wallet, $options);
  }

  /**
   * @param int $userId
   * @return ActiveQuery
   */
  public static function findByUser($userId)
  {
    return static::find()->andWhere(['user_id' => $userId]);
  }

  /**
   * @return ActiveQuery
   */
  public static function findByCurrentUser()
  {
    return static::findByUser(Yii::$app->user->id);
  }

  /**
   * @param $userId
   * @param $type
   * @return ActiveQuery
   */
  public static function findByUserAndType($userId, $type)
  {
    return static::findByUser($userId)->andWhere(['wallet_type' => $type]);
  }

  /**
   * Получаем путь к директории для загрузки файлов
   * @param $formname
   * @param $attribute
   * @return string
   */
  public static function getFilename($formname, $attribute)
  {
    /**
     * Если не существует класса $formname или его свойства $attribute, выходим
     * Для избежания сохранения файла вне директории /payments/user_wallets/
     * это возможно, если просунуть в $formname или $attribute что-то вроде '../../dir'
     */

    // Получаем имя класса
    $className = '\mcms\payments\models\wallet\\' . $formname;

    // Получаем имя атрибута
    preg_match('/\[(.+)' . WalletForm::FILE_FIELD_POSTFIX . '\]/', $attribute, $matches);
    $attributeName = ArrayHelper::getValue($matches, 1);

    // Если класс либо свойство отсутствуют, выходим
    if (!class_exists($className) || !property_exists($className, $attributeName)) {
      return null;
    }

    return '/payments/user_wallets/' . $formname . '/' . $attribute . '/';
  }

  /**
   * @inheritdoc
   */
  public function beforeSave($insert)
  {
    if ($this->is_deleted && $this->scenario != self::SCENARIO_DELETE) return false;

    return parent::beforeSave($insert);
  }

  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    // Только для инсерта, т.к. редактирование реализовано через помечание старой версии удаленной и инсерта новой
    if ($insert && (int)$this->is_autopayments === 1) {
      $this->updateAutopaymentWallet();
    }
  }

  /**
   * Если кошелек для автовыплат, во всех остальных кошельках партнера с данной валютой сбрасываем флаг
   */
  public function updateAutopaymentWallet()
  {
    parent::updateAll(['is_autopayments' => 0], [
      'and',
      ['user_id' => $this->user_id],
      ['currency' => $this->currency],
      ['is_autopayments' => 1],
      ['<>', 'id', $this->id]
    ]);
  }

  /**
   * Обновление кошелька.
   * Под обновлением подразумевается установка пометки "Удален" текущему кошельку и создание нового дублирующего кошелька,
   * но с новыми реквизитами. Это сделано для хранения истории изменений кошельков
   * TRICKY Логика дублируется в @see \mcms\payments\models\UserWalletGroup
   * @inheritdoc
   * @return bool|int int может быть возвращен только при сценарии DELETE
   */
  public function update($runValidation = true, $attributeNames = null)
  {
    if ($runValidation && !$this->validate($attributeNames)) {
      return false;
    }

    // Обновление записи при удалении @see delete()
    if ($this->scenario == self::SCENARIO_DELETE) {
      return parent::updateInternal($attributeNames);
    }

    $changedAttributes = array_keys(array_diff($this->getAttributes(), $this->getOldAttributes()));
    if (array_diff($changedAttributes, ['is_verified', 'is_autopayments']) === []) {
      // изменились только статусы, можно не обновлять сам кошелёк
      return $this->updateInternal($attributeNames);
    }

    return $this->renewInternal($attributeNames);
  }

  /**
   * @param null $attributes
   * @return bool
   */
  protected function renewInternal($attributes = null)
  {
    $values = $this->getDirtyAttributes($attributes);
    if (empty($values)) {
      $this->afterSave(false, $values);
      return 0;
    }

    $transaction = static::getDb()->beginTransaction();
    try {
      // Подготовка нового кошелька дублирующего текущий
      $newWallet = new static($this->getAttributes());
      unset($newWallet->id);

      // Удаление текущего кошелька и создание нового кошелька
      // TRICKY insert должен быть без валидации, так как валидация была сделана в модели от которой был сделан клон,
      // иначе будет валидироваться уникальность и подобное, что в данном случае может привести к ошибкам типа "такой кошелек уже существует"
      if ($this->delete() === false || !$newWallet->insert(false)) {
        throw new ModelNotSavedException;
      }

      $transaction->commit();
    } catch (\Exception $exception) {
      $transaction->rollBack();
      return false;
    }

    return true;
  }

  /**
   * Удаление кошелька.
   * Под удалением подразумевается установка пометки "Удален", так как кошелек не может быть полностью удален для хранения
   * истории изменений
   * TRICKY Логика дублируется в @see \mcms\payments\models\UserWalletGroup
   * @return false|int
   */
  public function delete()
  {
    // TRICKY Если не использовать refresh, кроме свойства is_deleted могут прокинуться другие изменения
    $this->refresh();
    $this->setScenario(self::SCENARIO_DELETE);
    $this->is_deleted = true;

    return $this->update();
  }

  /**
   * Удаление всех кошельков
   * TRICKY Вместо удаления применяется обновление (скрытие кошелька), что бы хранить историю изменений кошельков
   * @inheritdoc
   */
  public static function deleteAll($condition = '', $params = [])
  {
    return static::updateAll(['is_deleted' => true], $condition, $params);
  }

  /**
   * @param bool $excludeDeleted Исключать из результата удаленные кошельки
   * @return UserWalletsQuery
   */
  public static function find($excludeDeleted = true)
  {
    $query = new UserWalletsQuery(static::class);
    if ($excludeDeleted) $query->andWhere(['is_deleted' => false]);

    return $query;
  }

  /**
   * @return ActiveQuery
   */
  public function getWalletType()
  {
    return $this->hasOne(Wallet::class, ['id' => 'wallet_type']);
  }

  /**
   * @return number
   */
  public function getDailyLimitUse()
  {
    list($walletId, $currency) = $this->getWalletsForLimits($this->id, $this->currency);
    return (new UserPayment)->getDailyLimitUse($walletId, $currency);
  }

  /**
   * @return number
   */
  public function getMonthlyLimitUse()
  {
    list($walletId, $currency) = $this->getWalletsForLimits($this->id, $this->currency);
    return (new UserPayment)->getMonthlyLimitUse($walletId, $currency);
  }

  /**
   * Получить первую ошибку не привязываясь к аттрибуту
   * @return string
   */
  public function getOneError()
  {
    $firstErrors = $this->getFirstErrors();
    return reset($firstErrors);
  }

  /**
   * Получаем id кошельков и валюты для получения лимитов
   * @param $userWalletId
   * @param $currency
   * @return array
   */
  public function getWalletsForLimits($userWalletId, $currency)
  {
    $userWallet = UserWallet::findOne($userWalletId);
    //Если карта мултивалютная usd eur, нужно считать лимиты суммируя два кошелька usd и eur
    if ($userWallet->walletType->isCard() && in_array($currency, ['usd', 'eur'])) {
      $uniqueValue = $userWallet->getAccountObject()->getUniqueValue();
      $userWallets = UserWallet::findByUser($userWallet->user_id)->all();
      $userWalletIds = [];
      foreach ($userWallets as $wallet) { /* @var $wallet UserWallet*/
        if($wallet->getAccountObject()->getUniqueValue() == $uniqueValue) {
          $userWalletIds[$wallet->currency] = $wallet->id;
        }
      }

      if(count($userWalletIds) == 2 && !array_key_exists('rub', $userWalletIds)) {
        $userWalletId = $userWalletIds;
        $currency = ['usd', 'eur'];
      }
    }

    return [$userWalletId, $currency];
  }
}
