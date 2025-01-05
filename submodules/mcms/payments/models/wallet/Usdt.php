<?php

namespace mcms\payments\models\wallet;

class Usdt extends AbstractWallet
{
  public $wallet;
  
  //fixme убрать хардкод
  public static $currency = ['usd'];
  
  /**
   * @inheritDoc
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
      [['wallet'], 'match', 'pattern' => '/T[A-z\d]{33}/i'],
    ]);
  }
  
  /**
   * @inheritdoc
   */
  public function attributePlaceholders()
  {
    return [
      'wallet' => 'Txxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    ];
  }
  
  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'wallet' => "TRC20 Account",
    ];
  }
  
  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return \Yii::_t('payments.wallets.usdt', [], $language);
  }
}