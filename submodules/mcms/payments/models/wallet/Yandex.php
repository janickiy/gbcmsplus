<?php

namespace mcms\payments\models\wallet;


use mcms\payments\models\paysystems\PaySystemApi;
use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\validators\Yandex as YandexValidator;
use YandexMoney\API;
use Yii;

/**
 * Class Yandex
 * @package mcms\payments\models\wallet
 */
class Yandex extends AbstractWallet
{
  const INVALID_CODES = [
    'error' => true,
    'refused' => true,
    'hold_for_pickup' => true,
    'success' => false,
  ];

  const ERROR_MESSAGES = [
    'illegal_params' => 'Обязательные параметры платежа отсутствуют или имеют недопустимые значения.',
    'illegal_param_label' => 'Недопустимое значение параметра label.',
    'illegal_param_to' => 'Недопустимое значение параметра.',
    'illegal_param_amount' => 'Недопустимое значение параметра amount.',
    'illegal_param_amount_due' => 'Недопустимое значение параметра amount_due.',
    'illegal_param_comment' => 'Недопустимое значение параметра comment.',
    'illegal_param_message' => 'Недопустимое значение параметра message.',
    'illegal_param_expire_period' => 'Недопустимое значение параметра expire_period.',
    'not_enough_funds' => 'На счете плательщика недостаточно средств. Необходимо пополнить счет и провести новый платеж.',
    'payment_refused' => 'Магазин отказал в приеме платежа (например, пользователь попробовал заплатить за товар, которого нет в магазине).',
    'payee_not_found' => 'Получатель перевода не найден. Указанный счет не существует или указан номер телефона/email, не связанный со счетом пользователя или получателя платежа.',
    'authorization_reject' => 'В авторизации платежа отказано. Возможные причины:
 транзакция с текущими параметрами запрещена для данного пользователя;
 пользователь не принял Соглашение об использовании сервиса Яндекс.Деньги.',
    'limit_exceeded' => 'Превышен один из лимитов на операции:
 на сумму операции для выданного токена авторизации;
 сумму операции за период времени для выданного токена авторизации;
 ограничений Яндекс.Денег для различных видов операций.',
    'account_blocked' => 'Счет пользователя заблокирован.',
    'ext_action_required' => 'В настоящее время данный тип платежа не может быть проведен. Для получения возможности проведения таких платежей пользователю необходимо перейти на страницу по адресу ext_action_uri и следовать инструкции на данной странице. Это могут быть следующие действия:
 ввести идентификационные данные,
 принять оферту,
 выполнить иные действия согласно инструкции.',
    'technical_error' => 'Не удалось проверить существование кошелька',
  ];

  const ERROR_CODE = 'payee_not_found';

  public $wallet;

  //fixme убрать хардкод
  public static $currency = ['rub'];

  protected static $isLocalityRu = true;

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
      ['wallet', 'string', 'max' => 255],
      ['wallet', 'validateExistence'],
      [['wallet'], YandexValidator::class, 'message' => self::translate('error-wallet')],
    ]);
  }

  /**
   * @inheritdoc
   */
  protected function validateExistenceInternal()
  {
    // TODO Рефакторинг: @see \mcms\payments\models\wallet\AbstractWallet::validateExistenceInternal()

    /**
     * @var ApiMgmpSender $sender
     */
    $sender = Yii::createObject('mcms\payments\components\mgmp\send\MgmpSenderInterface');
    $result = $sender->checkYandexWallet($this->wallet);

    if (
      is_array($result) &&
      !empty(self::INVALID_CODES[$result['status']]) &&
      $result['error'] === self::ERROR_CODE
    ) {
      $this->addError('wallet', Yii::_t('payments.yandex.' . self::ERROR_CODE));
      return false;
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public static function getName($language = null)
  {
    return Yii::_t('payments.wallets.yandex');
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return ['wallet' => self::translate('attribute-wallet')];
  }

  /**
   * @inheritDoc
   */
  public function attributePlaceholders()
  {
    return ['wallet' => self::translate('attribute-placeholder-yandex-waller')];
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
