<?php

namespace mcms\payments\models\wallet;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\mgmp\send\ApiMgmpSender;
use mcms\payments\models\UserPayment;
use rgk\payprocess\components\utils\EpaymentsAPI;
use Yii;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

class Card extends AbstractWallet
{
  public $card_number;
  public $bank_name;
  public $cardholder_name;

  /** @var mixed TODO МЕГАКОСТЫЛЬ, хранит данные по реквизитам, чтобы на фронте прочитать эти данные */
  public $_cardData;

  //fixme убрать хардкод
  public static $currency = ['rub', 'usd', 'eur'];

  /**
   * @inheritdoc
   */
  public function getUniqueValue()
  {
    return $this->card_number;
  }

  /**
   * Проверить существование кошелька
   * TRICKY Если ПС не настроена или недоступна, нужно возвращать true, что бы управление кошельками не зависило от внешних сервисов!
   * TRICKY Метод должен возвращать true или false, все остальные значения считаются true
   * @see validateExistence()
   * @return bool
   * @throws \yii\base\InvalidConfigException
   */
  protected function validateExistenceInternal()
  {
    // TODO Рефакторинг: @see \mcms\payments\models\wallet\AbstractWallet::validateExistenceInternal()
    /**
     * @var PaySystemApi $paysystem
     */
    $paysystem = PaySystemApi::find()->andWhere([
      'code' => 'epayments',
      'currency' => 'usd',
    ])->one();
    if (!$paysystem || !$paysystem->isActive()) {
      Yii::warning('Не удалось определить API для валидации');
      return true;
    }
    $settings = $paysystem->getSettingsAsArray();

    /**
     * @var $api EpaymentsAPI
     */
    $api = Yii::createObject(EpaymentsAPI::class, [
      $settings['partnerId'],
      $settings['partnerSecret'],
      $settings['payPass'],
    ]);

    $result = $api->getExternalCard($this->card_number);
    if (is_array($result) && ArrayHelper::getValue($result, 'cardCurrencies')) {
      $currencies = ArrayHelper::getValue($result, 'cardCurrencies');
      $currencies = array_map(function ($cur) {
        return strtolower($cur);
      }, $currencies);
      if (!Yii::$app->request->post('submit')) {
        $this->addErrors(['_cardData' => [
          'kostyl_key' => [ // TODO КОСТЫЛЬ иначе ключи внутри массива стираются :(
            'currencies' => $currencies,
          ]
        ]]);
      }
    }

    // Выводим ошибку, если кошелек не валиден
    // 0 - это успех
    // отрицательные - это системные ошибки
    // положительные - пользовательские ошибки (но это не точно)
    $errors = ArrayHelper::getValue($result, 'errorMsgs', []);
    $error = reset($errors);
    $errorCode = ArrayHelper::getValue($result, 'errorCode', 0);

    if ($errorCode > 0) {
      $errorMessage = $errorCode == 25011 ? Yii::_t('payments.payments.card_invalid') : $error;
      $this->addError('card_number', $errorMessage);
      return false;
    }

    return true;
  }


    /**
   * @inheritdoc
   */
  public function getUniqueValueProtected()
  {
    return preg_replace('/(\d{2})\d+(\d{4})/', '$1**$2', $this->getUniqueValue());
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['card_number', 'cardholder_name'], 'required'],
      [['bank_name', 'cardholder_name'], 'string', 'max' => 255],
      ['cardholder_name', 'filter', 'filter' => 'trim'],
      ['cardholder_name', 'match', 'skipOnEmpty' => false, 'pattern' => '/^[a-z\s]{0,}$/i', 'message' => self::translate('message-cardholder_name')],
      [['card_number'], 'filter', 'filter' => function ($value) {
        return str_replace(' ', '', $value);
      }],
      ['card_number', 'match', 'skipOnEmpty' => false, 'pattern' => '/^\d{16}$/', 'message' => self::translate('message-card_number')],
      ['card_number', 'validateExistence'],
    ]);
  }

  public function getFormFields()
  {
    return [
      'bank_name', 'card_number', 'cardholder_name'
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return [
      'bank_name' => self::translate('attribute-bank_name'),
      'card_number' => self::translate('attribute-card_number'),
      'cardholder_name' => self::translate('attribute-name-lastname'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributePlaceholders()
  {
    return [
      'bank_name' => self::translate('placeholder-bank_name'),
      'card_number' => 'xxxx xxxx xxxx xxxx',
      'cardholder_name' => self::translate('placeholder-recipient'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getCustomFields(ActiveForm $form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    return [
      'card_number' => $this->getForm($form)->maskedTextInput('card_number', [
        'mask' => '9{4} 9{4} 9{4} 9{4}',
        'clientOptions' => [
          'oncomplete' => new JsExpression('function(){$(this).trigger("blur.yiiActiveForm")}')
        ]
      ]),
      'bank_name' => null
    ];
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.card', [], $language);
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
        $payment->userWallet->getAccountAssoc('card_number'),
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
  protected function handleDetailViewAttribute($attributes)
  {
    ArrayHelper::remove($attributes, '_cardData');
    ArrayHelper::remove($attributes, 'bank_name');

    return parent::handleDetailViewAttribute($attributes);
  }

}
