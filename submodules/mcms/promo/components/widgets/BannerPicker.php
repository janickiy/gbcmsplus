<?php

namespace mcms\promo\components\widgets;

use mcms\promo\models\Banner;
use mcms\promo\models\BannerTemplate;
use Yii;
use yii\base\Widget;
use yii\db\ActiveRecord;

/**
 * Class BannerPicker
 * @package mcms\promo\components\widgets
 */
class BannerPicker extends Widget
{
  /** @var  ActiveRecord */
  public $model;
  /** @var  */
  public $attribute;
  /**
   * @inheritdoc
   */
  public function run()
  {
    $templates = BannerTemplate::findAllActive()
      ->joinWith('activeBanners')
      ->all();

    return $this->render('banner_picker', [
      'model' => $this->model,
      'attribute' => $this->attribute,
      'templates' => $templates,
      'languages' => Yii::$app->params['languages']
    ]);
  }
}