<?php

namespace mcms\partners\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\BaseConsole;


class InstallController extends Controller
{

  public function actionIndex()
  {
    $projectName = BaseConsole::input('Укажите название проекта: ');
    $url = BaseConsole::input('Укажите URL проекта: ');
    $copyright = BaseConsole::input('Укажите копирайт: ');

    /** @var \mcms\partners\Module $partners */
    $partners = Yii::$app->getModule('partners');

    $partners->settings->offsetSet($partners::SETTINGS_PROJECT_NAME, $projectName);
    $partners->settings->offsetSet($partners::SETTINGS_SERVER_NAME, $url);
    $partners->settings->offsetSet($partners::SETTINGS_FOOTER_COPYRIGHT, $copyright);
  }

}