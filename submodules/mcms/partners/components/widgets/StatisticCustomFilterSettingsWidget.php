<?php

namespace mcms\partners\components\widgets;

use Yii;
use yii\base\Widget;

/**
 * Class StatisticCustomFilterSettingsWidget
 * @package mcms\partners\components\widgets
 */
class StatisticCustomFilterSettingsWidget extends Widget
{
  public $id;
  public $label;
  public $from;
  public $to;
  public $shouldUpperCaseLabel;

  /**
   * @inheritdoc
   */
  public function run()
  {

    return $this->render('stat_custom_filter_settings', [
      'id' => $this->id,
      'label' => $this->label,
      'from' => $this->from,
      'to' => $this->to,
      'shouldUpperCaseLabel' => $this->shouldUpperCaseLabel
    ]);
  }

}