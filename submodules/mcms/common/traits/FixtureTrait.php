<?php

namespace mcms\common\traits;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Class FixtureTrait
 * @package mcms\common\traits
 */
trait FixtureTrait
{
  function __construct()
  {
    $depends = $this->depends;
    $convertedDepends = [];

    foreach ($depends as $depend) {
      list($module, $fixtureCode) = StringHelper::explode($depend, '.');

      $convertedDepends[] = ArrayHelper::getValue(Yii::$app->getModule($module)->fixtures, $fixtureCode);
    }

    $this->depends = $convertedDepends;

    parent::__construct();
  }
}