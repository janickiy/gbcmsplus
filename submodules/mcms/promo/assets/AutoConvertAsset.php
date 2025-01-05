<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

/**
 * Автоматическая конвертация валют в инпутах формы (заполняешь инпут в одной валюте, в остальные записывается автоматически)
 */
class AutoConvertAsset extends AssetBundle
{
  public $sourcePath = "@mcms/promo/assets/resources";
  public $js = ['js/auto-convert.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}
