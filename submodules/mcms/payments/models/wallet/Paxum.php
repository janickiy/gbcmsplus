<?php

namespace mcms\payments\models\wallet;


use Yii;
use mcms\payments\models\UserPayment;
use yii\widgets\ActiveForm;

class Paxum extends AbstractWallet
{
  public $email;

  //fixme убрать хардкод
  public static $currency = ['usd', 'eur'];

  /**
   * @inheritdoc
   */
  public function getUniqueValue()
  {
    return $this->email;
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['email'], 'required'],
      [['email'], 'match', 'pattern' => '/T[A-z\d]{33}/i'],
    ]);
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.paxum');
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
        $payment->userWallet->getAccountAssoc('email'),
        Yii::$app->formatter->asDecimal($payment->amount, 2),
        Yii::$app->formatter->asDate($payment->created_at),
        $payment->id
      ],
      self::EXPORT_DELIMITER,
      self::EXPORT_ENCLOSURE
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributePlaceholders()
  {
    return [
      'email' => 'Txxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    ];
  }
  
  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'email' => "TRC20 Account",
    ];
  }
}
