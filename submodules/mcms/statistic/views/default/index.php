<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use mcms\statistic\models\mysql\Statistic;
use mcms\statistic\assets\StatisticAsset;
use rgk\export\ExportMenu;
use mcms\statistic\components\grid\StatisticGrid;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap\Html as BHtml;
use mcms\common\helpers\Html;
use mcms\statistic\models\mysql\DetailStatisticComplains;

StatisticAsset::register($this);

/** @var Statistic $model */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var string $exportFileName */
/** @var \mcms\common\AdminFormatter $formatter */
/** @var string $exportWidgetId */

$removeGroupFilterLabel = Yii::_t('statistic.statistic.remove_filter_group');
$this->registerJs('window.removeGroupFilterLabel = "' . $removeGroupFilterLabel . '";', $this::POS_HEAD);
?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">

    <?php
    $toolbar = ExportMenu::widget([
      'id' => $exportWidgetId,
      'dataProvider' => $dataProvider,
      'filterFormId' => 'statistic-filter-form',
      'isPartners' => true,
      'statisticModel' => $model,
      'columns' => StatisticGrid::getGridColums($model),
      'template'=>'{menu}',
      'target' => ExportMenu::TARGET_BLANK,
      'filename' => $exportFileName,
      'dropdownOptions' => [
        'label' => Yii::_t('main.export'),
        'class' => 'btn-xs btn-success export-btn', 'menuOptions' => ['class' => 'pull-right']
      ],
      'exportConfig' => [
        ExportMenu::FORMAT_HTML => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_EXCEL => false,
        ExportMenu::COPY_URL => [
          'label' => Yii::_t('main.copy_url'),
          'linkOptions' => [
            'id' => 'export_copy_url_link',
            'url' => 'javascript:void(0)',
          ],
          'alertMsg' => '',
          'options' => [
            'title' => Yii::_t('main.copy_url_title'),
          ],
        ],
      ],
    ]);
    $toolbar .= Html::dropDownList('table-filter', null, [], [
      'id' => 'table-filter',
      'class' => 'selectpicker menu-right col-i table-filter-select',
      'multiple' => true,
      'title' => BHtml::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
      'data-count-selected-text' => BHtml::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
      'data-selected-text-format' => 'count>1',
      'data-dropdown-align-right' => 1,
    ]);

    ?>
    <?php ContentViewPanel::begin([
      'padding' => false,
      'toolbar' => $toolbar,
    ]); ?>
    <div class="default-filters-block">
    <?= $this->render('_search', [
      'model' => $model,
      'countriesId' => $countriesId,
      'countries' => $countries,
      'operatorsId' => $operatorsId,
      'filterDatePeriods' => isset($filterDatePeriods) ? $filterDatePeriods : null,
      'shouldHideGrouping' => $shouldHideGrouping
    ]) ?>
    </div>
    <?php Pjax::begin(['id' => 'statistic-pjax']); ?>

    <?= StatisticGrid::widget([
      'dataProvider' => $dataProvider,
      'statisticModel' => $model,
      'resizableColumns' => false,
    ])
    ?>

    <?php
    // Скрипт объявляется в Pjax-блоке, что бы при смене валюты обновлялась и JS-переменная
    $this->registerJs(/** @lang JavaScript */
      "window.STATISTIC_CURRENCY = '{$model->currency}';", $this::POS_HEAD);
    ?>
    <?php Pjax::end(); ?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>