<?php

namespace mcms\common\multilang\widgets\input;

use Yii;
use yii\base\Model;
use yii\base\Widget;

class FileInputWidget extends Widget
{

  /** @var Model */
  public $model;

  public $attribute;

  public $options = [];

  public $pluginOptions = [];

  /**
   * Массив preview изображений, в качестве ключей - название языка.
   * @var array */
  public $previews = [];

  /**
   * Массив конфига preview изображений, в качестве ключей - название языка.
   * @var array */
  public $imagesDelete = [];

  public $pluginEvents = [];

  public function init()
  {
    $this->setId(uniqid());

    parent::init();
  }


  public function run()
  {
    return $this->render('fileinput', [
      'model' => $this->model,
      'attribute' => $this->attribute,
      'options' => $this->options,
      'pluginOptions' => $this->pluginOptions,
      'languages' => self::getLanguages(),
      'previews' => $this->previews,
      'imagesDelete' => $this->imagesDelete,
      'pluginEvents' => $this->pluginEvents
    ]);
  }

  public static function getLanguages()
  {
    return Yii::$app->params['languages'];
  }

}