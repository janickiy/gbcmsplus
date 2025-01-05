<?php

namespace mcms\promo\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Banner;
use mcms\promo\models\BannerAttributeValue;
use mcms\promo\models\BannerTemplateAttribute;
use Yii;
use yii\base\Widget;

class BannerValuesReferenceWidget extends Widget
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
    $entityIds = ArrayHelper::getColumn($this->banner->getAttributeValues()->where([
      'attribute_id' => $this->templateAttribute->id
    ])->each(), 'entity_id');

    return $this->render('banner_values_reference', [
      'form' => $this->form,
      'bannerValues' => new BannerAttributeValue([
        'attribute_id' => $this->templateAttribute->id,
        'entities' => $entityIds
      ]),
      'templateAttribute' => $this->templateAttribute
    ]);
  }
}