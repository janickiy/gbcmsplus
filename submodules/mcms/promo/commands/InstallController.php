<?php

namespace mcms\promo\commands;

use mcms\promo\models\Provider;
use Yii;
use yii\console\Controller;
use yii\helpers\BaseConsole;


class InstallController extends Controller
{

  public function actionIndex()
  {
    $path = BaseConsole::input('Укажите путь до микросервисов: ');

    /** @var \mcms\promo\Module $promo */
    $promo = Yii::$app->getModule('promo');

    $promo->settings->offsetSet($promo::SETTINGS_MAIN_REBILL_PERCENT_FOR_PARTNER, 90);
    $promo->settings->offsetSet($promo::SETTINGS_MAIN_BUYOUT_PERCENT_FOR_PARTNER, 90);

    $promo->settings->offsetSet($promo::SETTINGS_API_HANDLER_CLEAR_CACHE_TYPE, $promo::SETTINGS_API_HANDLER_CLEAR_CACHE_TYPE_CONSOLE);
    $promo->settings->offsetSet($promo::SETTINGS_API_HANDLER_PATH, $path);

    if (BaseConsole::confirm('Настроить провайдера Моблидерс?')) {
      $mobleadersId = BaseConsole::input('Укажите ID пользователя моблидерс: ');
      $mobleadersHash = BaseConsole::input('Укажите hash провайдера моблидерс: ');

      (new Provider([
        'url' => 'http://mobi-tds.com/?hash=' . $mobleadersHash . '&land_id={send_id}&p1={hit_id}&p2=source_id:{source_id}&operator_id={operator_id}&sid={secret}&pb={powered_by}',
        'name' => 'Mobleaders',
        'code' => 'mobleaders1',
        'handler_class_name' => 'Mobleaders',
        'settings' => '{"preland_add_param":"hard_add_preland=1","preland_off_param":"hard_off_preland=1","mobleaders_user_id":"' . $mobleadersId . '","api_url":"https://billing.rgk.tools"}',
        'status' => 1,
        'created_by' => 1,
      ]))->save();
    }

  }

}