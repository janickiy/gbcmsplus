<?php

namespace mcms\partners\assets\landings\wapsale;

use Yii;
use yii\web\AssetBundle;

class LandingAsset extends AssetBundle
{
  public $sourcePath = '@mcms/partners/assets/landings/wapsale';

  public $css = [
    'https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700,300&subset=latin,latin-ext',
    'https://fonts.googleapis.com/css?family=PT+Serif',
    'css/bootstrap.css',
    'fonts/font-awesome/css/font-awesome.css',
    'fonts/fontello/css/fontello.css',
    'plugins/rs-plugin/css/settings.css',
    'plugins/rs-plugin/css/extralayers.css',
    'plugins/magnific-popup/magnific-popup.css',
    'css/animations.css',
    'plugins/owl-carousel/owl.carousel.css',
    'css/style.css',
    'style-switcher/style-switcher.css',
    'css/custom.css',
  ];
  public $js = [
    'plugins/modernizr.js',
    'plugins/rs-plugin/js/jquery.themepunch.tools.min.js',
    'plugins/rs-plugin/js/jquery.themepunch.revolution.min.js',
    'plugins/isotope/isotope.pkgd.min.js',
    'plugins/owl-carousel/owl.carousel.js',
    'plugins/magnific-popup/jquery.magnific-popup.min.js',
    'plugins/jquery.appear.js',
    'plugins/jquery.countTo.js',
    'plugins/jquery.parallax-1.1.3.js',
    'plugins/jquery.validate.js',
    'plugins/jquery.browser.js',
    'plugins/SmoothScroll.js',
    'js/template.js',
    'js/custom.js',
  ];
  public $depends = [
    'yii\web\JqueryAsset',
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset',
    'yii\bootstrap\BootstrapPluginAsset',
    'mcms\common\assets\TargetXssAsset',
  ];

}
