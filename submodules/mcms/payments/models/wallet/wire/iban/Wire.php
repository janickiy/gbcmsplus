<?php

namespace mcms\payments\models\wallet\wire\iban;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\mgmp\send\ApiMgmpSender;
use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\AbstractWallet;
use Yii;
use yii\helpers\Html;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\widgets\ActiveForm;

/**
 * Модель WireIban кошелька
 * Class Wire
 * @package mcms\payments\models\wallet\wire\iban
 */
class Wire extends AbstractWallet
{
  const INVALID_CODES = [
    '001' => false, // это ок
    '002' => false,
    '003' => false,
    '004' => false,
    '005' => false,
    '201' => true, // а это ошибка
    '202' => true,
    '203' => true,
    '205' => true,
    '301' => true,
    '302' => true,
    '303' => true,
    '304' => true,
  ];
  const API_URL = 'https://api.iban.com/clients/api/ibanv2.php';

  const TYPE_IBAN = 0;
  const TYPE_ACCOUNT_NUMBER = 1;

  public $bank_name;
  public $bank_county;
  public $bank_address;
  public $recipient;
  public $recipient_country;
  public $recipient_address;
  public $iban_code;
  public $swift_code;
  /** @var integer переключатель между IBAN и Account Number */
  public $account_type = self::TYPE_IBAN;
  public $account_number;
  public $comment;
  /** @var mixed TODO МЕГАКОСТЫЛЬ, хранит данные по банку, чтобы на фронте прочитать эти данные */
  public $_bankData;

  //fixme убрать хардкод
  public static $currency = ['usd', 'eur'];

  /**
   * @inheritdoc
   */
  public function getUniqueValue()
  {
    return $this->iban_code;
  }

  /**
   * @inheritdoc
   */
  public function getUniqueValueProtected()
  {
    return preg_replace('/(.{2}).+(.{4})/', '$1**$2', $this->getUniqueValue());
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      ['iban_code', 'filter', 'filter' => 'strtoupper'],
      ['iban_code', 'filter', 'filter' => function ($value) {
        return str_replace(' ', '', $value);
      }],
      [['iban_code', 'recipient', 'recipient_country', 'recipient_address'], 'trim'],
      [['recipient', 'recipient_country', 'recipient_address'], 'required'],
      [['iban_code'], 'required', 'when' => function() { return $this->account_type == self::TYPE_IBAN; }],
      [['account_number', 'swift_code'], 'required', 'when' => function() { return $this->account_type == self::TYPE_ACCOUNT_NUMBER; }],
      [['iban_code', 'swift_code', 'bank_name', 'bank_county', 'bank_address', 'recipient', 'recipient_country',
        'recipient_address', 'account_number'], 'string', 'max' => 255],
      ['comment', 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
      [['account_number'], 'filter', 'filter' => function() { return ''; }, 'when' => function() { return $this->account_type == self::TYPE_IBAN; }],
      [['iban_code'], 'filter', 'filter' => function() { return ''; }, 'when' => function() { return $this->account_type == self::TYPE_ACCOUNT_NUMBER; }],
      ['iban_code', 'validateExistence', 'when' => function() { return $this->account_type == self::TYPE_IBAN; }],
      ['account_type', 'integer'],
    ]);
  }

  /**
   * TRICKY _bankData может быть перезаписан в @see afterValidate()
   * @inheritdoc
   */
  protected function validateExistenceInternal()
  {
    // TODO Рефакторинг: @see \mcms\payments\models\wallet\AbstractWallet::validateExistenceInternal()

    /**
     * @var ApiMgmpSender $sender
     */
    $sender = Yii::createObject('mcms\payments\components\mgmp\send\MgmpSenderInterface');
    $arrResponse = $sender->checkIbanCode($this->iban_code);

    $errors = [];
    if (isset($arrResponse['validations']) && is_array($arrResponse['validations'])) {
      foreach ($arrResponse['validations'] as $validation) {
        if (ArrayHelper::getValue(self::INVALID_CODES, $validation['code'])) {
          $errors['iban_code'] = Yii::_t('payments.wallets.error-wallet_does_not_exist');
          // одной ошибки достаточно
          break;
        }
      }
    }

    if (is_array($arrResponse)) {
      $bankData = ArrayHelper::getValue($arrResponse, 'bank_data');
      if (
        is_array($bankData)
        // Если данных о банке нет и сервис вернул ошибки, значит API недоступно
        && !(empty($bankData) && !empty($arrResponse['errors']))
      ) {
        $bankCity = ArrayHelper::getValue($bankData, 'city', '');
        $bankAddress = ArrayHelper::getValue($bankData, 'address', '');
        if ($bankCity) {
          $fullAddress = $bankCity . ($bankAddress ? ', ' . $bankAddress : '');
        } else {
          $fullAddress = $bankAddress;
        }

        $data = [ // TODO КОСТЫЛЬ иначе ключи внутри массива стираются :(
          'bank_county' => ArrayHelper::getValue($bankData, 'country', ''),
          'swift_code' => ArrayHelper::getValue($bankData, 'bic', ''),
          'bank_address' => $fullAddress,
        ];

        if (!empty($data['bank_address'])) {
          $this->bank_address = $data['bank_address'];
        }
        if (!empty($data['bank_county'])) {
          $this->bank_county = $data['bank_county'];
        }
        if (!empty($data['swift_code'])) {
          $this->swift_code = $data['swift_code'];
        }

        if (!Yii::$app->request->post('submit')) {
          $this->addErrors(['_bankData' => [
            'kostyl_key' => $data,
          ]]);
        }
      }
    }

    foreach ($errors as $attr => $message) {
      $this->addError($attr, $message);
    }

    return empty($errors);
  }

  public function afterValidate()
  {
    // Если IBAN код введен неверно (есть ошибки валидации),
    // возвращать данные о банке не нужно, даже если удалось определить информацию использую часть кода
    $errors = $this->getErrors();
    if (isset($errors['iban_code']) && isset($errors['_bankData'])) {
      $this->clearErrors('_bankData');
    }

    parent::afterValidate();
  }

  public function getCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    $walletForm = $this->getForm($form);

    $bankOptions = $options;
    $bankOptions['options'] = $this->account_type == self::TYPE_IBAN ?
       array_merge(ArrayHelper::getValue($options, 'options'), ['style' => 'display: none']) :
      ArrayHelper::getValue($options, 'options')
    ;

    $ibanCodeOptions = $options;
    $ibanCodeOptions['options'] = $this->account_type == self::TYPE_ACCOUNT_NUMBER ?
      array_merge(ArrayHelper::getValue($options, 'options'), ['style' => 'display: none']) :
      ArrayHelper::getValue($options, 'options')
    ;

    $alphanumOnly = [
      'mask' => '[*{*}]',
      'definitions' => ['*' => ['validator' => '^[a-zA-Z0-9 ]*$']], // иначе по-умолчанию * разрешает вводить кириллицу
      'clientOptions' => ['greedy' => false],
    ];

    return [
      'iban_code' => $walletForm->maskedTextInput('iban_code', array_merge([
        'mask' => '**** [*{4} ]{1,7}[*{2}]',
        'definitions' => [
          '*' => [ // иначе по-умолчанию * разрешает вводить кириллицу
            'validator' => '^[a-zA-Z0-9]*$',
          ],
        ],
        'options' => [
          'style' => 'text-transform: uppercase;'
        ],
        'clientOptions' => [
          'placeholder' => '',
          'greedy' => false,
          'groupSeparator' => ' ',
          'autoUnmask' => true,
          'removeMaskOnSubmit' => true,
        ],
      ]), $ibanCodeOptions),
      'account_type' => $walletForm->radioList('account_type', $this->getAccountTypes(), [
        'onchange' => 'if ($("input:radio[name=\"Wire[account_type]\"]:checked").val() == 1) {
          $("#'.Html::getInputId($this, 'iban_code').'").parent().hide();
          $("#'.Html::getInputId($this, 'account_number').'").parent().show();
          $("#'.Html::getInputId($this, 'swift_code').'").parent().show();
          $("#'.Html::getInputId($this, 'bank_county').'").parent().show();
          $("#'.Html::getInputId($this, 'bank_address').'").parent().show();
        } else { 
          $("#'.Html::getInputId($this, 'iban_code').'").parent().show();
          $("#'.Html::getInputId($this, 'account_number').'").parent().hide();
          if (!$("#'.Html::getInputId($this, 'swift_code').'").parent().hasClass("show")) {
            $("#'.Html::getInputId($this, 'swift_code').'").parent().hide();
          }
          if (!$("#'.Html::getInputId($this, 'bank_county').'").parent().hasClass("show")) {
            $("#'.Html::getInputId($this, 'bank_county').'").parent().hide();
          }
          if (!$("#'.Html::getInputId($this, 'bank_address').'").parent().hasClass("show")) {
            $("#'.Html::getInputId($this, 'bank_address').'").parent().hide();
          }
        }',
      ]),
      'account_number' => $walletForm->maskedTextInput('account_number', $alphanumOnly, $bankOptions),
      'bank_name' => $walletForm->maskedTextInput('bank_name', $alphanumOnly, $options),
      'bank_county' => $walletForm->maskedTextInput('bank_county', $alphanumOnly, $bankOptions),
      'bank_address' => $walletForm->maskedTextInput('bank_address', $alphanumOnly, $bankOptions),
      'swift_code' => $walletForm->maskedTextInput('swift_code', $alphanumOnly, $bankOptions),
      'comment' => $walletForm->maskedTextInput('comment', $alphanumOnly, $options),
      'recipient' => $walletForm->maskedTextInput('recipient', $alphanumOnly, $options),
      'recipient_country' => $walletForm->maskedTextInput('recipient_country', $alphanumOnly, $options),
      'recipient_address' => $walletForm->maskedTextInput('recipient_address', $alphanumOnly, $options),
    ];
  }

  public function getAdminCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    $walletForm = $this->getForm($form);

    $alphanumOnly = [
      'mask' => '[*{*}]',
      'definitions' => ['*' => ['validator' => '^[a-zA-Z0-9, ]*$']], // иначе по-умолчанию * разрешает вводить кириллицу
      'clientOptions' => ['greedy' => false],
    ];

    return [
      'bank_name' => $walletForm->maskedTextInput('bank_name', $alphanumOnly, $options),
      'bank_county' => $walletForm->maskedTextInput('bank_county', $alphanumOnly, $options),
      'bank_address' => $walletForm->maskedTextInput('bank_address', $alphanumOnly, $options),
      'swift_code' => $walletForm->maskedTextInput('swift_code', $alphanumOnly, $options),
      'recipient' => $walletForm->maskedTextInput('recipient', $alphanumOnly, $options),
      'recipient_country' => $walletForm->maskedTextInput('recipient_country', $alphanumOnly, $options),
      'recipient_address' => $walletForm->maskedTextInput('recipient_address', $alphanumOnly, $options),
      'iban_code' => $walletForm->maskedTextInput('iban_code', [
        'mask' => '**** [*{4} ]{1,7}[*{2}]',
        'definitions' => [
          '*' => [ // иначе по-умолчанию * разрешает вводить кириллицу
            'validator' => '^[a-zA-Z0-9]*$',
          ],
        ],
        'options' => [
          'style' => 'text-transform: uppercase;',
        ],
        'clientOptions' => [
          'placeholder' => '',
          'greedy' => false,
          'groupSeparator' => ' ',
          'autoUnmask' => true,
          'removeMaskOnSubmit' => true,
        ],
      ]),
      'account_type' => $walletForm->radioList('account_type', $this->getAccountTypes()),
      'account_number' => $walletForm->maskedTextInput('account_number', $alphanumOnly, $options),
      'comment' => $walletForm->maskedTextInput('comment', $alphanumOnly, $options),
    ];
  }

  public function getFormFields()
  {
    return [
      'bank_name',
      'account_type',
      'account_number',
      'iban_code',
      'swift_code',
      'comment',
      'bank_county',
      'bank_address',
      'recipient',
      'recipient_country',
      'recipient_address',
    ];
  }

  /**
   * @inheritdoc
   */
  protected function handleDetailViewAttribute($attributes)
  {
    ArrayHelper::remove($attributes, '_bankData');
    ArrayHelper::remove($attributes, 'account_type');

    return $attributes;
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'bank_name' => self::translate('attribute-wire_bank_name'),
      'iban_code' => self::translate('attribute-iban_code'),
      'swift_code' => self::translate('attribute-swift_code'),
      'bank_county' => self::translate('attribute-bank_county'),
      'bank_address' => self::translate('attribute-bank_address'),
      'recipient' => self::translate('attribute-recipient'),
      'recipient_country' => self::translate('attribute-recipient_country'),
      'recipient_address' => self::translate('attribute-recipient_address'),
      'account_number' => self::translate('attribute-account_number'),
      'comment' => self::translate('attribute-comment'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributePlaceholders()
  {
    return [
      'bank_name' => self::translate('placeholder-bank_name'),
      'recipient' => self::translate('placeholder-recipient'),
      'recipient_country' => self::translate('placeholder-recipient_country'),
      'recipient_address' => self::translate('placeholder-recipient_address'),
      'iban_code' => self::translate('placeholder-iban_code'),
      'swift_code' => self::translate('placeholder-swift_code'),
      'bank_county' => self::translate('placeholder-bank_county'),
      'bank_address' => self::translate('placeholder-bank_address'),
    ];
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.wire_iban');
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
        $payment->userWallet->getAccountAssoc('bank_name'),
        $payment->userWallet->getAccountAssoc('bank_county'),
        $payment->userWallet->getAccountAssoc('bank_address'),
        $payment->userWallet->getAccountAssoc('recipient'),
        $payment->userWallet->getAccountAssoc('recipient_address'),
        $payment->userWallet->getAccountAssoc('recipient_country'),
        $payment->userWallet->getAccountAssoc('iban_code'),
        $payment->userWallet->getAccountAssoc('swift_code'),
        Yii::$app->formatter->asDecimal($payment->amount, 2),
        Yii::$app->formatter->asDate($payment->created_at),
        $payment->id,
      ],
      self::EXPORT_DELIMITER,
      self::EXPORT_ENCLOSURE,
    ];
  }

  /**
   * Возвращаем массив типов аккаунтов для переключения в ПП
   * @return array
   */
  private function getAccountTypes()
  {
    return [
       self::TYPE_IBAN => Yii::_t('payments.wallets.wire_type_iban'),
      self::TYPE_ACCOUNT_NUMBER => Yii::_t('payments.wallets.wire_type_account_number'),
    ];
  }
}
