<?php

namespace mcms\notifications\components\widgets\notifyHeader\basic;


class NotifyHeaderAsset extends \yii\web\AssetBundle
{

  public $sourcePath = '@mcms/notifications/components/widgets/notifyHeader/basic/assets';

  public $js = [
    'notify.js',
  ];

  public $depends = [
    '\yii\web\YiiAsset',
   '\yii\web\JqueryAsset',
  ];
}