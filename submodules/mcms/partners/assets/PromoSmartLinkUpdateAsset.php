<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class PromoSmartLinkUpdateAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $js = [
    'js/pages/smart_link_update.js',
  ];
}