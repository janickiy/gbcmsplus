<?php

use kartik\grid\GridView;
use mcms\common\grid\ContentViewPanel;
use rgk\export\ExportMenu;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\statistic\models\mysql\DetailStatistic;
use mcms\statistic\assets\StatisticAsset;
use yii\widgets\Pjax;

StatisticAsset::register($this);
\mcms\common\grid\SortIcons::register($this);
$promoModule = Yii::$app->getModule('promo');
$userModule = Yii::$app->getModule('users');
$statModule = Yii::$app->getModule('statistic');
/** @var \mcms\statistic\models\mysql\DetailStatisticSubscriptions $statisticModel */
/** @var DetailStatistic $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */
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
        'template' => '{subscription-detail}',
        'urlCreator' => function ($action, $model, $key, $index) {
          return \yii\helpers\Url::to([$action, 'id' => $model['hit_id']]);
        },
        'buttonOptions' => [
          'data-modal-max-width' => '1300px', 'data-modal-width' => '100%'
        ],
        'visible' => $statisticModel->canViewDetailStatistic() && !$statisticModel->groupByPhone,
        'headerOptions' => ['data-disable-hide-column' => "true", 'class' => 'action-column'],
        'contentOptions' => ['data-disable-hide-column' => "true"],
      ],
      [
        'attribute' => 'hit_id',
        'label' => $statisticModel->getGridColumnLabel('hit_id'),
        'visible' => !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'phone',
        'label' => $statisticModel->getGridColumnLabel('phone_number'),
        'format' => $statisticModel->canViewFullPhone() ? 'raw' : 'protectedPhone',
        'visible' => $statisticModel->canViewPhone()
      ],
      [
        'attribute' => 'countPhones',
        'label' => $statisticModel->getGridColumnLabel('countPhones'),
        'format' => 'raw',
        'visible' => $statisticModel->groupByPhone,
        'value' => function ($model) use ($statisticModel) {
          // При клике по кол-ву переходим на ту же детальную стату,
          // отфильтрованную по тем же параметрам чтоб отобразить каждую пдп из этого кол-ва

          $value = ArrayHelper::getValue($model, 'countPhones');
          if (!$value) return 0;

          // берем текущий фильтр и добавляем в него доп. параметры для ссылки
          $searchAttrs =  array_filter(ArrayHelper::getValue($statisticModel->requestData, 'statistic', []));
          $searchAttrs['groupByPhone'] = 0;

          // TRICKY читай таск MCMS-1555
          // При сгруппированной стате фильтры сверху фильтруют по time_on.
          // При обычной стате фильтры сверху фильтруют по last_time.
          // И при клике на ссылку в сгруппированной мы должны выставить фильтры по дате подписки и фильтры по last_time
          $searchAttrs['start_date'] = $statisticModel->start_date;
          $searchAttrs['end_date'] = Yii::$app->formatter->asDate('today', 'php:Y-m-d');
          $searchAttrs['subscribeDateFrom'] = $statisticModel->start_date;
          $searchAttrs['subscribeDateTo'] = $statisticModel->end_date;


          $searchAttrs['phone_number'] = ArrayHelper::getValue($model, 'phone');

          return Html::a($value, ['subscriptions', 'statistic' => $searchAttrs], ['data-pjax' => 0, 'target' => '_blank']);
        }
      ],
      [
        'attribute' => 'ip',
        'label' => $statisticModel->getGridColumnLabel('ip'),
        'format' => 'ipFromLong',
        'visible' => $statisticModel->canViewIp() && !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'email',
        'label' => $statisticModel->getGridColumnLabel('email'),
        'format' => 'raw',
        'value' => function ($model, $key, $index, $column) use ($userModule) {
          $userId = ArrayHelper::getValue($model, 'user_id');
          return Html::a(
            '#' . $userId . '. '. ArrayHelper::getValue($model, 'email'),
            $userModule->api('userLink')->buildProfileLink($userId),
            [ 'target' => '_blank', 'data-pjax' => 0 ],
            ['UsersUserView' => ['userId' => $userId]],
            false
          );
        },
        'visible' => $statisticModel->canViewUser() && !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'stream_name',
        'label' => $statisticModel->getGridColumnLabel('stream'),
        'visible' => $statisticModel->canViewStream() && !$statisticModel->groupByPhone,
        'format' => 'stringOrNull',
        'value' => function ($model, $key, $index, $column) use ($promoModule) {
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
        'visible' => $statisticModel->canViewSource() && !$statisticModel->groupByPhone,
        'format' => 'stringOrNull',
        'value' => function ($model, $key, $index, $column) use ($promoModule) {
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
        'visible' => $statisticModel->canViewLanding() && !$statisticModel->groupByPhone,
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
        'value' => function ($model, $key, $index, $column) use ($promoModule) {
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
        'value' => function ($model, $key, $index, $column) use ($promoModule) {
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
        'visible' => $statisticModel->canViewPlatform() && !$statisticModel->groupByPhone,
        'format' => 'stringOrNull',
        'value' => function ($model, $key, $index, $column) use ($promoModule) {
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
        'attribute' => 'subscribed_at',
        'label' => $statisticModel->getGridColumnLabel('subscribed_at'),
        'format' => 'datetime',
        'visible' => !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'unsubscribed_at',
        'label' => $statisticModel->getGridColumnLabel('unsubscribed_at'),
        'format' => 'datetime',
        'value' => function ($model, $key, $index, $column) {
          return ArrayHelper::getValue($model, $column->attribute) ?: null;
        },
        'visible' => !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'last_rebill_at',
        'label' => $statisticModel->getGridColumnLabel('last_rebill_at'),
        'format' => 'datetime',
        'value' => function ($model, $key, $index, $column) {
          return ArrayHelper::getValue($model, $column->attribute) ?: null;
        },
        'visible' => !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'count_rebills',
        'label' => $statisticModel->getGridColumnLabel('rebill_count'),
        'visible' => !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'sum_profit_rub',
        'label' => $statisticModel->getGridColumnLabel('sum_profit_rub'),
        'visible' => $statisticModel->canViewPartnerProfit() && $statisticModel->canViewColumnByCurrency('rub') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getPartnerProfit($row, 'rub');
        },
      ],
      [
        'attribute' => 'sum_profit_eur',
        'label' => $statisticModel->getGridColumnLabel('sum_profit_eur'),
        'visible' => $statisticModel->canViewPartnerProfit() && $statisticModel->canViewColumnByCurrency('eur') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getPartnerProfit($row, 'eur');
        }
      ],
      [
        'attribute' => 'sum_profit_usd',
        'label' => $statisticModel->getGridColumnLabel('sum_profit_usd'),
        'visible' => $statisticModel->canViewPartnerProfit() && $statisticModel->canViewColumnByCurrency('usd') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getPartnerProfit($row, 'usd');
        }
      ],
      [
        'attribute' => 'sum_reseller_profit_rub',
        'label' => $statisticModel->getGridColumnLabel('sum_reseller_profit_rub'),
        'visible' => $statisticModel->canViewResellerProfit() && $statisticModel->canViewColumnByCurrency('rub') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getResellerProfit($row, 'rub');
        }
      ],
      [
        'attribute' => 'sum_reseller_profit_eur',
        'label' => $statisticModel->getGridColumnLabel('sum_reseller_profit_eur'),
        'visible' => $statisticModel->canViewResellerProfit() && $statisticModel->canViewColumnByCurrency('eur') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getResellerProfit($row, 'eur');
        }
      ],
      [
        'attribute' => 'sum_reseller_profit_usd',
        'label' => $statisticModel->getGridColumnLabel('sum_reseller_profit_usd'),
        'visible' => $statisticModel->canViewResellerProfit() && $statisticModel->canViewColumnByCurrency('usd') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getResellerProfit($row, 'usd');
        }
      ],
      [
        'attribute' => 'sum_real_profit_rub',
        'label' => $statisticModel->getGridColumnLabel('sum_real_profit_rub'),
        'visible' => $statisticModel->canViewAdminProfit() && $statisticModel->canViewColumnByCurrency('rub') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getAdminProfit($row, 'rub');
        }
      ],
      [
        'attribute' => 'sum_real_profit_eur',
        'label' => $statisticModel->getGridColumnLabel('sum_real_profit_eur'),
        'visible' => $statisticModel->canViewAdminProfit() && $statisticModel->canViewColumnByCurrency('eur') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getAdminProfit($row, 'eur');
        }
      ],
      [
        'attribute' => 'sum_real_profit_usd',
        'label' => $statisticModel->getGridColumnLabel('sum_real_profit_usd'),
        'visible' => $statisticModel->canViewAdminProfit() && $statisticModel->canViewColumnByCurrency('usd') && !$statisticModel->groupByPhone,
        'value' => function ($row) use ($model) {
          return $model->getAdminProfit($row, 'usd');
        }
      ],
      [
        'attribute' => 'is_visible_to_partner',
        'label' => $statisticModel->getGridColumnLabel('is_visible_to_partner'),
        'class' => '\kartik\grid\BooleanColumn',
        'contentOptions' => ['style' => 'min-width: 120px'],
        'visible' => !$statisticModel->groupByPhone && $statisticModel->canViewHiddenSoldSubscriptions(),
      ],
      [
        'attribute' => 'is_sold',
        'label' => $statisticModel->getGridColumnLabel('is_sold'),
        'class' => '\kartik\grid\BooleanColumn',
        'contentOptions' => ['style' => 'min-width: 120px'],
        'visible' => !$statisticModel->groupByPhone,
      ],
    ];

    $toolbar = $statModule->canExportDetailStatistic() ? ExportMenu::widget([
      'id' => $exportWidgetId,
      'dataProvider' => $dataProvider,
      'dropdownOptions' => ['class' => 'btn-xs btn-success', 'menuOptions' => ['class' => 'pull-right']],
      'template'=>'{menu}',
      'columns' => ArrayHelper::merge($gridColumns, [
        [
          'attribute' => 'referrer',
          'label' => $statisticModel->getGridColumnLabel('referrer'),
          'format' => 'raw', // вот только из-за этого форматтера указываем поле отдельно от грида
          'visible' => !$statisticModel->groupByPhone,
        ],
        [
          'attribute' => 'userAgent',
          'label' => $statisticModel->getGridColumnLabel('userAgent'),
          'visible' => !$statisticModel->groupByPhone,
        ],
        [
          'attribute' => 'subid1',
          'label' => $statisticModel->getGridColumnLabel('subid1'),
          'visible' => !$statisticModel->groupByPhone && $statisticModel->canViewSubid(),
        ],
        [
          'attribute' => 'subid2',
          'label' => $statisticModel->getGridColumnLabel('subid2'),
          'visible' => !$statisticModel->groupByPhone && $statisticModel->canViewSubid(),
        ],
        [
          'attribute' => 'getParams',
          'label' => $statisticModel->getGridColumnLabel('cid'),
          'value' => function ($row) {
            // Вытаскиваем cid из get_params
            parse_str($row['getParams'], $getParams);
            return ArrayHelper::getValue($getParams, 'cid');
          },
          'visible' => !$statisticModel->groupByPhone && $statisticModel->canViewCid(),
        ],
      ]),
      'hiddenColumns' => [0],
      'noExportColumns'=> [0],
      'target' => ExportMenu::TARGET_BLANK,
      'pjaxContainerId' => 'statistic-pjax',
      'filename' => Yii::_t('main.detail-statistic-index'),
      'exportConfig' => [
        ExportMenu::FORMAT_HTML => false,
        ExportMenu::FORMAT_PDF => false,
        ExportMenu::FORMAT_EXCEL =>  false,
      ],
    ]) : '';
    $toolbar .=  Html::dropDownList('table-filter', null, array_values($model->gridColumnLabels()), [
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
    ]); ?>
    <?= $this->render('_search_' . $model->getModelGroup(), [
      'model' => $model,
      'operatorsId' => $operatorsId,
      'countriesId' => $countriesId,
      'countries' => $countries,
      'currentGroup' => $currentGroup,
    ]) ?>
    <?php Pjax::begin(['id' => 'statistic-pjax'])?>
    <?php
    $gridColumns = ArrayHelper::merge($gridColumns, [
      [
        'attribute' => 'referrer',
        'label' => $statisticModel->getGridColumnLabel('referrer'),
        'visible' => !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'userAgent',
        'label' => $statisticModel->getGridColumnLabel('userAgent'),
        'visible' => !$statisticModel->groupByPhone,
      ],
      [
        'attribute' => 'subid1',
        'label' => $statisticModel->getGridColumnLabel('subid1'),
        'visible' => !$statisticModel->groupByPhone && $statisticModel->canViewSubid(),
      ],
      [
        'attribute' => 'subid2',
        'label' => $statisticModel->getGridColumnLabel('subid2'),
        'visible' => !$statisticModel->groupByPhone && $statisticModel->canViewSubid(),
      ],
      [
        'attribute' => 'getParams',
        'label' => $statisticModel->getGridColumnLabel('cid'),
        'value' => function ($row) {
          // Вытаскиваем cid из get_params
          parse_str($row['getParams'], $getParams);
          return ArrayHelper::getValue($getParams, 'cid');
        },
        'visible' => !$statisticModel->groupByPhone && $statisticModel->canViewCid(),
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
        'style' => 'overflow:hidden; width: 100%;'  // иначе таблица растягивается за пределы экрана.
      ],
      'emptyCell' => 0,
      'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end();?>
    <?php ContentViewPanel::end() ?>
  </div>
</div>
