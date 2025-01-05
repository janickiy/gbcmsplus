<?php

namespace mcms\payments\models\wallet;

use mcms\common\helpers\Html;
use mcms\payments\models\UserPayment;
use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Модель реквизитов юр. лица
 * Class PrivatePerson
 * @package mcms\payments\models\wallet
 */
class PrivatePerson extends AbstractWallet
{
  // данные
  public $ip_name;
  public $juridical_address;
  public $actual_address;
  public $same_address = 1;
  public $phone_number;
  public $email;

  // заполняет менеджер
  public $inn;
  public $ogrn;
  public $ogrn_date;
  public $ip_certificate_number;

  // сканы
  public $scan_tax_registration;
  public $scan_ip_registration;

  // банковские реквизиты
  public $account;
  public $bank_name;
  public $kor;
  public $bik;
  //  public $nds;


  protected static $isLocalityRu = true;

  //fixme убрать хардкод
  public static $currency = ['rub'];

  /**
   * @inheritdoc
   */
  public function getUniqueValue()
  {
    return $this->ip_name;
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['ip_name', 'actual_address', 'juridical_address', 'ogrn', 'bank_name'], 'string', 'max' => 255],
      [['ip_name', 'juridical_address', 'bank_name', 'email'], 'required'],
      ['inn', 'match', 'skipOnEmpty' => true, 'pattern' => '/^\d{12}$/', 'message' => self::translate('message-invalid_inn')],
      ['account', 'match', 'skipOnEmpty' => false, 'pattern' => '/^\d{20}$/', 'message' => self::translate('message-invalid_account')],
      ['kor', 'match', 'skipOnEmpty' => false, 'pattern' => '/^\d{20}$/', 'message' => self::translate('message-invalid_kor')],
      ['bik', 'match', 'skipOnEmpty' => false, 'pattern' => '/^\d{9}$/', 'message' => self::translate('message-invalid_bik')],
      [['phone_number'], 'match', 'skipOnEmpty' => false, 'pattern' => '/^\+[\d]{11,12}$/i', 'message' => self::translate('message-phone_number_invalid')],
      ['ogrn_date', 'match', 'pattern' => '/^\d{4}-\d{2}-\d{2}$/i', 'message' => self::translate('message-invalid_ogrn_date')],
      ['email', 'email', 'checkDNS' => true],
      ['ip_certificate_number', 'safe'],

      //      ['nds', 'boolean'],
      ['same_address', 'boolean'],
      ['actual_address', 'required', 'when' => function() { return !$this->same_address; }],
      ['actual_address', 'filter', 'filter' => function() { return ''; }, 'when' => function() { return $this->same_address; }],
      [['scan_tax_registration', 'scan_ip_registration'], 'string'],
      [['scan_tax_registration', 'scan_ip_registration'], 'filter', 'filter' => 'addslashes'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function getCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    $walletForm = $this->getForm($form);

    $onStartUpload = '$(\'' . $submitButtonSelector . '\').prop("disabled", true)';
    $onEndUpload = '$(\'' . $submitButtonSelector . '\').prop("disabled", false)';


    return $this->getCommonCustomFields($walletForm, $options) + [
        'inn' => null,
        'ogrn' => null,
        'ogrn_date' => null,
        'ip_certificate_number' => null,
        'scan_tax_registration' => $walletForm->imageInput('scan_tax_registration', $options, $onStartUpload, $onEndUpload),
        'scan_ip_registration' => $walletForm->imageInput('scan_ip_registration', $options, $onStartUpload, $onEndUpload),

      ];
  }

  /**
   * @inheritdoc
   */
  public function getAdminCustomFields(ActiveForm $form, $options = [])
  {
    $walletForm = $this->getForm($form);

    return $this->getCommonCustomFields($walletForm, $options) + [
        'scan_tax_registration' => $walletForm->hiddenInput('scan_tax_registration') .
          $walletForm->fileManage('scan_tax_registration', self::translate('download')),
        'scan_ip_registration' => $walletForm->hiddenInput('scan_ip_registration') .
          $walletForm->fileManage('scan_ip_registration', self::translate('download')),
        'ogrn_date' => $this->getForm($form)->maskedTextInput('ogrn_date', ['mask' => '9999-99-99']),
        'inn' => $walletForm->maskedTextInput('inn', ['mask' => '9{12}']),
        'ogrn' => $walletForm->maskedTextInput('ogrn', ['mask' => '9{15}']),
        'ip_certificate_number' => $walletForm->maskedTextInput('ip_certificate_number', ['mask' => '9{2} 9{9}']),
      ];
  }

  /**
   * @param WalletForm $walletForm
   * @param $options
   * @return array
   */
  private function getCommonCustomFields(WalletForm $walletForm, $options)
  {
    $actualAddressHtmlOptions = $this->same_address ? ['options' => ['class' => 'form-group hidden']] : [];

    return [
      //      'nds' => $walletForm->checkbox('nds', $options),
      'account' => $walletForm->maskedTextInput('account', ['mask' => '9{20}']),
      'kor' => $walletForm->maskedTextInput('kor', ['mask' => '9{20}']),
      'bik' => $walletForm->maskedTextInput('bik', ['mask' => '9{9}']),
      'phone_number' => $walletForm->maskedTextInput('phone_number', ['mask' => '+9{11}']),
      'actual_address' => $walletForm->textInput('actual_address', $actualAddressHtmlOptions),
      'same_address' => $walletForm->checkbox('same_address', $options, [
        'onchange' => '$(this).is(":checked") ? $("#'.Html::getInputId($this, 'actual_address').'").parent().addClass("hidden") : $("#'.Html::getInputId($this, 'actual_address').'").parent().removeClass("hidden")',
      ]),
    ];
  }

  public function getImageAttributes()
  {
    return ['scan_tax_registration', 'scan_ip_registration'];
  }

  /**
   * @inheritdoc
   */
  protected function handleDetailViewAttribute($attributes)
  {
    if ($this->same_address) {
      $attributes['same_address']['value'] = Yii::_t('app.common.Yes');
      ArrayHelper::remove($attributes, 'actual_address');
    } else {
      ArrayHelper::remove($attributes, 'same_address');
    }

    $attributes['scan_tax_registration'] = [
      'attribute' => 'scan_tax_registration',
      'format' => 'raw',
      'value' => !$this->scan_tax_registration ? '-' : Html::a(Yii::t('yii', 'View') . ' <i class="fa fa-external-link"></i>', $this->getFileUrl('scan_tax_registration'), [
        'target' => '_blank',
        'data-pjax' => 0
      ], true),
    ];

    $attributes['scan_ip_registration'] = [
      'attribute' => 'scan_ip_registration',
      'format' => 'raw',
      'value' => !$this->scan_ip_registration ? '-' : Html::a(Yii::t('yii', 'View') . ' <i class="fa fa-external-link"></i>', $this->getFileUrl('scan_ip_registration'), [
        'target' => '_blank',
        'data-pjax' => 0
      ], true),
    ];

//    $attributes['nds']['value'] = $this->nds ? Yii::_t('app.common.Yes') : Yii::_t('app.common.No');

    return $attributes;
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'ip_name' => self::translate('attribute-ip_name'),
      'juridical_address' => self::translate('attribute-juridical_address'),
      'same_address' => self::translate('attribute-same_address'),
      'actual_address' => self::translate('attribute-actual_address'),
      'phone_number' => self::translate('attribute-phone_number'),
      'email' => self::translate('attribute-email'),
      'ogrn' => self::translate('attribute-ogrn-ip'),
      'ogrn_date' => self::translate('attribute-ogrn-ip_date'),
      'ip_certificate_number' => self::translate('attribute-ip_certificate_number'),
      'scan_tax_registration' => self::translate('attribute-scan_tax_registration'),
      'scan_ip_registration' => self::translate('attribute-scan_ogrn-ip_registration'),
      'inn' => self::translate('attribute-inn'),
      'account' => self::translate('attribute-account'),
      'bank_name' => self::translate('attribute-bank_name'),
      'kor' => self::translate('attribute-kor'),
      'bik' => self::translate('attribute-bik'),
      //      'nds' => self::translate('attribute-nds'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributePlaceholders()
  {
    return [
      'ip_name' => self::translate('placeholder-ip_name'),
      'juridical_address' => self::translate('placeholder-juridical_address'),
      'actual_address' => self::translate('placeholder-actual_address'),
      'ogrn' => self::translate('placeholder-ogrn'),
      'ogrn_date' => self::translate('placeholder-ogrn_date'),
      'ip_certificate_number' => self::translate('placeholder-ip_certificate_number'),
      'inn' => self::translate('placeholder-inn'),
      'account' => self::translate('placeholder-account'),
      'bank_name' => self::translate('placeholder-bank_name'),
      'kor' => self::translate('placeholder-kor'),
      'bik' => self::translate('placeholder-bik'),
      'email' => self::translate('placeholder-email'),
      'phone_number' => self::translate('placeholder-phone_number'),
    ];
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.private_person', [], $language);
  }

  /**
   * @inheritDoc
   */
  public function getMinPayoutSumRub()
  {
    return Wallet::findOne(Wallet::WALLET_TYPE_PRIVATE_PERSON)->rub_min_payout_sum ?: parent::getMinPayoutSumRub();
  }


  /**
   * Получение списка параметров для экспорта в csv: данных, разделителя и кавычек
   * @param UserPayment $payment
   * @return array
   */
  public static function getExportRowParameters(UserPayment $payment)
  {
    return [
      [$payment->userWallet->getAccountAssoc('ip_name'),
        $payment->userWallet->getAccountAssoc('juridical_address'),
        $payment->userWallet->getAccountAssoc('actual_address'),
        $payment->userWallet->getAccountAssoc('phone_number'),
        $payment->userWallet->getAccountAssoc('email'),
        $payment->userWallet->getAccountAssoc('ogrn'),
        $payment->userWallet->getAccountAssoc('ogrn_date'),
        $payment->userWallet->getAccountAssoc('ip_certificate_number'),
        $payment->userWallet->getAccountAssoc('inn'),
        $payment->userWallet->getAccountAssoc('account'),
        $payment->userWallet->getAccountAssoc('bank_name'),
        $payment->userWallet->getAccountAssoc('kor'),
        $payment->userWallet->getAccountAssoc('bik'),
        //        $payment->userWallet->getAccountAssoc('nds'),
        Yii::$app->formatter->asDecimal($payment->amount, 2),
        Yii::$app->formatter->asDate($payment->created_at),
        $payment->id,
      ],
      self::EXPORT_DELIMITER,
      self::EXPORT_ENCLOSURE
    ];
  }

}
