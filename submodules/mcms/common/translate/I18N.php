<?php

namespace mcms\common\translate;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use mcms\modmanager\models\Module as ModuleModel;

class I18N extends \yii\i18n\I18N
{
  const LOCAL_SUFFIX = '-local';

  public function init()
  {
    parent::init();

    $modules = \Yii::$app->getModules();

    foreach ($modules as $module) {

      $moduleId = ArrayHelper::getValue($module, 'id');
      $key = $moduleId . '.*';

      if ($module instanceof Module && (!$module->canGetProperty('messages') || !$messages = $module->messages)) continue;
      if (isset($this->translations[$key]) || !($messages = ArrayHelper::getValue($module, 'messages'))) continue;

      $this->translations[$key] = [
        'class' => 'yii\i18n\PhpMessageSource',
        'sourceLanguage' => \Yii::$app->sourceLanguage,
        'basePath' => $messages
      ];

    }

  }

  public function translate($category, $message, $params, $language)
  {
    $messageSource = $this->_getMessageSource($category);
    if ($messageSource === null && strpos($category, '.') === false && Yii::$app->controller !== null) {
      $messageSource = $this->_getMessageSource(Yii::$app->controller->module->id . '.' . $category);
    }
    if ($messageSource === null) {
      return $this->format($message, $params, $language);
    }
    if (($pos = strpos($category, '.')) !== false) {
      $category = substr($category, $pos + 1);
    }

    $localFilePath = Yii::getAlias($messageSource->basePath . '/' . $language . '/' . $category . self::LOCAL_SUFFIX . '.php');

    $localTranslation = file_exists($localFilePath)
      ? $messageSource->translate($category . self::LOCAL_SUFFIX, $message, $language)
      : false
    ;

    $translation = $localTranslation ? $localTranslation : $messageSource->translate($category, $message, $language);
    if ($translation === false) {
      return $this->format($message, $params, $messageSource->sourceLanguage);
    } else {
      return $this->format($translation, $params, $language);
    }
  }

  private function _getMessageSource($category)
  {
    try {
      return $this->getMessageSource($category);
    } catch (InvalidConfigException $e) {
      return null;
    }
  }

}
