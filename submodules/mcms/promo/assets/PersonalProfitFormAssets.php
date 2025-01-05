<?php

namespace mcms\promo\assets;

use yii\web\AssetBundle;

class PersonalProfitFormAssets extends AssetBundle
{
  public $sourcePath = '@mcms/promo/assets/resources';
  public $js = ['js/personal_profit_form.js'];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}