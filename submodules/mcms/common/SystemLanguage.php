<?php

namespace mcms\common;

use mcms\common\helpers\ArrayHelper;
use rgk\utils\helpers\Lang;
use Yii;
use mcms\common\exceptions\InvalidLanguageException;
use yii\web\Cookie;

class SystemLanguage
{

  /** @var string  */
  public $current;

  /** @var array */
  public $all;


  public function __construct()
  {
    $this->current = Yii::$app->language;
    $this->all = Yii::$app->params['languages'];
  }

  public function getOther()
  {
    $languages = array_flip($this->all);
    if (array_key_exists($this->current, $languages)) unset($languages[$this->current]);

    return array_flip($languages);
  }

  public function getCurrent()
  {
    return $this->current;
  }

  public function setLang($language, $onlyCookie = false)
  {

    $language = $language ? : current($this->all);

    if (!array_key_exists($language, array_flip($this->all))) throw new InvalidLanguageException;

    if(!$onlyCookie) $this->setLangModel(Yii::$app->user->identity, $language);
    Yii::$app->language = $language;
    $this->setLangCookie($language);
  }

  private function setLangModel($user, $lang)
  {
    if (!$user instanceof Yii::$app->user->identityClass) return false;

    $user->language = $lang;
    return $user->save();
  }
  private function setLangCookie($lang)
  {
    Yii::$app->response->cookies->add(new Cookie([
      'name' => 'lang',
      'value' => $lang,
      'expire' => time() + 864000
    ]));
  }

  static function getClientLanguage()
  {
    /** @var \mcms\user\models\User $user */
    $user = Yii::$app->user->identity;

    if ($user && !empty($user->language)) {
      return $user->language;
    }

    return ArrayHelper::getValue(Yii::$app->request->cookies->get('lang'), 'value', Lang::getLang());
  }

  public static function getLanguangesDropDownArray()
  {
    if (!ArrayHelper::getValue(Yii::$app->params, 'languages')) return [];
    $languagesArray = [];
    foreach (Yii::$app->params['languages'] as $language) {
      $languagesArray[$language] = Yii::_t("commonMsg.lang.{$language}");
    }
    return $languagesArray;
  }
}