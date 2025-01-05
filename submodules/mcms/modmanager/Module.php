<?php

namespace mcms\modmanager;

use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use mcms\common\translate\SystemLanguage;
use mcms\modmanager\models\Module as ModuleModel;

use yii\helpers\ArrayHelper;

class Module extends \mcms\common\module\Module implements BootstrapInterface
{

  public $class;

  // TODO вызывать это событие при установке удалении модуля
  const EVENT_MODULE_CHANGED = 'event.module.changed';

  const ADMIN_APP_ID = 'app-backend';

  public function init()
  {
    parent::init();
    if (Yii::$app instanceof ConsoleApplication) {
      $this->controllerNamespace = 'mcms\modmanager\commands';
    }

    // подключаем конфиги самого модуля
    // http://www.yiiframework.com/doc-2.0/guide-structure-modules.html#module-classes
    $config = require(__DIR__ . '/config/main.php');
    $config['id'] = $this->id;

    Yii::configure($this, $config);
  }

  /**
   * @inheritdoc
   * @throws \yii\base\InvalidConfigException
   */
  public function bootstrap($app)
  {
    // tricky: перез установкой языка необходимо засетить все модули, чтобы в них подгрузились корректные переводы
    $this->setModulesConfig($app);

    // определяем язык пользователя
    Yii::$app->language = SystemLanguage::getClientLanguage();

    /** @var \mcms\partners\Module $partnersModule */
    /** @var \mcms\notifications\Module $notificationModule */
    if (defined('YII_ENV') && YII_ENV === 'dev') {
      $target = Yii::$app->params['dev_log'];
      Yii::$app->log->targets[] = Yii::createObject($target);
    }
  }

  public function setModulesConfig($app)
  {
    //todo порефакторить и убрать костыль
    if (!$tableSchema = Yii::$app->db->schema->getTableSchema(ModuleModel::tableName())) {
      return false;
    }
    $moduleModel = new ModuleModel();
    $enabledModules = $moduleModel->findEnabled();


    foreach ($enabledModules as $moduleId => $moduleName) {

      $moduleConfig = $moduleModel->getModuleById($moduleId);
      $class = $moduleConfig['class'];

      $app->setModule($moduleId, $moduleConfig);

      if (ArrayHelper::getValue($moduleConfig, 'preload', false)) {

        $component = $app->getModule($moduleId);

        if ($component instanceof BootstrapInterface) {
          Yii::trace('Bootstrap with ' . get_class($component) . '::bootstrap()', __METHOD__);
          $component->bootstrap($app);
        } else {
          Yii::trace('Bootstrap "' . $class . '" with ' . get_class($component), __METHOD__);
        }
      }

    }

    if (Yii::$app instanceof ConsoleApplication) {
      $partnersModule = Yii::$app->getModule('partners');
      if ($partnersModule && $partnersModule->settings->isTablesReady()) {
        Yii::$app->urlManager->setBaseUrl($partnersModule->getServerName());
        Yii::$app->urlManager->setHostInfo($partnersModule->getServerName());
      }
    }
  }
}
