<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class SourceFormAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/default';

  public $css = [];
  public $js = [
    'js/source-form.js'
  ];

  public $depends = [
    'mcms\partners\assets\AppAsset',
  ];
}
