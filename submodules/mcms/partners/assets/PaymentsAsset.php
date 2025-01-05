<?php

namespace mcms\partners\assets;

use yii\web\AssetBundle;

class PaymentsAsset extends AssetBundle
{

  public $sourcePath = '@mcms/partners/assets/resources/basic';

  public $css = [
    'scss/pages/payments.scss'
  ];

  public $js = [
    'js/vue/payments.component.js',
    'js/pages/payments.js'
  ];
  public $depends = [
    'mcms\partners\assets\BasicAsset',
    'mcms\partners\assets\VueAsset',
     // Для формы настроек платежной системы
     // TRICKY Возможны конфликты со стилями payments.scss
    'mcms\partners\assets\FinanceAsset',
  ];
}