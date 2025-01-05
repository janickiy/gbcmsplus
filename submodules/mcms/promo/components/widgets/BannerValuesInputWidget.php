<?php

namespace mcms\promo\components\widgets;

use mcms\pages\models\CategoryProp;
use mcms\pages\models\Page;
use mcms\pages\models\PageProp;
use mcms\promo\models\Banner;
use mcms\promo\models\BannerAttributeValue;
use mcms\promo\models\BannerTemplateAttribute;
use Yii;
use yii\base\Widget;

class BannerValuesInputWidget extends Widget
{

  /**
   * @var \kartik\form\ActiveForm
   */
  public $form;

  /**
   * @var BannerTemplateAttribute;
   */
  public $templateAttribute;

  /**
   * @var Banner
   */
  public $banner;

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    $bannerValues = $this->banner->getAttributeValues()
      ->where([
      'attribute_id' => $this->templateAttribute->id
      ])
      ->all()
    ;

    return $this->render('banner_values_input', [
      'form' => $this->form,
      'bannerValues' => empty($bannerValues)
        ? [new BannerAttributeValue(['attribute_id' => $this->templateAttribute->id])]
        : $bannerValues
      ,
      'templateAttribute' => $this->templateAttribute
    ]);
  }
}