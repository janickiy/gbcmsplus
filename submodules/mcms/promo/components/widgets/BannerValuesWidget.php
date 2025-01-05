<?php

namespace mcms\promo\components\widgets;

use mcms\promo\models\Banner;
use mcms\promo\models\BannerTemplate;
use yii\base\Widget;
use yii\web\View;

class BannerValuesWidget extends Widget
{

  public static $counter = 0;

  /**
   * @var \kartik\form\ActiveForm
   */
  public $form;

  /**
   * @var Banner
   */
  public $banner;

  const PJAX_CONTAINER = 'bannerValuesPjax';

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    /** @var BannerTemplate $bannerTemplate */
    $templateAttributes = $this->banner->isNewRecord ? false : [];
    if ($this->banner->template_id
      && $bannerTemplate = BannerTemplate::findOne($this->banner->template_id) ) {
      $templateAttributes = $bannerTemplate->getTemplateAttributes()->orderBy(['id' => SORT_ASC])->all();
    }

    $render = $this->render('banner_values', [
      'banner' => $this->banner,
      'form' => $this->form,
      'templateAttributes' => $templateAttributes
    ]);

    $this->view->registerJs('var propsCounter = ' . self::$counter . ';', View::POS_END);

    return $render;
  }
}