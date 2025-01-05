<?php

namespace mcms\payments\models\paysystems;

use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\paysystems\api\BaseApiSettings;
use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\base\Object;
use yii\widgets\ActiveForm;

/**
 * Платежные системы сгруппированные по коду
 * @property $name
 * @property $code
 */
class PaySystemApiGroup extends Object
{
  /** @var string Название */
  private $name;
  /** @var string Код */
  private $code;
  /** @var PaySystemApi[] Платежные системы отсортированные по валюте RUB, USD, EUR */
  public $paysystemApis = [];

  /**
   * Массив настроек ПС.
   * Индексирован по валюте
   * @return BaseApiSettings[]
   */
  public function getSettingObjects()
  {
    // Валюты перечислены для сортировки
    $models = ['rub' => [], 'usd' => [], 'eur' => []];

    foreach ($this->paysystemApis as $paysystemApi) {
      $model = $paysystemApi->getSettingsObject();
      $models[$paysystemApi->currency] = $model;
    }

    return array_filter($models);
  }

  /**
   * Поддерживаемые ПС для получения денег
   * @return Wallet[]
   */
  public function getAvailableRecipients()
  {
    if (!$this->paysystemApis) return [];

    $availableRecipients = [];
    $apiRecipients = reset($this->paysystemApis)->getSettingsObject()->getAvailableRecipients();

    foreach ($apiRecipients as $paysystemCode) {
      $paySystem = Wallet::findOne(['code' => $paysystemCode]);
      $availableRecipients[] = [
        'paysystem' => $paySystem,
        'activeCurrencies' => $paySystem->getCurrencies(),
        'allCurrencies' => $paySystem->getCurrencies(false),
      ];
    }

    return $availableRecipients;
  }

  /**
   * Обновить общие параметры модели
   */
  public function updateData()
  {
    $paysystemApi = reset($this->paysystemApis);
    if (!$paysystemApi) {
      $this->name = $this->code = null;
      return;
    }

    $this->name = $paysystemApi->name;
    $this->code = $paysystemApi->code;
  }

  /**
   * Найти ПС и сгруппировать
   * @param $code
   * @return PaySystemApiGroup
   */
  public static function findGroup($code)
  {
    $group = new PaySystemApiGroup;
    $group->paysystemApis = PaySystemApi::findAll(['code' => $code]);
    $group->updateData();

    return $group;
  }

  /**
   * Заполнить модели настроек данными.
   *
   * TRICKY Сохраняются и валидируются только модели, данные которых были изменены
   * Это сделано, что бы можно было заполнить данные только в одной из форм,
   * иначе придется заполнять всегда все 3 формы из-за required полей
   *
   * @param array $data Данные для заполнения. Например POST
   * @return BaseApiSettings[] Модели настроек, данные которых были изменены
   */
  public function loadSettings($data)
  {
    /** @var BaseApiSettings[] $settingObjectsAll Все настройки */
    /** @var BaseApiSettings[] $settingObjectsChanged Настройки, данные которых были изменены  */
    /** @var string|null $settingsCurrencyToApplyOnGroup Валюта настроек, которые нужно применить на все валюты ПС */
    /** @var BaseApiSettings $settingsToApplyOnGroup Настройки, которые нужно применить на все валюты ПС */

    $settingObjectsAll = $this->getSettingObjects();
    $settingObjectsChanged = [];
    $settingsCurrencyToApplyOnGroup = ArrayHelper::getValue($data, 'settings-to-apply-on-group');
    $settingsToApplyOnGroup = ArrayHelper::getValue($settingObjectsAll, $settingsCurrencyToApplyOnGroup);

    // Заполнение моделей настроек данными из форм
    // Модели, данные которых были изменены, добавятся в $settingObjectsChanged
    if (!$settingsToApplyOnGroup) {
      foreach ($settingObjectsAll as $settings) {
        $settingsDataBeforeLoad = $settings->attributes;
        $settings->load($data);

        // Проверка изменены ли данные модели
        if ($settings->attributes != $settingsDataBeforeLoad) {
          $settingObjectsChanged[] = $settings;
        }
      }
    } else {
      // Заполнение модели, которая была помечена флагом "Применить на все валюты API"
      $settingsToApplyOnGroup->load($data);

      // Применение настроек из $settingsToApplyOnGroup на настройки в остальных валютах
      foreach ($settingObjectsAll as $settings) {
        $settings->attributes = $settingsToApplyOnGroup->attributes;
        $settingObjectsChanged[] = $settings;
      }
    }

    return $settingObjectsChanged;
  }

  /**
   * Валидировать настройки для вывода в форме
   * @param BaseApiSettings[] $settingObjects
   * @return array
   */
  public function validateSettings($settingObjects)
  {
    $errors = [];
    // TRICKY Если использовать validateMultiple, то ошибки будут с дополнительным ключом, который нам не нужен
    foreach ($settingObjects as $settingObject) {
      $errors += ActiveForm::validate($settingObject);
    }

    return $errors;
  }

  /**
   * Сохранить настройки
   * @param BaseApiSettings[] $settingObjects
   * @return bool
   */
  public function saveSettings($settingObjects)
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      foreach ($settingObjects as $settings) {
        if (!$settings->save()) {
          throw new ModelNotSavedException;
        }
      }

      $transaction->commit();
    } catch (\Exception $exception) {
      $transaction->rollBack();

      return false;
    }

    return true;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getCode()
  {
    return $this->code;
  }
}