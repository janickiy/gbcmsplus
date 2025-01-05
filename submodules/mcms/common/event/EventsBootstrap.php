<?php

namespace mcms\common\event;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\db\ActiveRecord;
use yii\base\Event as YiiEvent;

/**
 * Навешиваем события при старте приложения
 */
class EventsBootstrap implements BootstrapInterface
{
  /**
   * Bootstrap method to be called during application bootstrap stage.
   * @param Application $app the application currently running
   */
  public function bootstrap($app)
  {
    // В прогоне тестов приложение поднимается многократно на каждом тестовом методе. И если навешивать события через
    // YiiEvent::on то они будут каждый раз навешиваться и потом выполняться столько же раз.
    // Из-за этого каждый следующий тест выполняется медленнее предыдущего, мы с этим столкнулись.
    // Чтобы исключить это сделали глобальную переменную EVENTS_BOOTSTRAP_ATTACHED
    if (!defined('EVENTS_BOOTSTRAP_ATTACHED')) {
      $filter = function ($event) {
        \rgk\utils\helpers\FilterHelper::filter($event->sender);
      };

      YiiEvent::on(ActiveRecord::class, ActiveRecord::EVENT_BEFORE_UPDATE, $filter);
      YiiEvent::on(ActiveRecord::class, ActiveRecord::EVENT_BEFORE_INSERT, $filter);

      Yii::$container->set(\mcms\statistic\components\mainStat\BaseFetch::class, [
        'class' => \mcms\statistic\components\mainStat\mysql\Fetch::class
      ]);

      Yii::$container->set(\mcms\statistic\components\newStat\BaseFetch::class, [
        'class' => \mcms\statistic\components\newStat\mysql\Fetch::class
      ]);

      // Виджет ожидает Yii::$app->language в формате 'en-US', а у нас хранится просто как 'en'
      // Ниже костыль исправляющий баг вызванный конфликтом описанным выше.
      // Для en языка не нужно подтягивать дополнительные переводы, так как они не нужны и их нет
      YiiEvent::on(\vova07\imperavi\Widget::class, \vova07\imperavi\Widget::EVENT_INIT, function ($event) {
        if ($event->sender->settings['lang'] === 'en') {
          unset($event->sender->settings['lang']);
        }
      });
      define('EVENTS_BOOTSTRAP_ATTACHED', true);
    }

  }
}
