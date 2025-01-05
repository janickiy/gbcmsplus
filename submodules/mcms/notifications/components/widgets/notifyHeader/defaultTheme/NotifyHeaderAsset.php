<?php

namespace mcms\notifications\components\widgets\notifyHeader\defaultTheme;


class NotifyHeaderAsset extends \yii\web\AssetBundle
{

  public $sourcePath = '@mcms/notifications/components/widgets/notifyHeader/defaultTheme/assets';

  public $js = [
    'notify.js',
  ];

  public $css = [
    'style.css'
  ];

  public $depends = [
    '\yii\web\YiiAsset',
   '\yii\web\JqueryAsset',
  ];
}