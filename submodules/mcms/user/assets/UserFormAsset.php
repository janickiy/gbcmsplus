<?php
namespace mcms\user\assets;

use yii\web\AssetBundle;

class UserFormAsset extends AssetBundle
{
  public $sourcePath = '@mcms/user/assets/resources';
  public $js = [
    'js/user_form.js',
    'js/jquery-password-generator-plugin.js',
    'js/password_generate.js',
  ];
  public $depends = [
    'yii\bootstrap\BootstrapAsset',
    'yii\web\YiiAsset',
  ];
}