<?php

namespace mcms\payments\models\wallet;

use kartik\builder\Form;
use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\UserPayment;
use rgk\payprocess\components\utils\EpaymentsAPI;
use Yii;
use yii\widgets\ActiveForm;

/**
 * Class Epayments
 * @package mcms\payments\models\wallet
 *
 * @property string wallet
 */
class Epayments extends AbstractWallet
{
  public $wallet;

  //fixme убрать хардкод
  public static $currency = ['usd', 'eur'];


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
      [['wallet'], 'match', 'pattern' => '/^\d{3}-\d{6}$/'],
    ]);
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'wallet' => self::translate('attribute-wallet')
    ];
  }

  /**
   * @inheritdoc
   */
  public function getCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    return [
      'wallet' => $this->getForm($form)->maskedTextInput('wallet', ['mask' => '9{3}-9{6}']),
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributePlaceholders()
  {
    return [
      'wallet' => '000-123456'
    ];
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.epayments');
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
}