<?php

namespace mcms\common\multilang\widgets\multilangform;


class MultiLangFormAsset extends \yii\web\AssetBundle
{
  public $sourcePath = '@mcms/common/multilang/widgets/multilangform/assets';

  public $js = [
    'form.js',
  ];

  public $depends = [
   '\yii\web\YiiAsset',
   '\yii\web\JqueryAsset',
  ];
}