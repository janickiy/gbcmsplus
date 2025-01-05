<?php

use mcms\partners\assets\DatePickerAsset;
use mcms\partners\assets\StatAsset;
use kartik\grid\GridView;
use mcms\partners\components\helpers\TbReasonsHelper;
use yii\widgets\Pjax;

StatAsset::register($this);
DatePickerAsset::register($this);

/** @var \mcms\statistic\models\mysql\TBStatistic $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \yii\data\ActiveDataProvider $exportDataProvider */
/** @var mcms\common\web\View $this */
/** @var string $exportFileName */
/** @var string $exportWidgetId */

$gridColumns = [
  [
    'attribute' => 'time',
    'label' => Yii::_t('statistic.tb_statistic-time'),
    'format' =>  ['date', 'dd.MM.Y HH:mm'],
    'contentOptions' => function ($item) {
      return [
        'dthit' => $item['hit_id']
      ];
    },
  ],
  [
    'attribute' => 'ip',
    'label' => Yii::_t('statistic.tb_statistic-ip'),
    'format' => 'ipFromLong',
  ],
  [
    'attribute' => 'country_name',
    'label' => Yii::_t('statistic.tb_statistic-country_name'),
  ],
  [
    'attribute' => 'operator_name',
    'label' => Yii::_t('statistic.tb_statistic-operator'),
  ],
  [
    'attribute' => 'landing_name',
    'label' => Yii::_t('statistic.tb_statistic-landing'),
    'value' => function ($item) {
      return $item['landing_id'] ? '#' . $item['landing_id'] . ' - ' . $item['landing_name'] : null;
    },
  ],
  [
    'attribute' => 'user_agent',
    'label' => Yii::_t('statistic.tb_statistic-user_agent')
  ],
  [
    'attribute' => 'referer',
    'label' => Yii::_t('statistic.tb_statistic-referer')
  ],
  [
    'attribute' => 'platform_name',
    'label' => Yii::_t('statistic.tb_statistic-os')
  ],
  [
    'attribute' => 'source_name',
    'label' => Yii::_t('statistic.tb_statistic-link'),
    'value' => function ($item) {
      return '#' . $item['source_id'] . ' - ' . $item['source_name'];
    },
  ],
  [
    'attribute' => 'tb_reason',
    'headerOptions' => [
      'class' => 'hidden',
    ],
    'contentOptions' => [
      'class' => 'hidden tb-reason',
    ],
    'value' => function($item){
      return TbReasonsHelper::getName($item['tb_reason']);
    }
  ],
];
?>

<div class="container-fluid">
  <div class="bgf">
    <?= $this->render('_search', [
      'model' => $model,
      'filterDatePeriods' => isset($filterDatePeriods) ? $filterDatePeriods : null,
    ]) ?>

    <?= $this->render('../_export_menu', [
      'exportFileName' => $exportFileName,
      'dataProvider' => $exportDataProvider,
      'gridColumns' => $gridColumns,
      'exportWidgetId' => $exportWidgetId,
    ]);
    ?>

    <?php Pjax::begin(['id' => 'statistic-pjax', 'clientOptions' => [
      'method' => 'POST'
    ]]); ?>

    <?php if(!$dataProvider->totalCount):?>
    <div class="empty_data">
      <i class="icon-no_data"></i>
      <span><?= Yii::_t('main.no_results_found') ?></span>
    </div>
    <?php else:?>

    <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'resizableColumns' => false,
      'tableOptions' => [
        'id' => 'tb-statistic-grid',
        'class' => 'table table-striped-custom table-custom table-trafficback dataTable',
        'data-skip-summary-calculation' => '0',
        'data-empty-result' => Yii::t('yii', 'No results found')
      ],
      'layout' => '{items}<div align="center">{pager}</div>',
      'options' => [
        'class' => 'grid-view',
        'style' => 'overflow:auto' // иначе таблица растягивается за пределы экрана.
      ],
      'export' => false,
      'bordered' => false,
      'striped' => false,
      'columns' => [
        [
          'attribute' => 'time',
          'label' => Yii::_t('statistic.tb_statistic-time'),
          'format' =>  ['date', 'dd.MM.Y HH:mm'],
          'contentOptions' => function ($item) {
            return [
              'dthit' => $item['hit_id']
            ];
          },
        ],
        [
          'attribute' => 'ip',
          'label' => Yii::_t('statistic.tb_statistic-ip'),
          'format' => 'ipFromLong',
        ],
        [
          'attribute' => 'country_name',
          'label' => Yii::_t('statistic.tb_statistic-country_name'),
        ],
        [
          'attribute' => 'operator_name',
          'label' => Yii::_t('statistic.tb_statistic-operator'),
        ],
        [
          'attribute' => 'landing_name',
          'label' => Yii::_t('statistic.tb_statistic-landing'),
          'value' => function ($item) {
            return $item['landing_id'] ? '#' . $item['landing_id'] . ' - ' . $item['landing_name'] : null;
          },
        ],
        [
          'attribute' => 'user_agent',
          'label' => Yii::_t('statistic.tb_statistic-user_agent')
        ],
        [
          'attribute' => 'referer',
          'label' => Yii::_t('statistic.tb_statistic-referer')
        ],
        [
          'value' => function() {
            return '<span class="load_content-partial"><i class="icon-view"></i></span>';
          },
          'contentOptions' => [
            'class' => 'table-collapse_btn',
          ],
          'format' => 'raw',
        ],
        [
          'attribute' => 'platform_name',
          'headerOptions' => [
            'class' => 'hidden',
          ],
          'contentOptions' => function($model){
            return [
              'class' => 'hidden tb-os',
              'tb-reason' => $model['tb_reason'],
            ];
          },
        ],
        [
          'attribute' => 'source_name',
          'headerOptions' => [
            'class' => 'hidden',
          ],
          'contentOptions' => [
            'class' => 'hidden tb-link',
          ],
          'value' => function ($item) {
            return '#' . $item['source_id'] . ' - ' . $item['source_name'];
          },
        ],
        [
          'attribute' => 'operator_name',
          'headerOptions' => [
            'class' => 'hidden',
          ],
          'contentOptions' => [
            'class' => 'hidden tb-op',
          ],
        ],
        [
          'attribute' => 'landing_name',
          'headerOptions' => [
            'class' => 'hidden',
          ],
          'contentOptions' => [
            'class' => 'hidden tb-land',
          ],
          'value' => function ($item) {
            return $item['landing_id'] ? '#' . $item['landing_id'] . ' - ' . $item['landing_name'] : null;
          },
        ],
        [
          'attribute' => 'user_agent',
          'headerOptions' => [
            'class' => 'hidden',
          ],
          'contentOptions' => [
            'class' => 'hidden tb-ua',
          ],
        ],
        [
          'attribute' => 'referer',
          'headerOptions' => [
            'class' => 'hidden',
          ],
          'contentOptions' => [
            'class' => 'hidden tb-ref',
          ],
        ],
        [
          'attribute' => 'tb_reason',
          'headerOptions' => [
            'class' => 'hidden',
          ],
          'contentOptions' => [
            'class' => 'hidden tb-reason',
          ],
          'value' => function($item){
        return TbReasonsHelper::getName($item['tb_reason']);
          }
        ],
      ],
    ]) ?>

    <?php endif;?>
    <?php Pjax::end(); ?>

    <div class="collapse_template">
      <table>
        <tr class="collapse_tr">
          <td colspan="8">
            <div class="collapse-content" style="display: block;">
              <table class="table">
                <tr>
                  <td>
                    <dl class="dl-horizontal">
                      <dt><?= Yii::_t('statistic.tb_statistic-os') ?></dt>
                      <dd class="tb-os"></dd>
                    </dl>
                    <dl class="dl-horizontal">
                      <dt><?= Yii::_t('statistic.tb_statistic-link') ?></dt>
                      <dd class="tb-link"></dd>
                    </dl>
                    <dl class="dl-horizontal">
                      <dt><?= Yii::_t('statistic.tb_statistic-operator') ?></dt>
                      <dd class="tb-op"></dd>
                    </dl>
                    <dl class="dl-horizontal">
                      <dt><?= Yii::_t('statistic.tb_statistic-landing') ?></dt>
                      <dd class="tb-land"></dd>
                    </dl>
                  </td>
                  <td>
                    <dl class="dl-horizontal">
                      <dt><?= Yii::_t('statistic.tb_statistic-user_agent') ?></dt>
                      <dd class="tb-ua"></dd>
                    </dl>
                    <dl class="dl-horizontal">
                      <dt><?= Yii::_t('statistic.tb_statistic-referer') ?></dt>
                      <dd class="tb-ref"></dd>
                    </dl>
                    <dl class="dl-horizontal">
                      <dt><?= Yii::_t('statistic.tb_statistic-reason') ?></dt>
                      <dd class="tb-reason"></dd>
                    </dl>
                  </td>
                </tr>
              </table>
            </div>
          </td>
        </tr>
      </table>
    </div>

  </div>
</div>