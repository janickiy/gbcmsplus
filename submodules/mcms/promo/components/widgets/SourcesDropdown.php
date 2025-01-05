<?php
namespace mcms\promo\components\widgets;

use mcms\promo\models\Source;
use yii\helpers\Url;
use mcms\common\widget\Select2;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\search\SourceSearch;

/**
 * Class SourcesDropdown
 * @package mcms\promo\components\widgets
 */
class SourcesDropdown extends Select2 {

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
    $url = ArrayHelper::getValue($config, 'url', ['/promo/arbitrary-sources/select2/']);
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
      $searchModel = new SourceSearch(['id' => $config['initValueId']]);
      if (is_array($config['initValueId'])) {
        $searchModel->setScenario(SourceSearch::SCENARIO_IDS_SEARCH);
      }

      $config['data'] = ArrayHelper::map($searchModel->search([])->getModels(), 'id', function($model) {
        /** @var Source $model  */
        return $model->getStringInfo();
      });
    } else {
      $config['data'] = [];
    }

    parent::__construct($config);
  }
}