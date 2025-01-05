<?php
namespace mcms\user\admin\assets;

use yii\web\AssetBundle;

/**
 * Class AuthTreeAsset
 * @package mcms\user\admin\assets
 */
class AuthTreeAsset extends AssetBundle
{
  public $sourcePath = '@mcms/user/admin/assets';

  public $css = [
    'css/auth_tree.css'
  ];

  public $depends = [
    'yii\bootstrap\BootstrapAsset',
    'mcms\common\assets\CookiesAsset',
    'wbraganca\fancytree\FancytreeAsset',
  ];
}