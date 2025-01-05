<?php

namespace mcms\partners\assets\basic;

use yii\web\AssetBundle;

class ProfileAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/profile.scss',
  ];

  public $js = [
    'js/bootstrap-select.js',
    'js/pages/profile.js',
    'js/pages/multiple-contacts.js',
  ];

  public $depends = [
    'mcms\partners\assets\BasicAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
  ];
}
