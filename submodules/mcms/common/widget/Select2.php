<?php

namespace mcms\common\widget;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use yii\helpers\Url;

class Select2 extends \kartik\widgets\Select2
{
  // TRICKY Константа не используется в файле mcms/promo/assets/resources/js/arbitrary-sources-update.js, нужно менять вручную
  const THEME_SMARTADMIN = 'smartadmin';

  public $theme = Select2::THEME_SMARTADMIN;
  private $canViewSelect = true;

  /**
   * @inheritDoc
   */
  public function __construct(array $config = [])
  {
    $pluginOptions = ArrayHelper::getValue($config, 'pluginOptions', []);
    $ajax = ArrayHelper::getValue($pluginOptions, 'ajax', []);
    $url = ArrayHelper::getValue($ajax, 'url');
    if ($url && is_array($url)) {
      $this->canViewSelect = Html::hasUrlAccess($url);
      $config['pluginOptions']['ajax']['url'] = Url::to($url);
    }
    parent::__construct($config);
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    if (!$this->canViewSelect) return;
    parent::run();
  }
}