<?php

use kartik\grid\GridView;
use mcms\common\grid\ContentViewPanel;
use rgk\export\ExportMenu;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\statistic\models\Complain;
use mcms\statistic\models\mysql\DetailStatistic;
use mcms\statistic\assets\StatisticAsset;
use yii\widgets\Pjax;

StatisticAsset::register($this);
\mcms\common\grid\SortIcons::register($this);
$promoModule = Yii::$app->getModule('promo');
$userModule = Yii::$app->getModule('users');
$statModule = Yii::$app->getModule('statistic');
/** @var \mcms\statistic\models\mysql\DetailStatisticComplains $statisticModel */
/** @var DetailStatistic $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var string $currentGroup */
/** @var array $countriesId */
/** @var array $operatorsId */
/** @var string $exportWidgetId */
/** @var \mcms\statistic\Module $statModule */
?>

<div id="page-content-wrapper">
  <div class="container-fluid xyz">

    <?php $statisticModel = $model->getStatisticModel(); ?>

    <?php
    // TRICKY при изменении формата колонок внести изменения в mcms\statistic\controllers\DetailController::actionDownloadCsv()
    $gridColumns = [
      [
        'class' => \mcms\statistic\components\grid\DetailActionColumn::class,
        'template' => '{complain-detail}',
        'visible' => $statisticModel->canViewDetailStatistic(),
        'urlCreator' => function($action, $model, $key, $index) {
          return \yii\helpers\Url::to([$action, 'id' => $model['hit_id']]);
        },
        'buttonOptions' => [
          'data-modal-width' => '800px',
        ],
        'headerOptions' => ['data-disable-hide-column' => "true", 'class' => 'action-column'],
        'contentOptions' => ['data-disable-hide-column' => "true"],
      ],
      [
        'attribute' => 'hit_id',
        'label' => $statisticModel->getAttributeLabel('hit_id')
      ],
      [
        'attribute' => 'type',
        'label' => $statisticModel->getAttributeLabel('type'),
        'value' => function ($row) use ($statisticModel) {
          $type = ArrayHelper::getValue($row, 'type');
          if (is_null($type)) return $type;
          return ArrayHelper::getValue(Complain::getTypes(), $type);
        }
      ],
      [
        'attribute' => 'time',
        'label' => $statisticModel->getAttributeLabel('time'),
        'format' => 'datetime',
      ],
      [
        'attribute' => 'description',
        'label' => $statisticModel->getAttributeLabel('description'),
        'format' => 'raw',
      ],
      [
        'attribute' => 'phone_number',
        'label' => $statisticModel->getAttributeLabel('phone_number'),
        'format' => $statisticModel->canViewFullPhone() ? 'raw' : 'protectedPhone',
        'visible' => $statisticModel->canViewPhone()
      ],
      [
        'attribute' => 'ip',
        'label' => $statisticModel->getGridColumnLabel('ip'),
        'format' => 'ipFromLong',
        'visible' => $statisticModel->canViewIp()
      ],
      [
        'attribute' => 'email',
        'label' => $statisticModel->getGridColumnLabel('email'),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($userModule) {
          $userId = ArrayHelper::getValue($model, 'user_id');
          return Html::a(
            '#' . $userId . '. ' . ArrayHelper::getValue($model, 'email'),
            $userModule->api('userLink')->buildProfileLink($userId),
            ['target' => '_blank', 'data-pjax' => 0],
            ['UsersUserView' => ['userId' => $userId]],
            false
          );
        },
        'visible' => $statisticModel->canViewUser()
      ],
      [
        'attribute' => 'stream_name',
        'label' => $statisticModel->getGridColumnLabel('stream'),
        'visible' => $statisticModel->canViewStream(),
        'format' => 'stringOrNull',
        'value' => function($model, $key, $index, $column) use ($promoModule) {
          $url = $promoModule->api('url')->viewStream(ArrayHelper::getValue($model, 'stream_id'));
          return Html::a(
            ArrayHelper::getValue($model, $column->attribute),
            $url,
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          );
        }
      ],
      [
        'attribute' => 'source_name',
        'label' => $statisticModel->getGridColumnLabel('source'),
        'visible' => $statisticModel->canViewSource(),
        'format' => 'stringOrNull',
        'value' => function($model, $key, $index, $column) use ($promoModule) {
          $url = $promoModule->api('url')->viewSource(
            ArrayHelper::getValue($model, 'source_id'),
            ArrayHelper::getValue($model, 'source_type')
          );
          return Html::a(
            ArrayHelper::getValue($model, $column->attribute),
            $url,
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          );
        }
      ],
      [
        'attribute' => 'landing_name',
        'label' => $statisticModel->getGridColumnLabel('landings'),
        'visible' => $statisticModel->canViewLanding(),
        'format' => 'stringOrNull',
        'value' => function($model, $key, $index, $column) use ($promoModule) {
          $landingId = ArrayHelper::getValue($model, 'landing_id');
          if ($landingId === null) return null;

          $url = $promoModule->api('url')->viewLanding($landingId);

          return Html::a(
            Yii::$app->formatter->asLanding($landingId, ArrayHelper::getValue($model, $column->attribute)),
            $url,
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          );
        }
      ],
      [
        'attribute' => 'country_name',
        'label' => $statisticModel->getGridColumnLabel('countries'),
        'visible' => $statisticModel->canViewCountry(),
        'format' => 'stringOrNull',
        'value' => function($model, $key, $index, $column) use ($promoModule) {
          $url = $promoModule->api('url')->viewCountry(ArrayHelper::getValue($model, 'country_id'));
          return Html::a(
            ArrayHelper::getValue($model, $column->attribute),
            $url,
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          );
        }
      ],
      [
        'attribute' => 'operator_name',
        'label' => $statisticModel->getGridColumnLabel('operators'),
        'visible' => $statisticModel->canViewOperator(),
        'format' => 'stringOrNull',
        'value' => function($model, $key, $index, $column) use ($promoModule) {
          $url = $promoModule->api('url')->viewOperator(ArrayHelper::getValue($model, 'operator_id'));
          return Html::a(
            ArrayHelper::getValue($model, $column->attribute),
            $url,
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          );
        }
      ],
      [
        'attribute' => 'platform_name',
        'label' => $statisticModel->getGridColumnLabel('platforms'),
        'visible' => $statisticModel->canViewPlatform(),
        'format' => 'stringOrNull',
        'value' => function($model, $key, $index, $column) use ($promoModule) {
          $url = $promoModule->api('url')->viewPlatform(ArrayHelper::getValue($model, 'platform_id'));
          return Html::a(
            ArrayHelper::getValue($model, $column->attribute),
            $url,
            ['target' => '_blank', 'data-pjax' => 0],
            [],
            false
          );
        }
      ],
      [
        'attribute' => 'landing_pay_type_name',
        'label' => $statisticModel->getGridColumnLabel('landing_pay_type_name'),
      ],
    ];

    $fullExportMenu = $statModule->canExportDetailStatistic() ? ExportMenu::widget([
      'id' => $exportWidgetId,
      'dataProvider' => $dataProvider,
      'container' => [],
      'dropdownOptions' => ['class' => 'btn-xs btn-success', 'menuOptions' => ['class' => 'pull-right']],
      'template'=>'{menu}',
      'columns' => ArrayHelper::merge($gridColumns, [
        [
          'attribute' => 'referrer',
          'label' => $statisticModel->getGridColumnLabel('referrer'),
          'format' => 'raw',
        ],
        [
          'attribute' => 'userAgent',
          'label' => $statisticModel->getGridColumnLabel('userAgent')
        ],
      ]),
      'hiddenColumns' => [0],
      'noExportColumns'=> [0],
      'target' => ExportMenu::TARGET_BLANK,
      'pjaxContainerId' => 'statistic-pjax',
      'filename' => Yii::_t('main.detail-statistic-complains'),
      'exportConfig' => [
        ExportMenu::FORMAT_HTML => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_EXCEL =>  false,
      ],
    ]) : '';
    $fullExportMenu .=  Html::dropDownList('table-filter', null, array_values($model->gridColumnLabels()), [
      'class' => 'selectpicker menu-right col-i',
      'id' => 'table-filter',
      'multiple' => true,
      'title' => yii\bootstrap\Html::icon('cog') . ' ' .Yii::_t('statistic.statistic.filter_table'),
      'data-count-selected-text' => yii\bootstrap\Html::icon('cog') . ' ' . Yii::_t('statistic.statistic.filter_table'),
      'data-selected-text-format' => 'count>1',
    ]);
    ?>
    <?php ContentViewPanel::begin([
      'padding' => false,
      'toolbar' => $fullExportMenu,
    ]); ?>
    <?= $this->render('_search_' . $model->getModelGroup(), [
      'operatorsId' => $operatorsId,
      'countriesId' => $countriesId,
      'countries' => $countries,
      'model' => $model,
      'currentGroup' => $currentGroup
    ]) ?>
    <?php Pjax::begin(['id' => 'statistic-pjax'])?>
    <?php
    $gridColumns = ArrayHelper::merge($gridColumns, [
      [
        'attribute' => 'referrer',
        'label' => $statisticModel->getGridColumnLabel('referrer')
      ],
      [
        'attribute' => 'userAgent',
        'label' => $statisticModel->getGridColumnLabel('userAgent')
      ],
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

    <?=
    AdminGridView::widget([
      'dataProvider' => $dataProvider,
      'exportConfig' => [
        GridView::CSV => [
        ],
      ],
      'resizableColumns' => false,
      'tableOptions' => [
        'id' => 'statistic-data-table',
        'class' => 'table table-striped nowrap text-center detail-table dataTables_scrollHeadInner',
        'data-empty-result' => Yii::t('yii', 'No results found.')
      ],
      'options' => [
        'class' => 'grid-view',
        'style' => 'overflow:hidden; width:100%;'  // иначе таблица растягивается за пределы экрана.
      ],
      'emptyCell' => 0,
      'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end();?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>
