<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class ProfileAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/default';

  public $css = [];
  public $js = [
    'js/profile.js',
  ];

  public $depends = [
    'mcms\partners\assets\AppAsset',
    'mcms\partners\assets\ClipboardAsset',
  ];
}
