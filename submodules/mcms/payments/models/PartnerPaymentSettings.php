<?php

namespace mcms\payments\models;

use Yii;
use mcms\user\models\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%partner_payment_settings}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $amount
 * @property integer $totality
 * @property integer $wallet_id
 * @property integer $invoicing_cycle
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $last_checked_at
 * @property string $message
 *
 * @property User $user
 * @property UserWallet $wallet
 */
class PartnerPaymentSettings extends \yii\db\ActiveRecord
{
  
  const INVOICING_CYCLE_DAILY = 1;
  const INVOICING_CYCLE_WEEKLY = 2;
  const INVOICING_CYCLE_MONTHLY = 3;
  
  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      [
        'class' => BlameableBehavior::class,
        'createdByAttribute' => 'user_id',
        'updatedByAttribute' => false,
        'attributes' => [
          BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'user_id',
          BaseActiveRecord::EVENT_BEFORE_INSERT => 'user_id',
        ]
      ]
    ];
  }
  
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%partner_payment_settings}}';
  }
  
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['wallet_id', 'invoicing_cycle'], 'required'],
      [['wallet_id', 'invoicing_cycle','last_checked_at'], 'integer'],
      [['totality'], 'boolean'],
      [['amount'], 'required', 'when' => function ($model) {
        return $model->totality == 0;
      }, 'whenClient' => "function (attribute, value) {
                  return $('#".Html::getInputId($this,'totality')."').prop('checked') == false;
            }"],
      [['amount'], 'default', 'value' => 0],
      [['amount'], 'number', 'min' => 0],
      [['amount','wallet_id'], 'validateMin','clientValidate' => 'clientValidateMin', 'when' => function ($model) {
        return $model->totality == 0;
      }, 'whenClient' => "function (attribute, value) {
                  return $('#".Html::getInputId($this,'totality')."').prop('checked') == false;
            }"],
      [['invoicing_cycle'], 'in','range' => [self::INVOICING_CYCLE_DAILY,self::INVOICING_CYCLE_WEEKLY,self::INVOICING_CYCLE_MONTHLY]],
      [['wallet_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserWallet::class, 'targetAttribute' => ['wallet_id' => 'id','user_id' => 'user_id']],
    ];
  }
  
  public function validateMin($attribute, $params)
  {
    /** @var UserWallet $userWallet */
    $userWallet = Yii::$app->getModule('payments')->api('userWallet',['walletId'=>$this->wallet_id,'user_id' => Yii::$app->user->getId()])->getResult();;

    $minimalAmount = $userWallet->walletType->getMinPayoutByCurrency($userWallet->currency);
    $profitPercent = $userWallet->walletType->profit_percent;
    $totalMin = ($profitPercent < 0) ? $minimalAmount - $profitPercent : $minimalAmount + $profitPercent;
    if($totalMin > 0){
      if($totalMin > $this->amount){
        $this->addError('amount',Yii::_t('payments.partner-companies.minimum_withdrawal_amount_including_commission {value}',['value' => Yii::$app->formatter->asCurrencyDecimal($totalMin,$userWallet->currency)]));
      }
    }
  }
  
  public function clientValidateMin($attribute, $params, $validator) {
    $userWallets = Yii::$app->getModule('payments')->api('userWallet')->getUserWallets();
    
    $userWalletsEncode = Json::encode(array_map(function ($val){
      /** @var  $val UserWallet */
      return ['id' => $val->id,
        'currency' => $val->currency,
        'wallet_type' => $val->wallet_type,
        'min' => is_numeric($val->walletType->getMinPayoutByCurrency($val->currency)) ? $val->walletType->getMinPayoutByCurrency($val->currency) : 0,
        'profit_percent' => is_numeric($val->walletType->profit_percent) ? $val->walletType->profit_percent : 0
      ];
    },$userWallets));
    $walletInputId = Html::getInputId($this,'wallet_id');
    $amountInputId = Html::getInputId($this,'amount');
    $message = Json::encode(Yii::_t('payments.partner-companies.minimum_withdrawal_amount_including_commission {value}'));
    return <<<JS
  let paymentSysytems = {$userWalletsEncode};
  let currentWallet = $('#{$walletInputId}').val();
  let amount = isNaN(parseFloat($('#{$amountInputId}').val())) ? 0 : parseFloat($('#{$amountInputId}').val()).toFixed(2);
  
  if(paymentSysytems.length > 0){
    conditions = getPaymentSystem(paymentSysytems,currentWallet);
    
    let profitPercent = calcProfitPercent(amount,conditions);
    let totalMin = (profitPercent < 0) ? parseFloat(conditions.min) - profitPercent : parseFloat(conditions.min) + profitPercent
    
    if(totalMin > 0){
        if(totalMin > amount){
           let errorText = {$message}.replace(/\{value\}/g,totalMin+" "+currencySymbol(conditions.currency));
            if(attribute.name == 'amount'){
                messages.push(errorText)
            }else{
              $('#'+attribute.\$form.attr('id')).yiiActiveForm('updateAttribute', '{$amountInputId}', [errorText]);
            }
        }
    }

    function getPaymentSystem(paymentSysytems, currentWallet) {
        return paymentSysytems.find(function (elm, index, array) {
            if (elm.id == currentWallet) {
                return elm;
            }
            return undefined;
        })
    }

    function calcProfitPercent(amount, conditions) {
        if (amount > 0 && amount >= parseFloat(conditions.min)) {
            return amount / 100 * parseFloat(conditions.profit_percent)
        }
        return parseFloat(conditions.profit_percent);
    }

    function currencySymbol(currency) {
        switch (currency) {
            case RUR:
                currency = "₽";
                break;
            case EUR:
                currency = "€";
                break;
            case USD:
                currency = "$";
                break;
        }
        return currency;
    }
}
JS;
  }
  
  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('payments.partner-companies.id'),
      'user_id' => Yii::_t('payments.partner-companies.user id'),
      'amount' => Yii::_t('payments.partner-companies.amount'),
      'totality' => Yii::_t('payments.partner-companies.totality'),
      'wallet_id' => Yii::_t('payments.partner-companies.wallet id'),
      'invoicing_cycle' => Yii::_t('payments.partner-companies.invoicing cycle'),
      'created_at' => Yii::_t('payments.partner-companies.created at'),
      'updated_at' => Yii::_t('payments.partner-companies.updated at'),
      'last_checked_at' => Yii::_t('payments.partner-companies.last checked at'),
    ];
  }
 
  public static function getInvoicingCycles()
  {
    return [
      self::INVOICING_CYCLE_DAILY => Yii::_t('payments.partner-companies.invoicing_cycle-daily'),
      self::INVOICING_CYCLE_WEEKLY => Yii::_t('payments.partner-companies.invoicing_cycle-weekly'),
      self::INVOICING_CYCLE_MONTHLY => Yii::_t('payments.partner-companies.invoicing_cycle-monthly')
    ];
  }
  
  
  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }
  
  /**
   * @return \yii\db\ActiveQuery
   */
  public function getWallet()
  {
    return $this->hasOne(UserWallet::class, ['id' => 'wallet_id']);
  }
}
