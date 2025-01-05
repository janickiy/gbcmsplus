<?php

namespace mcms\promo\components\widgets;

use mcms\common\multilang\LangAttribute;
use mcms\promo\models\Banner;
use mcms\promo\models\BannerAttributeValue;
use mcms\promo\models\BannerTemplateAttribute;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

class BannerValuesFileWidget extends Widget
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

  /** @var BannerAttributeValue */
  private $bannerValues;

  private $imagesDelete = [];
  private $previews = [];

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    $this->bannerValues = $this->banner->getAttributeValues()
      ->where(['attribute_id' => $this->templateAttribute->id])
      ->one()
      ?
      : new BannerAttributeValue(['attribute_id' => $this->templateAttribute->id]);

    $this->initPreview();

    $render = $this->render('banner_values_file', [
      'form' => $this->form,
      'bannerValues' => $this->bannerValues,
      'templateAttribute' => $this->templateAttribute,
      'previews' => $this->previews,
      'imagesDelete' => $this->imagesDelete
    ]);

    BannerValuesWidget::$counter++;

    return $render;

  }

  private function initPreview()
  {
    if ($this->bannerValues->isNewRecord) return;

    $multilang = $this->bannerValues->multilang_value;

    if (!$multilang instanceof LangAttribute) return;

    $uploadUrl = BannerAttributeValue::getUploadUrl($this->templateAttribute->id);

    foreach ($multilang as $lang => $file) {
      $this->previews[$lang][] = Html::img($uploadUrl . '/' . $file, ['class' => 'file-preview-image']);
      $this->imagesDelete[$lang] = [
        'key' => $file,
        'url' => Url::toRoute([
          'file-delete',
          'isProp' => 1,
          'id' => $this->bannerValues->id,
        ]),
        'extra' => [
          'lang' => $lang,
        ],
        'frameAttr' => [
          'style' => 'height:80px',
          'title' => 'My Custom Title',
        ],
      ];
    }
  }
}