<?php

namespace mcms\common\widget\alert;


use yii\web\AssetBundle;

class AlertAsset extends AssetBundle
{
  public $sourcePath = '@mcms/common/widget/alert/assets';

  public $js = [
    'js/SmartNotification.js'
  ];

  public $depends = [
    '\admin\assets\AppAsset',
  ];
}