<?php
namespace mcms\pages\assets;

use yii\web\AssetBundle;

class PagePreviewAsset extends AssetBundle
{
  public $sourcePath = '@mcms/pages/assets/resources';
  public $css = [
    'css/page_preview.css',
  ];
  public $js = [
  ];
  public $depends = [
    'yii\bootstrap\BootstrapAsset',
  ];
}