<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\AjaxRequest;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\Landing;
use mcms\promo\models\SourceOperatorLanding;
use yii\bootstrap\Html;
use yii\helpers\Url;
use kartik\select2\Select2Asset;
use yii\widgets\Pjax;


Select2Asset::register($this);

?>

<?php Pjax::begin(['id' => 'webmaster-sources-landings-list']) ?>
<?php ContentViewPanel::begin([
  'padding' => false,
  'header' => Yii::_t('promo.landings.main'),
  'buttons' => [],
  'toolbar' => Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'id' => 'show-shortcut',
      'class' => 'btn btn-success btn-xs',
      'label' => Html::icon('plus') . ' ' . Yii::_t('promo.landing_set_items.add-landing')
    ],
    'url' => ['/promo/webmaster-sources/add-landing/', 'sourceId' => $model->id],
  ])
]);
?>
<?= AdminGridView::widget([
  'dataProvider' => $model->landingModels,
  'export' => false,
  'tableOptions' => [
    'class' => 'container-items',
  ],
  'rowOptions' => function ($model) {
    // Неактивный или заблокированный - красный
    if ($model->landing->isDisabled()) {
      return ['class' => 'danger'];
    }
    // Активный и скрытый/по запросу - желтый
    if ($model->landing->isEnabled() && $model->landing->isHiddenByRequest()) {
      return ['class' => 'warning'];
    }

    return [];
  },
  'columns' => [
    [
      'attribute' => 'operator_id',
      'label' => Yii::_t('promo.landings.operator-attribute-operator_id'),
      'format' => 'raw',
      'value' => function ($item) {
        return $item->operator->getViewLink();
      },
    ],
    [
      'attribute' => 'landing_id',
      'label' => Yii::_t('promo.landings.operator-attribute-landing_id'),
      'format' => 'raw',
      'value' => function ($item) use ($model) {
        /** @var SourceOperatorLanding $item */
        $link = '';
        if ($item->isLandingUnblocked()) {
          // Если есть одобренная заявка на разблокировку лендинга, показываем ссылку на блокировку
          $link = Html::a(Yii::_t('promo.landing_unblock_requests.lock-landing'), [
            '/promo/webmaster-sources/lock-landing/',
            'id' => $item->id
          ], [
            'data-pjax' => 0,
            'data-confirm-text' => Yii::_t('promo.landing_unblock_requests.lock-landing-question'),
            'class' => 'ajax-request text-danger',
          ]);
        } else if ($item->isLandingBlocked()) {
          // Если нет одобренной заявки на разблокировку лендинга, показываем ссылку на разблокировку
          $link = Html::a(Yii::_t('promo.landing_unblock_requests.unlock-landing'), [
            '/promo/webmaster-sources/unlock-landing/',
            'id' => $item->id
          ], [
            'data-pjax' => 0,
            'data-confirm-text' => Yii::_t('promo.landing_unblock_requests.unlock-landing-question'),
            'class' => 'ajax-request text-success',
          ]);
        }

        return $item->landing->getViewLink() . ' ' . $link;
      }
    ],
    [
      'attribute' => 'profit_type',
      'label' => Yii::_t('promo.sources.attribute-default_profit_type'),
      'format' => 'raw',
      'value' => function ($item) {
        return $item->profitTypeName;
      }
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update-modal} {delete-landing}',
      'buttonsPath' => [
        'update-modal' => '/promo/webmaster-sources/update-landing/',
        'delete-landing' => '/promo/webmaster-sources/delete-landing/',
      ],
      'buttons' => [
        'update-modal' => function ($url, $item, $key) use ($model) {
          return Modal::widget([
            'toggleButtonOptions' => [
              'tag' => 'a',
              'class' => 'update-landing btn btn-xs btn-default',
              'label' => Html::icon('pencil'),
              'data-pjax' => 0,
            ],
            'url' => [
              '/promo/webmaster-sources/update-landing/',
              'sourceId' => $model->id,
              'key' => $key,
              'landingId' => $item->landing_id,
              'operatorId' => $item->operator_id,
              'profitType' => $item->profit_type,
            ],
          ]);
        },
        'delete-landing' => function ($url, $item, $key) use ($model) {
          return AjaxRequest::widget([
            'pjaxId' => '#webmaster-sources-landings-list',
            'useAccessControl' => false,
            'title' => '<span class="glyphicon glyphicon-trash"></span>',
            'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'options' => [
              'tag' => 'a',
              'class' => 'btn btn-xs btn-default',
              'label' => Html::icon('pencil'),
              'data-pjax' => 0,
            ],
            'url' => [
              '/promo/webmaster-sources/delete-landing/',
              'sourceId' => $model->id,
              'key' => $key,
              'landingId' => $item->landing_id,
              'operatorId' => $item->operator_id,
              'profitType' => $item->profit_type,
            ]
          ]);
        }
      ]
    ],
  ]
]); ?>
<?php ContentViewPanel::end() ?>
<?php Pjax::end() ?>