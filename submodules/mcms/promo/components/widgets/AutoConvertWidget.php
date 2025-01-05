<?php

namespace mcms\promo\components\widgets;

use mcms\promo\assets\AutoConvertAsset;
use yii\web\View;

/**
 * Автоматическая конвертация валют в инпутах формы (заполняешь инпут в одной валюте, в остальные записывается автоматически)
 */
class AutoConvertWidget
{
  /**
   * @var array Курсы валют
   */
  private $exchangeCourses;

  /**
   * @var View
   */
  private $view;

  // id полей, которые необходимо заполнять
  private $rubInputId;
  private $usdInputId;
  private $eurInputId;

  /**
   * AutoConvertWidget constructor.
   * @param View $view
   * @param array $exchangeCourses
   * @param $rubInputId
   * @param $usdInputId
   * @param $eurInputId
   */
  public function __construct(View $view, array $exchangeCourses, $rubInputId, $usdInputId, $eurInputId)
  {
    $this->exchangeCourses = $exchangeCourses;
    $this->rubInputId = $rubInputId;
    $this->usdInputId = $usdInputId;
    $this->eurInputId = $eurInputId;
    $this->view = $view;
  }

  /**
   * Загрузка ассетов
   */
  public function run()
  {
    $config = <<<JS
AUTO_CONVERT_CONFIG = {
  courses: {
    usdEur: {$this->exchangeCourses['usd_eur']}, 
    usdRub: {$this->exchangeCourses['usd_rub']}, 
    rubEur: {$this->exchangeCourses['rub_eur']}, 
    rubUsd: {$this->exchangeCourses['rub_usd']}, 
    eurUsd: {$this->exchangeCourses['eur_usd']}, 
    eurRub: {$this->exchangeCourses['eur_rub']}
  }, 
  rubInputId: '{$this->rubInputId}', 
  usdInputId: '{$this->usdInputId}', 
  eurInputId: '{$this->eurInputId}'
};

var acObject = autoConvert(AUTO_CONVERT_CONFIG);

$(document).on('blur', acObject.getSelector(), function(){
  acObject.convertFields($(this));
});
JS;
    $this->view->registerJs($config, View::POS_READY);
    AutoConvertAsset::register($this->view);
  }
}
