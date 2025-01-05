<?php
namespace mcms\payments\components\widgets\assets;

use yii\web\AssetBundle;

class PartnerAwaitingPaysums extends AssetBundle
{
  public $sourcePath = __DIR__ ;
  
  public $css = [
    'scss/partner_awaiting_paysums.scss',
  ];
  public $js = [
    'js/partner_awaiting_paysums.js'
  ];
  public $depends = [
    'yii\web\YiiAsset',
  ];
}