<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class DatePickerAsset extends AssetBundle
{

  public $sourcePath = '@mcms/partners/assets/resources/basic';

  /**
   * @var array JavaScript files
   */
  public $js = [
    'js/bootstrap-datepicker.js',
  ];

  public $depends = [
    'mcms\partners\assets\BasicAsset',
  ];

}


