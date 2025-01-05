<?php

namespace mcms\payments\models\wallet;

use mcms\payments\models\UserPayment;
use Yii;
use yii\widgets\ActiveForm;

/**
 * Class Qiwi
 * @package mcms\payments\models\wallet
 *
 * @property string wallet
 */
class Qiwi extends AbstractWallet
{
  public $phone_number;

  //fixme убрать хардкод
  public static $currency = ['rub'];

  protected static $isLocalityRu = true;

  /**
   * @inheritdoc
   */
  public function getUniqueValue()
  {
    return $this->phone_number;
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['phone_number'], 'required'],
//      ['phone_number', 'filter', 'filter' => [$this, 'filterNumber']],
      [['phone_number'], 'match', 'skipOnEmpty' => false, 'pattern' => '/^\+[\d]{11,12}$/i', 'message' => self::translate('message-phone_number_invalid')],
    ]);
  }

  public function filterNumber($value)
  {
    return preg_replace('/[-() ]/', '', $value);
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.qiwi');
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'phone_number' => self::translate('attribute-phone_number'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    return [
      'phone_number' => $this->getForm($form)->maskedTextInput('phone_number', ['mask' => '+9{11,12}']),
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributePlaceholders()
  {
    return [
      'phone_number' => '+xxxxxxxxxxx'
    ];
  }


  /**
   * @inheritDoc
   */
  public function getMinPayoutSumRub()
  {
    return Wallet::findOne(Wallet::WALLET_TYPE_QIWI)->rub_min_payout_sum ? : parent::getMinPayoutSumRub();
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
        $payment->userWallet->getAccountAssoc('phone_number'),
        Yii::$app->formatter->asDecimal($payment->amount, 2),
        Yii::$app->formatter->asDate($payment->created_at),
        $payment->id
      ],
      self::EXPORT_DELIMITER,
      self::EXPORT_ENCLOSURE
    ];
  }
}