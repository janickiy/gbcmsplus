<?php

namespace mcms\notifications\components\widgets\notifyHeader;

use mcms\notifications\models\search\BrowserNotificationSearch;
use Yii;
use yii\helpers\ArrayHelper;


abstract class AbstractNotifyHeaderWidget extends \yii\base\Widget
{
  public $options;
  public $showAllUrl;
  public $clearUrl;
  public $readAllUrl;
  public $settingsUrl;

  public function init()
  {
    $this->showAllUrl = ArrayHelper::getValue($this->options, 'show_all_url');
    $this->clearUrl = ArrayHelper::getValue($this->options, 'clear_url');
    $this->readAllUrl = ArrayHelper::getValue($this->options, 'read_all_url');
    $this->settingsUrl = ArrayHelper::getValue($this->options, 'settings_url');
    parent::init();
  }

  abstract function registerAsset();

  /**
   * @inheritdoc
   */
  public function run()
  {
    $this->registerAsset();

    $modulesWithEvents = Yii::$app->getModule('modmanager')
      ->api('modulesWithEvents', ['useDbId'])
      ->setResultTypeArray()
      ->getResult()
    ;

    $modulesDbId = ArrayHelper::map($modulesWithEvents, 'dbId', 'dbId');

    $params = [
      'user_id' => Yii::$app->user->id,
      'is_hidden' => 0,
      'categoriesId' => $modulesDbId,
    ];

    $model = new BrowserNotificationSearch($params);

    $notificationsDataProvider = $model->search($params);
    $notificationsDataProvider->setTotalCount(BrowserNotificationSearch::getTotalCount(Yii::$app->user->id, $modulesDbId));

    return $this->render('notifications-list', [
      'notificationsDataProvider' => $notificationsDataProvider,
      'unViewedCount' => BrowserNotificationSearch::getUnviewedCount(Yii::$app->user->id, $modulesDbId),
      'modules' => ArrayHelper::map($modulesWithEvents, 'dbId', 'id'),
      'clearUrl' => $this->clearUrl,
      'readAllUrl' => $this->readAllUrl,
      'showAllUrl' => $this->showAllUrl,
      'settingsUrl' => $this->settingsUrl,
    ]);
  }

}