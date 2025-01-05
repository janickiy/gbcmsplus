<?php

namespace mcms\partners\components\widgets;

use mcms\partners\assets\ProgressAsset;
use Yii;
use yii\base\Widget;
use yii\web\View;

class ProgressWidget extends Widget
{

  public function run()
  {
    ProgressAsset::register($this->view);

    $this->view->registerJs('
      NProgress.done(true);

      $(document).ajaxStart(function(){
        if (window.ajaxValidate) {
          return;
        }
        NProgress.start();
      });
      $(document).ajaxStop(function(){
        if (window.ajaxValidate) {
          return;
        }
        NProgress.done();
      });', View::POS_LOAD, 'NProgress');
  }
}