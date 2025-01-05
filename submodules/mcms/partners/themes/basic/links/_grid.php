<?php

use yii\helpers\Url;
use yii\grid\GridView;
use yii\grid\Column;
use mcms\common\helpers\Link;
use mcms\common\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/* @var mcms\common\web\View $this */
?>

<?= GridView::widget([
  'layout' => '{items}{pager}',
  'dataProvider' => $sourcesDataProvider,
  'tableOptions' => [
    'id' => 'links-grid-table',
    'class' => 'table table-striped-custom table-custom',
  ],
  'rowOptions' => function($model, $key, $index, $grid) { /* @var mcms\promo\models\Source $model*/
    return ['class' => ($index % 2 == 1 ? 'even' : '') . ($model->isBlocked() || (!$model->isSmartLink() && $model->stream && $model->stream->isDisabled()) ? ' disabled' : '')];
  },
  'formatter' => ['class' => 'yii\i18n\Formatter','nullDisplay' => ''],
  'columns' => [
    [
      'label' => Yii::_t('links.link_id'),
      'attribute' => 'id',
      'contentOptions' => [
        'data-label' => Yii::_t('links.link_id'),
      ]
    ],
    [
      'label' => Yii::_t('links.name'),
      'attribute' => 'name',
      'contentOptions' => [
        'data-label' => Yii::_t('links.name'),
      ]
    ],
    [
      'label' => Yii::_t('main.landings'),
      'value' => function($model) { /* @var mcms\promo\models\Source $model*/
        $result = '';
        if ($model->isSmartLink()) {
          // TODO: выпилить после того, как начнем получать рейтинг лендов из ML. Всем писать All
          foreach ($model->getSmartLinkCountriesOperators() as $country) {
            $result .= '<div class="table-country-oss"><span>' . $country['name'] . ': </span> <ul>';
            foreach ($country['operators'] as $operator) {
              $result .= Html::tag('li', $operator['name']) . ' ';
            }
            $result .= '</ul></div>';
          }
          return $result;
          //TODO: раскомментить после того, как начнем получать рейтинг лендов из ML
          //return Yii::_t('links.all_landings');
        }

        foreach ($model->getListOperatorLandings() as $country) {
          $result .= '<div class="table-country-oss"><span>' . $country['name'] . ': </span> <ul>';
          foreach ($country['operators'] as $operator) {
            if (!$model->isStatusModeration() && ArrayHelper::getValue($operator, 'moderation') || ArrayHelper::getValue($operator, 'disabled') || ArrayHelper::getValue($operator, 'blocked') || ArrayHelper::getValue($operator, 'locked')) {
              if ($link = ArrayHelper::getValue($operator, 'blocked')) {
                $result .= $model->getOperatorTooltip($operator, Yii::_t('links.traffic') . ' ' . Yii::_t('links.traffic_blocked') . ($link->operator_blocked_reason ? ': ' . $link->operator_blocked_reason : ''), 'label-red');
                continue;
              }
              if (ArrayHelper::getValue($operator, 'moderation')) {
                $result .= $model->getOperatorTooltip($operator, Yii::_t('links.landing_on_moderation', ['n' => count($operator['moderation'])]), 'label-orange');
                continue;
              }
              if (ArrayHelper::getValue($operator, 'locked')) {
                $result .= $model->getOperatorTooltip($operator, Yii::_t('links.landing_locked', ['n' => count($operator['locked'])]), 'label-red');
                continue;
              }
              if (ArrayHelper::getValue($operator, 'disabled')) {
                $result .= $model->getOperatorTooltip($operator, Yii::_t('links.landing_disabled', ['n' => count($operator['disabled'])]), 'label-red');
                continue;
              }
              continue;
            }
            $result .=
              $model->isStatusModeration()
                ? $model->getOperatorTooltip($operator, Yii::_t('links.landings_on_moderation'), 'label-orange')
                : Html::tag('li', $operator['name'] . ' ' . Html::tag('i', count($operator['landings'])))
              ;
          }
          $result .= '</ul></div>';
        }
        return $result;
      },
      'format' => 'raw',
      'contentOptions' => [
        'data-label' => Yii::_t('main.landings'),
      ]
    ],
    [
      'label' => Yii::_t('links.stream'),
      'value' => function($model) {
        return ($model->stream) ? $model->stream->name : '';
      },
      'contentOptions' => [
        'data-label' => Yii::_t('links.stream'),
      ]
    ],
//    [
//      'label' => Yii::_t('main.tb_sale'),
//      'value' => function($model) {
//        return $model->is_trafficback_sell ? '<i class="icon-checked"></i>' : '';
//      },
//      'format' => 'raw',
//      'contentOptions' => function ($model, $key, $index, $column) {
//        return ['class' => 'sell_tv'];
//      }
//    ],
    [
      'class' => Column::class,
      'content' => function ($model, $key, $index, $column) { /* @var mcms\promo\models\Source $model*/
        if ($model->isSmartLink() && $model->isNewRecord) {
          return Link::get('/partners/smart-links/activate/', [], [
            'data-pjax' => 0,
            'data-confirm' => Yii::_t('links.question-activate'),
          ], '<i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('links.activate') . '" class="icon-link"></i>');

        }
        if ($model->isSmartLink()) {
          $buttons = [
            Link::get('/partners/smart-links/update/', ['id' => $model->id], [
              'data-pjax' => 0,
            ], '<i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('links.edit_link') . '" class="icon-edit"></i>'),
            Html::tag('span', ' <i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('sources.sources_code_bt') . '" class="icon-link"></i>', [
              'class' => 'code',
              'data-source' => $model->id,
              'data-action' => Url::to(['get-link']),
              'role' => 'button',
            ])
          ];
          return implode(' ', $buttons);
        }
        $buttons = [];

        if (!$model->isBlocked()) {
          $buttons = $buttons + [
            Link::get('add', ['id' => $model->id, '#' => 'step_1'], [
              'data-blocked' => $model->isBlockedString(),
              'data-pjax' => 0,
              'class' => 'edit-button',
            ], ' <i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('links.edit_link') . '" class="icon-edit"></i>'),
            Link::get('add', ['id' => $model->id, '#' => $model->stream->isEnabled() ? 'step_2' : 'step_1'], [
              'data-blocked' => $model->isBlockedString(),
              'data-pjax' => 0,
              'class' => 'edit-button',
            ], ' <i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('links.choose_landing') . '" class="icon-box"></i>'),
            Html::tag('span', ' <i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('sources.sources_code_bt') . '" class="icon-link"></i>', [
              'class' => 'code',
              'data-source' => $model->id,
              'data-action' => Url::to(['get-link']),
              'role' => 'button',
            ]),
            Link::get("copy", ["id" => $model->id], [
              'data-pjax' => 0,
              'data-confirm' => Yii::_t('links.link_confirm_copy'),
            ], '<i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('links.copy_link') . '" class="icon-blank"></i>')
          ];
        } else {
          $buttons = $buttons + [
              Html::tag('span',
                Html::tag('i', '', [
                  'data-toggle' => 'tooltip',
                  'data-placement' => 'top',
                  'title' => $model->reject_reason,
                  'class' => 'icon-dismiss',
                ]) . ' ' . Yii::_t('links.blocked'),
                [
                  'class' => 'link_blocked'
                ])
            ];
        }

        $buttons[] = Link::get("delete", ["id" => $model->id], [
            'data-pjax' => 0,
            'data-confirm' => Yii::_t('links.link_confirm_delete'),
          ], '<i data-toggle="tooltip" data-placement="top" title="' . Yii::_t('links.delete_link') . '" class="icon-delete"></i>');

        return implode(' ', $buttons);
      },
      'contentOptions' => function ($model, $key, $index, $column) {
        return ['class' => 'table-collapse_btn btn_fix'];
      }
    ]
  ]
]) ?>
