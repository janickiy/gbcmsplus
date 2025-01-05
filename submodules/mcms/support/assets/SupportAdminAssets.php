<?php

namespace mcms\support\assets;

use yii\web\AssetBundle;

class SupportAdminAssets extends AssetBundle
{
  public $sourcePath = '@mcms/support/assets';
  public $css = [];
  public $js = ['admin.js'];
  public $depends = [
    'yii\web\YiiAsset',
    'branchonline\lightbox\LightboxAsset'
  ];
}