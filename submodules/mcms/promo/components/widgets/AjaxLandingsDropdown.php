<?php
namespace mcms\promo\components\widgets;

use mcms\promo\models\Landing;
use mcms\promo\models\search\LandingSearch;
use mcms\common\widget\Select2;
use mcms\common\helpers\ArrayHelper;

/**
 * Class AjaxLandingsDropdown
 * @package mcms\promo\components\widgets
 */
class AjaxLandingsDropdown extends Select2 {

  /**
   * Выбранные ид
   * @var
   */
  public $initValueId;

  public $showToggleAll = false;
  /**
   * @var string $ajaxRequestDataCallback обработчик параметров аякс запроса для select2
   */
  public $ajaxRequestDataCallback = null;

  /**
   * @inheritdoc
   */
  public function __construct($config = [])
  {
    $url = ArrayHelper::getValue($config, 'url', ['/promo/landings/select2/']);
    $ajaxRequestDataCallback = ArrayHelper::getValue($config, 'ajaxRequestDataCallback', null);

    unset($config['url']);

    $ajaxConfig = [
      'url' => $url,
    ];

    if ($ajaxRequestDataCallback) {
      $ajaxOptions['data'] = $ajaxRequestDataCallback;
    }

    $config = ArrayHelper::merge([
      'pluginOptions' => [
        'allowClear' => true,
        'ajax' => $ajaxConfig
      ]
    ], $config);

    if (!empty($config['initValueId'])) {
      $dataProvider = (new LandingSearch)->search(['id' => $config['initValueId']], '');
      $config['data'] = ArrayHelper::map($dataProvider->getModels(), 'id', function($model) {
        /** @var Landing $model  */
        return $model->getStringInfo();
      });
    } else {
      $config['data'] = [];
    }

    parent::__construct($config);
  }
}