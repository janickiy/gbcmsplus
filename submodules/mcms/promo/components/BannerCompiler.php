<?php

namespace mcms\promo\components;

use mcms\promo\models\Banner;
use mcms\promo\models\BannerAttributeValue;
use mcms\promo\models\BannerTemplate;
use Yii;

/**
 * Class BannerCompiler
 * @package mcms\promo\components
 */
class BannerCompiler
{
  /** @var  Banner */
  private $banner;
  /** @var BannerTemplate  */
  private $templateModel;
  /** @var string|null  */
  private $language;

  /** @var BannerAttributeValue[]  */
  private $values;

  static function createFromTemplateAttributeValues(
    BannerTemplate $template,
    array $attributeValues,
    $language = null
  ) {
    $instance = new self;
    $instance->templateModel = $template;
    $instance->values = $attributeValues;
    $instance->language = $language;

    return $instance;
  }

  static function createFromBannerLanguage(Banner $banner, $language = null)
  {
    $instance = new self;
    $instance->banner = $banner;
    $instance->language = $language;
    $instance->templateModel = $banner->getTemplate()->one();

    $instance->values = $banner->getAttributeValues()->indexBy('attribute_id')->all();
    return $instance;
  }

  /**
   * @return array|string
   */
  public function compile()
  {
    $compiled = [];
    $template = $this->templateModel->template;

    /** Если указан конкретный язык в параметрах, отдаём контент для языка */
    if ($this->language) return $this->compileLang($this->language, $this->values, $template);

    foreach (Yii::$app->params['languages'] as $language) {
      $compiled[$language] = $this->compileLang($language, $this->values, $template);
    }

    return $compiled;
  }

  /**
   * @param array $values
   * @param $lang
   * @return array
   */
  private function getReplacements(array $values, $lang)
  {
    $replacements = [];
    /**
     * @var BannerAttributeValue $valueModel
     */
    foreach($values as $attributeId => $valueModel) {
      $replacements = array_merge($replacements, $valueModel->getBannerReplacements($lang));
    }

    return $replacements;
  }

  /**
   * @param $language
   * @param $values
   * @param $template
   * @return string
   */
  private function compileLang($language, $values, $template)
  {
    $replacements = $this->getReplacements($values, $language);
    return strtr($template, $replacements);
  }

  public function getTemplateCode()
  {
    return $this->templateModel->code;
  }
}