<?php

namespace mcms\common\translate;

use Yii;
use yii\web\Cookie;
use yii\console\Application as ConsoleApplication;

class SystemLanguage
{
  const DEBUG_MODE_SOURCE = 'src';

  /** @var string */
  public $current;

  /** @var array */
  public $all;

  /** @var string|bool|null Режим дебага переводов */
  protected static $debugMode = null;


  public function __construct()
  {
    $this->current = Yii::$app->language;
    $this->all = Yii::$app->params['languages'];
  }

  /**
   * Определение статуса режима кодов вместо переводов
   *
   * Режим дебага включается путем передачи GET-параметра debugLang
   * debugLang=true|1 - включить режим отображения кодов переводов
   * debugLang=ru|en - принудительно сменить язык
   * debugLang=false|0 - отключить режим дебага
   * Статус режима дебага сохраняется в куки и очищается после закрытия браузера
   *
   * @return bool|string
   * static::DEBUG_MODE_SOURCE - режим отображение кодов вместо переводов
   * ru|en - режим принудительной смены языка
   * false - режим дебага отключен
   */
  public static function getDebugMode()
  {
    if (Yii::$app instanceof ConsoleApplication) return false;
    if (static::$debugMode !== null) return static::$debugMode;

    $debugMode = false;
    $debugModeRequest = Yii::$app->request->get('debugLang');

    if ($debugModeRequest !== null) {
      // Определение режима дебага из запроса
      $debugModeRaw = $debugModeRequest;
    } else {
      // Определение режима дебага из хранилища
      $debugModeRaw = Yii::$app->request->cookies->getValue('debugLang', false);
    }

    // Нормализация значения
    if ($debugModeRaw === 'true' || $debugModeRaw === '1' || $debugModeRaw == static::DEBUG_MODE_SOURCE) {
      $debugMode = static::DEBUG_MODE_SOURCE;
    } else if ((new static)->langExists($debugModeRaw)) {
      $debugMode = $debugModeRaw;
    }

    // Сохранение в хранилище
    Yii::$app->response->cookies->add(new Cookie(['name' => 'debugLang', 'value' => $debugMode]));
    static::$debugMode = $debugMode;

    return $debugMode;
  }

  /**
   * Определение принудительно подключаемого языка для дебага
   */
  public static function getDebugLang()
  {
    $debugMode = static::getDebugMode();

    return $debugMode && (new static)->langExists($debugMode) ? $debugMode : null;
  }

  public function getOther()
  {
    $languages = array_flip($this->all);
    if (array_key_exists($this->current, $languages)) {
      unset($languages[$this->current]);
    }

    return array_flip($languages);
  }

  public function getCurrent()
  {
    return $this->current;
  }

  public function langExists($language)
  {
    return $language && array_key_exists($language, array_flip($this->all));
  }

  public function setLang($language)
  {
    if (!$this->langExists($language)) throw new InvalidLanguageException;

    if (!Yii::$app->user->isGuest) {
      $this->setLangModel(Yii::$app->user->identity, $language);
    } else {
      $this->setLangCookie($language);
    }
  }

  private function setLangModel($user, $lang)
  {
    if (!$user instanceof Yii::$app->user->identityClass) {
      return false;
    }

    $user->language = $lang;
    return $user->save();
  }

  private function setLangCookie($lang)
  {
    Yii::$app->response->cookies->add(new Cookie([
      'name' => 'lang',
      'value' => $lang,
      'expire' => 864000
    ]));
  }

  static function getClientLanguage()
  {
    // для консольных комманд
    if (Yii::$app instanceof ConsoleApplication) {
      return Yii::$app->language;
    }

    $debugLang = static::getDebugLang();
    if ($debugLang) return $debugLang;

    if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->language)) {
      return Yii::$app->user->identity->language;
    }

    $cookieLang = Yii::$app->request->cookies->get('lang');
    return $cookieLang ? $cookieLang->value : Yii::$app->language;
  }
}