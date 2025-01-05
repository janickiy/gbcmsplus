<?php

namespace mcms\promo\models\form;

use mcms\promo\components\ProviderSyncInterface;
use mcms\promo\models\Provider;
use UnexpectedValueException;
use yii\base\Model;

/**
 * Тестилка получения инфы от провайдеров через их апи
 */
class ProviderTestForm extends Model
{
  const TYPE_COUNTRY = 'country';
  const TYPE_OPERATOR = 'operator';
  const TYPE_LANDING = 'landing';

  /** @var int */
  public $providerId;
  /** @var string страна|ленд|оператор */
  public $type;
  /** @var bool проверять ли при синке время апдейта */
  public $checkUpdateTime;

  /** @var string тут хранится ответ в виде строки */
  private $response;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['providerId', 'type'], 'required'],
      ['type', 'in', 'range' => array_keys(self::getTypesList())],
      ['providerId', 'exist', 'skipOnError' => true, 'targetClass' => Provider::class, 'targetAttribute' => ['providerId' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'providerId' => 'Provider'
    ];
  }

  /**
   * @return mixed
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * отправка запроса. Результат присвоит в свойство response
   * @return bool
   */
  public function sendRequest()
  {
    if (!$this->validate()) {
      return false;
    }

    $provider = Provider::findOne((int) $this->providerId);

    $handlerClass = 'mcms\promo\components\handlers\\' . $provider->handler_class_name;

    if (!$provider->handler_class_name || !class_exists($handlerClass)) {
      throw new UnexpectedValueException("Error: Provider handler class name not found! $handlerClass");
    }

    /* @var $handler ProviderSyncInterface */
    $handler = new $handlerClass($provider);

    if (!$handler->auth()) {
      $this->response = 'Auth failed';
      return false;
    }

    $this->response = null;

    if ($this->type === self::TYPE_COUNTRY) {
      $this->response = $handler->getCountriesFromApi();
    }

    if ($this->type === self::TYPE_OPERATOR) {
      $this->response = $handler->getOperatorsFromApi();
    }

    if ($this->type === self::TYPE_LANDING) {
      $this->response = $handler->getLandingsFromApi();
    }

    return true;
  }

  /**
   * Список типов
   * @return string[]
   */
  public static function getTypesList()
  {
    return [
      self::TYPE_COUNTRY => 'Countries',
      self::TYPE_OPERATOR => 'Operators',
      self::TYPE_LANDING => 'Landings',
    ];
  }
}
