<?php
namespace mcms\promo\components\widgets;

use mcms\promo\models\Stream;
use yii\helpers\Url;
use mcms\common\widget\Select2;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\search\StreamSearch;

/**
 * Class StreamsDropdown
 * @package mcms\promo\components\widgets
 */
class StreamsDropdown extends Select2
{

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
    $url = ArrayHelper::getValue($config, 'url', Url::to(['/promo/streams/stream-search/']));
    $ajaxRequestDataCallback = ArrayHelper::getValue($config, 'ajaxRequestDataCallback', null);

    unset($config['url']);

    $ajaxOptions = [
      'url' => $url,
    ];

    if ($ajaxRequestDataCallback) {
      $ajaxOptions['data'] = $ajaxRequestDataCallback;
    }

    $config = ArrayHelper::merge([
      'pluginOptions' => [
        'allowClear' => true,
        'ajax' => $ajaxOptions,
      ]
    ], $config);

    if (!empty($config['initValueId'])) {
      $searchModel = new StreamSearch(['id' => $config['initValueId']]);
      if (is_array($config['initValueId'])) {
        $searchModel->setScenario(StreamSearch::SCENARIO_IDS_SEARCH);
      }

      $config['data'] = ArrayHelper::map($searchModel->search([])->getModels(), 'id', function ($model) {
        /** @var Stream $model */
        return $model->getStringInfo();
      });
    } else {
      $config['data'] = [];
    }

    parent::__construct($config);
  }
}