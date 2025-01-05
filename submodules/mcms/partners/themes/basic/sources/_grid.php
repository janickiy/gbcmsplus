<?php

use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\Column;
use mcms\common\helpers\Link;
use mcms\common\helpers\Html;

/* @var mcms\common\web\View $this */
?>

<?= GridView::widget([
  'layout' => '{items}{pager}',
  'dataProvider' => $sourcesDataProvider,
  'tableOptions' => [
    'id' => 'links-grid-table',
    'class' => 'table table-striped-custom table-custom',
  ],
  'rowOptions' => function($model, $key, $index, $grid) {
    return ['class' => $index % 2 == 1 ? 'even' : ''];
  },
  'columns' => [
    [
      'label' => Yii::_t('sources.sources_id'),
      'attribute' => 'id',
      'contentOptions' => [
        'data-label' => Yii::_t('sources.sources_id'),
      ]
    ],
    [
      'label' => Yii::_t('sources.sources_name'),
      'attribute' => 'url',
      'contentOptions' => [
        'data-label' => Yii::_t('sources.sources_name'),
      ]
    ],
    [
      'label' => Yii::_t('sources.sources_category'),
      'value' => function($model) {
        return $model->getCurrentCategoryName();
      },
      'contentOptions' => [
        'data-label' => Yii::_t('sources.sources_category'),
      ]
    ],
    [
      'label' => Yii::_t('sources.sources_monetization'),
      'value' => function($model) {
        return $model->getDefaultProfitTypeName();
      },
      'contentOptions' => [
        'data-label' => Yii::_t('sources.sources_monetization'),
      ]
    ],
    [
      'label' => Yii::_t('sources.sources_format'),
      'value' => function($model) {
        $ads = $model->getAdsType();
        return $ads ? $ads->name : null;
      },
      'contentOptions' => [
        'data-label' => Yii::_t('sources.sources_format'),
      ]
    ],
    [
      'label' => Yii::_t('sources.sources_status'),
      'format' => 'raw',
      'value' => function($model) {
        return $model->getStatus() . (
        $model->isDisabled() && $model->reject_reason
          ? Html::tag('i', '', ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => $model->reject_reason, 'class' => 'icon-question'])
          : '');
      },
      'contentOptions' => function ($model, $key, $index, $column) {
        $options = [
          'data-label' => Yii::_t('sources.sources_status'),
        ];
        if ($model->isDeclined()) {
          $options['class'] = 'status__fail';
        }
        if ($model->isEnabled()) {
          $options['class'] = 'status__ok';
        }

        return $options;
      }
    ],
    [
      'class' => Column::class,
      'content' => function ($model, $key, $index, $column) {
        $buttons = [];

        if (!$model->isBlocked()) {
          $buttons += [
            Link::get("", ["#" => ''], [
              'class' => 'code',
              'data-source' => $model->id,
              'data-action' => Url::to(['code']),
              'role' => 'button',
            ], '<i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('sources.sources_code_bt') . '" class="icon-code"></i>'),
            Link::get("", ["#" => ''], [
              'class' => 'settings',
              'data-source' => $model->id,
              'data-action' => Url::to(['settings']),
              'role' => 'button',
            ], '<i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('sources.sources_settings_bt') . '" class="icon-options"></i>'),
          ];
        }

        $buttons[] = Link::get("delete", ["id" => $model->id], [
            'data-pjax' => 0,
            'data-confirm' => Yii::_t('sources.source_confirm_delete'),
          ], '<i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('sources.sources_delete_bt') . '" class="icon-delete"></i>');

        return implode(' ', $buttons);
      },
      'contentOptions' => function ($model, $key, $index, $column) {
        /**
         * TRICKY этот блок не показывается на мобильной версии, потому что раскрывающиеся блоки не адаптированы под нее
         */
        return ['class' => 'table-collapse_btn hidden-xs'];
      }
    ]
  ]
]) ?>
