<?php

namespace mcms\partners\models;

use Yii;
use yii\base\Model;
use yii\helpers\Html;

class NotificationForm extends Model
{
  public $dateBegin;
  public $dateEnd;
  public $categoryId;
  public $typeId;
  public $dateperiod;
  public $modules;

  public function init()
  {
    $this->dateBegin = date('Y-m-d', strtotime('-7 days'));
    $this->dateEnd = date('Y-m-d');;
    $this->typeId = 'all';
    $this->dateperiod = 'week';
    $this->modules = ['statistic', 'promo', 'payments', 'pages', 'support', 'users'];
  }

  public function rules()
  {
    return [
      [['dateBegin', 'dateEnd'], 'required'],
      [['typeId', 'dateperiod'], 'string'],
      [['categoryId'], 'integer']
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'dateBegin' => Yii::_t('partners.main.from'),
      'dateEnd' => Yii::_t('partners.main.to'),

    ];
  }

  public function load($data, $formName = null)
  {
    $result = parent::load($data, $formName);
    if (count($this->categoryId) == 0) {
      $this->categoryId = array_keys($this->getModules());
    }

    $this->dateperiod = '';
    if($this->dateBegin == $this->dateEnd && $this->dateBegin == date('d.m.Y')) {
      $this->dateperiod = 'today';
    }
    if($this->dateBegin == $this->dateEnd && $this->dateBegin == date('d.m.Y',  strtotime('-1day'))) {
      $this->dateperiod = 'yesterday';
    }
    if($this->dateBegin == date('d.m.Y', strtotime('-1week')) && $this->dateEnd == date('d.m.Y')) {
      $this->dateperiod = 'week';
    }
    if($this->dateBegin == date('d.m.Y', strtotime('-1month')) && $this->dateEnd == date('d.m.Y')) {
      $this->dateperiod = 'month';
    }
    return $result;
  }


  public function getCategories()
  {
    $modulesEventList = [];
    $enabledModules = $this->getModules();

    foreach ($enabledModules as $id => $name) {
      $modulesEventList[$id] = Html::tag('i', '', [
        'class' => 'icon-' . $name,
        'title' => Yii::_t('partners.labels.module-' . $name)
      ]);
    }

    return $modulesEventList;
  }

  public function getModules()
  {
    $modulesEventList = [];

    foreach ($this->modules as $moduleId) {

      $moduleApiResult = Yii::$app->getModule('modmanager')
        ->api('moduleById', ['moduleId' => $moduleId])
        ->getResult()
      ;
      if($moduleApiResult) $modulesEventList[$moduleApiResult->id] = $moduleId;
    }

    return $modulesEventList;
  }

}