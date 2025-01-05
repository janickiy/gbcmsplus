<?php

namespace mcms\common\multilang\widgets\input;


class InputWidgetAsset extends \yii\web\AssetBundle
{
  public $sourcePath = '@mcms/common/multilang/widgets/input/assets';

  public $js = [
    'input.js',
  ];

  public $depends = [
   '\yii\web\YiiAsset',
   '\yii\web\JqueryAsset',
  ];
}