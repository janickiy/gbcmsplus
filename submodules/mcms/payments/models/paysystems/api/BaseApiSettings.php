<?php

namespace mcms\payments\models\paysystems\api;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\widgets\FormBuilder;
use mcms\payments\models\paysystems\PaySystemApi;
use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;
use yii\widgets\ActiveForm;

/**
 * Базовый класс для моделей содержащих настройки платежных систем для доступа к API.
 * В этом классе должны быть описаны параметры требуемые для работы с API. Например: WMID, ключ, поддерживаемые валюты...
 *
 * Название классов должно быть в формате {PaymentSystemName}Api.
 * Например: WebmoneyApi.
 * Если название будет иметь другой формат, класс PaymentSystemApi не сможет получить объект.
 */
abstract class BaseApiSettings extends Model
{
  /**
   * @var PaySystemApi
   * TRICKY Устанавливается автоматически
   * TRICKY Свойство приватное и управляется геттером/сеттером что бы не попадало в список свойств модели
   */
  private $paysystemApi;

  /**
   * @inheritdoc
   */
  public function formName()
  {
    return $this->paysystemApi
      ? parent::formName() . '-' . $this->paysystemApi->currency
      : parent::formName();
  }


  /**
   * Поддерживаемые ПС для отправки денег
   * @return string[] [paysystem_code]
   */
  abstract public function getAvailableRecipients();

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return [[array_keys($this->getAttributes()), 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process']];
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return json_encode($this->getAttributes());
  }

  /**
   * @return array
   */
  public function getFormFields()
  {
    return array_keys($this->getAttributes());
  }

  public function getAdminFormFields()
  {
    return $this->getFormFields();
  }

  /**
   * @param ActiveForm $form
   * @param array $options
   * @param string $submitButtonSelector
   * @return array
   */
  public function getCustomFields($form, $options = [], $submitButtonSelector = '[type="submit"]')
  {
    return [];
  }

  /**
   * @param ActiveForm $form
   * @param array $options
   * @return array
   */
  public function getAdminCustomFields($form, $options = [])
  {
    return $this->getCustomFields($form, $options);
  }

  private $senderForm;

  public function getForm(ActiveForm $form)
  {
    if (!$this->senderForm) {
      $this->senderForm = new FormBuilder([
        'form' => $form,
        'model' => $this,
      ]);
    }
    return $this->senderForm;
  }

  /**
   * @param $attribute
   * @return string
   */
  public function attributePlaceholder($attribute)
  {
    return ArrayHelper::getValue($this->attributePlaceholders(), $attribute)
      ?: ArrayHelper::getValue($this->attributePlaceholders(), [$this->getScenario(), $attribute]);
  }

  /**
   * @return array
   */
  public function attributePlaceholders()
  {
    return [];
  }

  public static function getFilepath($formname, $attribute)
  {
    return '/payment-system-api/files/' . $formname . '/' . $attribute . '/';
  }

  public function getAccessTokenUrl()
  {
    return null;
  }

  /**
   * @return PaySystemApi
   */
  public function getPaysystemApi()
  {
    return $this->paysystemApi;
  }

  /**
   * @param PaySystemApi $paysystemApi
   */
  public function setPaysystemApi($paysystemApi)
  {
    $this->paysystemApi = $paysystemApi;
  }

  /**
   * Сохранить настройки
   * @return bool
   * @throws ServerErrorHttpException
   */
  public function save()
  {
    if (!$this->paysystemApi) throw new ServerErrorHttpException('Невозможно сохранить настройки. Не указан API');

    $this->paysystemApi->setSettings($this);

    return $this->paysystemApi->save();
  }
}