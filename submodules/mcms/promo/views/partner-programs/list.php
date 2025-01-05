<?php
use mcms\common\grid\ActionColumn;
use mcms\common\grid\TextColumn;
use kartik\widgets\DatePicker;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\promo\models\PartnerProgram;
use rgk\utils\widgets\AjaxButton;
use yii\bootstrap\Html as BHtml;
use mcms\promo\models\PersonalProfit;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \mcms\promo\models\search\PartnerProgramSearch $searchModel */
/** @var \yii\data\ActiveDataProvider $dataProvider */
?>
<?php $this->beginBlock('actions'); ?>

<?= BHtml::a(PersonalProfit::t('actualize-courses'), ['personal-profits/actualize-courses'], [
    'class' => 'btn btn-primary',
    AjaxButtons::CONFIRM_ATTRIBUTE => PersonalProfit::t('actualize-courses-confirm'),
    AjaxButtons::AJAX_ATTRIBUTE => 1
  ]); ?>

<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => BHtml::icon('plus') . ' ' . Yii::_t(PartnerProgram::LANG_PREFIX . 'create-program'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => ['create-modal'],
]) ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin(['padding' => false]) ?>
<?php Pjax::begin(); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'export' => false,
  'columns' => [
    [
      'attribute' => 'id',
      'contentOptions' => ['style' => 'width: 5%'],
    ],
    [
      'attribute' => 'name',
      'contentOptions' => ['style' => 'width: 20%'],
    ],
    [
      'attribute' => 'description',
      'contentOptions' => ['style' => 'width: 20%'],
      'class' => TextColumn::class,
    ],
    [
      'attribute' => 'created_at',
      'format' => 'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'createdFrom',
        'attribute2' => 'createdTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),
      'contentOptions' => ['style' => 'width: 20%'],
    ],
    [
      'attribute' => 'updated_at',
      'format' => 'datetime',
      'filter' => DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'updatedFrom',
        'attribute2' => 'updatedTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar"></i>',
        'pluginOptions' => [
          'format' => 'yyyy-mm-dd',
          'orientation' => 'bottom',
          'autoclose' => true,
        ]
      ]),
      'contentOptions' => ['style' => 'width: 20%'],
    ],
    [
      'class' => ActionColumn::class,
      'template' => '{update} {delete} {copy} {actualize-courses}',
      'contentOptions' => ['class' => 'col-min-width-100'],
      'buttonsPath' => [
        'actualize-courses' => '/promo/personal-profits/actualize-courses/'
      ],
      'buttons' => [
        'copy' => function ($url) {
          $options = [
            'class' => 'btn btn-xs btn-default',
            'title' => Yii::_t('app.common.Copy'),
            AjaxButtons::AJAX_ATTRIBUTE => 1,
            'data-pjax' => 0,
            AjaxButtons::CONFIRM_ATTRIBUTE => Yii::_t('partner_programs.are-you-sure-want-to-copy'),
          ];
          return BHtml::a(BHtml::icon('copy'), $url, $options);
        },
        'actualize-courses' => function ($url, $model) {
          $options = [
            'title' => PersonalProfit::t('actualize-courses'),
            'aria-label' => PersonalProfit::t('actualize-courses'),
            AjaxButton::CONFIRM_ATTRIBUTE => PersonalProfit::t('actualize-courses-confirm'),
            'data-pjax' => 0,
            AjaxButton::RELOAD_ATTRIBUTE => 1,
            'class' => 'btn btn-xs btn-primary'
          ];
          return AjaxButton::widget([
            'options' => $options,
            'text' => BHtml::icon('refresh'),
            'url' => Url::to(['personal-profits/actualize-courses', 'programId' => $model->id])
          ]);
        }
      ],
    ],
  ],
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();