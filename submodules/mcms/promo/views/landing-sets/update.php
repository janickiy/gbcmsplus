<?php
/** @var \yii\web\View $this */
/** @var \mcms\promo\models\LandingSet $model */
/** @var \mcms\promo\models\search\LandingSetItemSearch $landingsSearchModel */
/** @var \yii\data\ActiveDataProvider $landingsDataProvider */
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\grid\ActionColumnAsset;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\AjaxRequest;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\Select2;
use mcms\promo\components\widgets\LandingsDropdown;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingSetItem;
use yii\bootstrap\Html as BHtml;
use yii\helpers\Url;
use yii\widgets\Pjax;
use mcms\common\widget\UserSelect2;
use mcms\promo\models\Source;

$langPrefix = 'promo.landing_sets.';
?>

<div class="row">
  <!-- Управление лендингами -->
  <div class="col-sm-7">
    <?php Pjax::begin(['id' => 'landings-list']) ?>
    <?php ContentViewPanel::begin([
      'header' => Yii::_t($langPrefix . 'landings'),
      'buttons' => [],
      'padding' => false,
      'toolbar' => $model->canManageManual()
        ? Modal::widget([
          'toggleButtonOptions' => [
            'tag' => 'a',
            'label' => BHtml::icon('plus') . ' ' . Yii::_t('promo.landing_set_items.add-landing'),
            'class' => 'btn btn-xs btn-success',
            'data-pjax' => 0,
          ],
          'url' => ['landing-set-items/create-modal', 'setId' => $model->id],
        ])
        : null,
    ]) ?>
    <?php if (!$model->canManageManual()) { ?>
      <div class="alert alert-warning"><?= Yii::_t($langPrefix . 'landings-manual-manage-forbidden') ?></div>
    <?php } ?>
    <?php $columns = [
      [
        'attribute' => 'id',
        'contentOptions' => ['style' => 'width: 80px'],
      ],
      [
        'attribute' => 'operator_id',
        'format' => 'raw',
        'value' => 'operatorLink',
        'filter' => OperatorsDropdown::widget([
          'model' => $landingsSearchModel,
          'attribute' => 'operator_id',
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => ['allowClear' => true],
          'useSelect2' => true
        ]),
      ],
      [
        'attribute' => 'landing_id',
        'format' => 'raw',
        'value' => 'landingLink',
        'filter' => LandingsDropdown::widget([
          'model' => $landingsSearchModel,
          'attribute' => 'landing_id',
          'options' => [
            'placeholder' => '',
          ],
          'pluginOptions' => ['allowClear' => true],
          'useSelect2' => true
        ]),
      ],
      [
        'attribute' => 'is_enabled',
        'class' => '\kartik\grid\BooleanColumn',
        'trueLabel' => Yii::_t($langPrefix . 'status-active'),
        'falseLabel' => Yii::_t($langPrefix . 'status-inactive'),
        'filterWidgetOptions' => [
          'pluginOptions' => [
            'allowClear' => true
          ],
          'options' => [
            'placeholder' => '',
          ],
        ],
      ],
    ];

    if ($model->canManageManual()) {
      $columns[] = [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => '{update-modal} {enable} {disable} {delete}',
        'contentOptions' => ['class' => 'col-min-width-100'],
        'controller' => 'promo/landing-set-items',
      ];
    }
    ?>
    <?= AdminGridView::widget([
      'dataProvider' => $landingsDataProvider,
      'filterModel' => $landingsSearchModel,
      'export' => false,
      'rowOptions' => function ($model) {
        /** @var LandingSetItem $model */
        // Неактивный или заблокированный - красный
        if ($model->landing->isDisabled()) {
          return ['class' => 'danger'];
        }
        // Активный и скрытый/по запросу - желтый
        if ($model->landing->isEnabled() && $model->landing->isHiddenByRequest()) {
          return ['class' => 'warning'];
        }

        // Элемент набора неактивен - выделаем красным
        return $model->isDisabled() ? ['class' => 'danger'] : [];
      },
      'columns' => $columns,
    ]); ?>
    <?php ContentViewPanel::end() ?>
    <?php Pjax::end() ?>
  </div>
  <!-- /Управление лендингами -->

  <div class="col-sm-5">
    <!-- Параметры набора -->
    <div class="col-sm-12">
      <?php ContentViewPanel::begin(['header' => Yii::_t($langPrefix . 'params'), 'buttons' => []]) ?>
      <?php $form = AjaxActiveKartikForm::begin([
        'ajaxSuccess' => Modal::ajaxSuccess('#landings-list'),
        'ajaxComplete' => 'function() { ModalWidget.empty("#modalWidget") }',
      ]) ?>
      <?= $this->render('_params-form-content', ['model' => $model, 'form' => $form]) ?>
      <div class="row">
        <div class="col-sm-6">
          <?php if ($model->category_id): ?>
          <?= Html::a(
              BHtml::icon('refresh', ['prefix' => 'fa fa-']) . ' ' . Yii::_t('promo.landing_sets.landings-update'),
            ['/promo/landing-sets/update-landings/', 'id' => $model->id],
            [
              'class' => 'pull-left btn btn-primary',
              AjaxButtons::CONFIRM_ATTRIBUTE => Yii::_t('promo.webmaster_sources.are-you-shure'),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
            ]
          ) ?>
          <?php endif; ?>
        </div>
        <div class="col-sm-6">
          <?= Html::submitButton(
            BHtml::icon('save', ['prefix' => 'fa fa-']) . ' ' . Yii::_t($model->isNewRecord ? 'app.common.Create' : 'app.common.Save'),
            ['class' => 'pull-right btn btn-' . ($model->isNewRecord ? 'success' : 'primary')]
          ) ?>
        </div>
      </div>
      <?php AjaxActiveKartikForm::end() ?>
      <?php ContentViewPanel::end() ?>
    </div>
    <!-- /Параметры набора -->

    <!-- Управление источниками -->
    <div class="col-sm-12">
      <?php ContentViewPanel::begin([
        'padding' => false,
        'header' => Yii::_t($langPrefix . 'sources'),
        'buttons' => [],
        'toolbar' => Modal::widget([
          'toggleButtonOptions' => [
            'tag' => 'a',
            'title' => Yii::t('yii', 'Off'),
            'label' => BHtml::icon('plus') . ' ' . Yii::_t($langPrefix . 'add-source'),
            'class' => 'btn btn-xs btn-success',
            'data-pjax' => 0,
          ],
          'url' => ['/promo/landing-sets/link-source/', 'id' => $model->id],
        ])
      ]) ?>
      <?php Pjax::begin(['id' => 'webmaster-sources-pjax']); ?>
      <?= AdminGridView::widget([
        'dataProvider' => $sourcesDataProvider,
        'filterModel' => $sourcesSearchModel,
        'id' => 'webmasterSourcesGrid',
        'rowOptions' => function ($model) {
          /* @var Source $model */
          return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
        },
        'export' => false,
        'columns' => [
          [
            'attribute' => 'url',
            'format' => 'raw',
            'value' => function ($model) {
              /** @var Source $model */
              return Html::a($model->url, $model->getUpdateRoute(), ['data-pjax' => 0]);
            },
            'contentOptions' => ['style' => 'max-width: 180px;word-wrap: break-word;']
          ],
          [
            'attribute' => 'user_id',
            'vAlign' => 'middle',
            'format' => 'raw',
            'width' => '200px',
            'filter' => UserSelect2::widget([
                'model' => $sourcesSearchModel,
                'attribute' => 'user_id',
                'initValueUserId' => $sourcesSearchModel->user_id,
                'options' => [
                  'placeholder' => '',
                ],
              ]
            ),
            'value' => 'userLink',
            'enableSorting' => false,
            'contentOptions' => ['style' => 'max-width: 200px; overflow: auto; word-wrap: break-word;'],
          ],
          [
            'class' => 'mcms\common\grid\ActionColumn',
            'template' => '{sync} {autosync-enable} {autosync-disable} {delete}',
            'contentOptions' => ['class' => 'col-min-width-100'],
            'buttonsPath' => [
              'sync' => '/promo/webmaster-sources/landing-sets-sync',
              'autosync-enable' => '/promo/webmaster-sources/landing-sets-autosync-enable',
              'autosync-disable' => '/promo/webmaster-sources/landing-sets-autosync-disable',
              'delete' => '/promo/landing-sets/unlink-source/',
            ],
            'buttons' => [
              'sync' => function ($url, $model) {
                return AjaxRequest::widget([
                  'url' => $url,
                  'title' => BHtml::icon('refresh'),
                  'pjaxId' => '#webmaster-sources-pjax',
                  'useAccessControl' => false,
                  'confirm' => Yii::_t('promo.sources.are-you-sure-want-to-update-source'),
                  'options' => [
                    'title' => Yii::_t('promo.sources.landing-set-sync'),
                    'class' => 'btn btn-xs btn-info',
                  ]
                ]);
              },
              'autosync-enable' => function ($url, $model) {
                return !$model->landing_set_autosync ? AjaxRequest::widget([
                  'url' => $url,
                  'title' => BHtml::icon('ok'),
                  'pjaxId' => '#webmaster-sources-pjax',
                  'useAccessControl' => false,
                  'options' => [
                    'title' => Yii::_t('promo.sources.attribute-landing_set_autosync'),
                    'class' => 'btn btn-xs btn-success',
                  ]
                ]) : null;
              },
              'autosync-disable' => function ($url, $model) {
                return $model->landing_set_autosync ? AjaxRequest::widget([
                  'url' => $url,
                  'title' => BHtml::icon('remove'),
                  'pjaxId' => '#webmaster-sources-pjax',
                  'useAccessControl' => false,
                  'options' => [
                    'title' => Yii::_t('promo.sources.attribute-landing_set_autosync'),
                    'class' => 'btn btn-xs btn-danger',
                  ]
                ]) : null;
              },
            ]
          ],
        ]
      ]); ?>
      <?php Pjax::end(); ?>
      <?php ContentViewPanel::end() ?>
    </div>
    <!-- /Управление источниками -->
  </div>
</div>