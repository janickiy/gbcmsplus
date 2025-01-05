<?php
/**
 * @copyright Copyright (c) 2024 VadimTs
 * @link https://tsvadim.dev/
 * Creator: VadimTs
 * Date: 03.07.2024
 */

namespace mcms\payments\models\wallet;

use Yii;

class Capitalist extends AbstractWallet
{
  public $wallet;
  
  //fixme убрать хардкод
  public static $currency = ['rub', 'usd', 'eur'];
  
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
      [['wallet'], 'required'],
      //[['wallet'], 'match', 'pattern' => '/T[A-z\d]{33}/i'],
      [['wallet'], 'match', 'pattern' => '/^[REU][0-9]{7,12}$/'],
    ]);
  }
  
  /**
   * @inheritdoc
   */
  public function attributePlaceholders()
  {
    return [
      'wallet' => Yii::_t('payments.wallets.placeholder-capitalist-wallet'),
    ];
  }
  
  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'wallet' => self::translate('attribute-wallet'),
    ];
  }
  
  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.capitalist', [], $language);
  }
}