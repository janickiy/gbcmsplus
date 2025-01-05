<?php

use kartik\grid\GridView;
use mcms\common\grid\ContentViewPanel;
use rgk\export\ExportMenu;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use mcms\statistic\assets\StatisticAsset;
use yii\helpers\Url;

StatisticAsset::register($this);
\mcms\common\grid\SortIcons::register($this);
$promoModule = Yii::$app->getModule('promo');
$userModule = Yii::$app->getModule('users');
/** @var \mcms\statistic\models\mysql\Referrals $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var string $exportFileName */
/** @var string $exportWidgetId */

?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">
    <?php
    $formatter = Yii::$app->formatter;

    $gridColumns = [
      [
        'label' => $model->getGridColumnLabel('user_id'),
        'attribute' => 'user_id',
        'format' => 'raw',
        'footer' => Yii::_t('statistic.statistic_total'),
        'value' => function ($item) use ($model) {
          return Html::a(
            $model->formatUserName($item),
            ['/users/users/view/', 'id' => $item['user_id']],
            ['target' => '_blank', 'data-pjax' => 0], ['UsersUserView' => ['userId' => $item['user_id']]], false
          );
        },
        'contentOptions' => ['class' => 'text-left']
      ],
      [
        'label' => $model->getGridColumnLabel('referrals_count'),
        'attribute' => 'referrals_count',
        'format' => 'raw',
        'footer' => $formatter->asInteger($model->getResultValue('referrals_count')),
        'value' => function ($item) use ($model) {
          return Yii::$app->user->can('StatisticReferralsPartnerModal') ? Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'label' => $item['referrals_count'],
              'data-pjax' => 0,
            ],
            'size' => Modal::SIZE_LG,
            'url' => Url::to([
              'referrals/partner-modal',
              'user_id' => $item['user_id'],
              'start_date' => $model->start_date,
              'end_date' => $model->end_date,
            ]),
          ]) : $item['referrals_count'];
        },
      ],
      [
        'label' => $model->getGridColumnLabel('profit_rub'),
        'attribute' => 'profit_rub',
        'format' => 'statisticSum',
        'footer' => $formatter->asStatisticSum($model->getResultValue('profit_rub')),
      ],
      [
        'label' => $model->getGridColumnLabel('profit_eur'),
        'attribute' => 'profit_eur',
        'format' => 'statisticSum',
        'footer' => $formatter->asStatisticSum($model->getResultValue('profit_eur')),
      ],
      [
        'label' => $model->getGridColumnLabel('profit_usd'),
        'attribute' => 'profit_usd',
        'format' => 'statisticSum',
        'footer' => $formatter->asStatisticSum($model->getResultValue('profit_usd')),
      ],
      [
        'label' => $model->getGridColumnLabel('referral_percent'),
        'attribute' => 'referral_percent',
        'format' => 'raw',
        'footer' => '&nbsp;',
      ],
    ];

    $toolbar = ExportMenu::widget([
      'id' => $exportWidgetId,
      'dataProvider' => $dataProvider,
      'dropdownOptions' => ['class' => 'btn-xs btn-success', 'menuOptions' => ['class' => 'pull-right']],
      'template'=>'{menu}',
      'columns' => $gridColumns,
      'target' => ExportMenu::TARGET_BLANK,
      'pjaxContainerId' => 'statistic-pjax',
      'filename' => $exportFileName,
      'exportConfig' => [
        ExportMenu::FORMAT_HTML => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_EXCEL => false,
      ],
    ]);
    $toolbar .= Html::dropDownList('table-filter', null, array_values($model->gridColumnLabels()), [
      'class' => 'selectpicker menu-right col-i',
      'id' => 'table-filter',
      'multiple' => true,
      'title' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
      'data-count-selected-text' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
      'data-selected-text-format' => 'count>1',
    ]);
    ?>
    <?php ContentViewPanel::begin([
      'padding' => false,
      'toolbar' => $toolbar,
    ]);
    $gridColumns = array_map(function($value) {
      if (empty($value['attribute'])) {
        return $value;
      }

      $value['headerOptions'] = [];
      $value['headerOptions']['data-code'] = $value['attribute'];
      return $value;
    }, $gridColumns);
    ?>

    <?= $this->render('_search', [
      'model' => $model,
      'filterDatePeriods' => isset($filterDatePeriods) ? $filterDatePeriods : null,
    ]) ?>
    <?=
    AdminGridView::widget([
      'dataProvider' => $dataProvider,
      'exportConfig' => [
        GridView::CSV => [
        ],
      ],
      'resizableColumns' => false,
      'pjax' => true,
      'pjaxSettings' => ['options' => ['id' => 'statistic-pjax']],
      'tableOptions' => [
        'id' => 'statistic-data-table',
        'class' => 'table table-striped nowrap text-center detail-table dataTables_scrollHeadInner',
        'data-empty-result' => Yii::t('yii', 'No results found.')
      ],
      'options' => [
        'class' => 'grid-view',
        'style' => 'overflow:hidden; width: 100%;'  // иначе таблица растягивается за пределы экрана.
      ],
      'showFooter' => true,
      'emptyCell' => 0,
      'columns' => $gridColumns,
    ]);
    ?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>
