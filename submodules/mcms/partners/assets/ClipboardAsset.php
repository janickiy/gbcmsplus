<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class ClipboardAsset extends AssetBundle
{

  public $sourcePath = '@mcms/partners/assets/resources/default';

  /**
   * @var array JavaScript files
   */
  public $js = [
    'js/clipboard.min.js',
  ];
}
