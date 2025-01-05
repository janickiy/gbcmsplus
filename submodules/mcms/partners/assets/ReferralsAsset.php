<?php

namespace mcms\partners\assets;

use Yii;
use yii\web\AssetBundle;

class ReferralsAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/referals.scss',
  ];

  public $js = ['js/pages/referrals.js'];
  public $depends = [
    'mcms\partners\assets\ClipboardAsset',
    'mcms\partners\assets\BasicAsset',
    'mcms\partners\assets\BootstrapSelectAsset',
  ];



}
