<?php

namespace mcms\payments\models\wallet;

use baibaratsky\WebMoney\Signer;
use baibaratsky\WebMoney\WebMoney as WebmoneyApi;
use baibaratsky\WebMoney\Api\X\X8;
use mcms\payments\components\WebMoneyCurlRequester;
use mcms\payments\models\paysystems\PaySystemApi;
use Yii;
use mcms\payments\models\UserPayment;
use yii\widgets\ActiveForm;

/**
 * Class Wme
 * @package mcms\payments\models\wallet
 *
 * @property string wallet
 */
class WebMoney extends AbstractWallet
{
  public $wallet;

  //fixme убрать хардкод
  public static $currency = ['rub', 'usd', 'eur'];

  public static $isSingleCurrency = true;

  /**
   * @inheritdoc
   */
  public function getUniqueValue()
  {
    return $this->wallet;
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['wallet'], 'required'],
      [['wallet'], 'match', 'pattern' => '/^[REZ][0-9]{12}$/'],
      ['wallet', 'validateExistence'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function validateExistenceInternal()
  {
    $sender = Yii::createObject('mcms\payments\components\mgmp\send\MgmpSenderInterface');
    $result = $sender->checkWebmoney($this->wallet);

    if (isset($result['status'])) {
      if ($result['status'] === 'error') {
        // кошелек не верный (единственная валидация, пока эту прямо так)
        $this->addError('wallet', Yii::_t('payments.wallets.error-wallet_does_not_exist'));
        return false;
      }
      // все в порядке
      return true;
    }
    // ошибка авторизации или мгмп не доступен, тогда считаем кошелек правильным
    return true;
  }


  /**
   * @inheritDoc
   */
  public function attributePlaceholders()
  {
    return [
      'wallet' => Yii::_t('payments.wallets.placeholder-webmoney-wallet')
    ];
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.webmoney');
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'walletType' => self::translate('attribute-wallet_type'),
      'wallet' => self::translate('attribute-wallet'),
      'attestat' => self::translate('attribute-attestat'),
      'attestat_type' => self::translate('attribute-attestat_type'),
      'datereg' => self::translate('attribute-datereg'),
      'bl' => self::translate('attribute-bl'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    return [
      'wallet' => $this->getForm($form)->maskedTextInput('wallet', ['mask' => 'A9{12}', 'definitions' => [
        'A' => [
          'validator' => '[REZrez]',
          'cardinality' => 1,
          'casing' => 'upper',
        ],
        '9' => [
          'validator' => "\\d",
          'cardinality' => 1,
        ],
      ],'clientOptions' => [
        'placeholder' => 'Xxxxxxxxxxxxx',
      ]]),
    ];
  }

  /**
   * Получение списка параметров для экспорта в csv: данных, разделителя и кавычек
   * @param UserPayment $payment
   * @return array
   */
  public static function getExportRowParameters(UserPayment $payment)
  {
    return [
      [
        $payment->userWallet->getAccountAssoc('wallet'),
        Yii::$app->formatter->asDecimal($payment->amount, 2),
        Yii::$app->formatter->asDate($payment->created_at),
        $payment->id
      ],
      self::EXPORT_DELIMITER,
      self::EXPORT_ENCLOSURE
    ];
  }

  /**
   * @inheritDoc
   */
  public function protectedAttributes()
  {
    return ['wallet'];
  }

  /**
   * @inheritDoc
   */
  public function getMinPayoutSumRub()
  {
    return Wallet::findOne(Wallet::WALLET_TYPE_WEBMONEY)->rub_min_payout_sum ?: parent::getMinPayoutSumRub();
  }

  /**
   * @inheritDoc
   */
  public function getMinPayoutSumEur()
  {
    return Wallet::findOne(Wallet::WALLET_TYPE_WEBMONEY)->eur_min_payout_sum ?: parent::getMinPayoutSumEur();
  }

  /**
   * @inheritDoc
   */
  public function getMinPayoutSumUsd()
  {
    return Wallet::findOne(Wallet::WALLET_TYPE_WEBMONEY)->usd_min_payout_sum ?: parent::getMinPayoutSumUsd();
  }


}
