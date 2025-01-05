<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use kartik\editable\Editable;
use kartik\editable\EditableAsset;
use kartik\editable\EditablePjaxAsset;
use kartik\popover\PopoverXAsset;
use mcms\common\widget\Select2;
use mcms\common\widget\modal\Modal;
use mcms\promo\assets\ArbitrarySourcesIndexAssets;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\Source;
use kartik\date\DatePicker;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\ActiveFormAsset;
use yii\widgets\Pjax;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html as OurHtml;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var mcms\promo\models\search\SourceSearch $searchModel
 * @var \yii\data\ArrayDataProvider $streamNamesData
 */

$this->title = Yii::_t('smart_links.main');
ArbitrarySourcesIndexAssets::register($this);
EditableAsset::register($this);
EditablePjaxAsset::register($this);
PopoverXAsset::register($this);
ActiveFormAsset::register($this);
?>

<?php ContentViewPanel::begin([
    'padding' => false,
]);
?>

<?php Pjax::begin(['id' => 'smart_links-pjax']); ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'id' => 'smartLinksGrid',
  'export' => false,
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '50px'
    ],
    [
      'class' => 'kartik\grid\ExpandRowColumn',
      'header' => false,
      'allowBatchToggle' => false,
      'value' => function ($model, $key, $index, $column) {
        return AdminGridView::ROW_COLLAPSED;
      },
      'detailUrl' => Url::to(['view']),
      'detailOptions'=>[
        'class'=> 'kv-state-enable',
      ],
      'visible' => OurHtml::hasUrlAccess(['/promo/arbitrary-sources/view/'])
    ],
    [
      'attribute' => 'name',
      'contentOptions' => ['style'=>'max-width: 150px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'link',
      'width' => '100px'
    ],
    [
      'attribute' => 'user_id',
      'vAlign'=> 'middle',
      'width' => '200px',
      'format' => 'raw',
      'filter' => UserSelect2::widget([
          'model' => $searchModel,
          'attribute' => 'user_id',
          'initValueUserId' => $searchModel->user_id,
          'options' => [
            'id' => 'user-select2-id',
            'placeholder' => '',
          ],
        ]
      ),
      'value' => 'userLink',
      'enableSorting' => false,
      'contentOptions' => ['style'=>'max-width: 200px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'stream.name',
      'label' => Yii::_t('promo.sources.attribute-stream_id'),
      'filter' => Select2::widget([
        'model' => $searchModel,
        'attribute' => 'stream_id',
        'data' => $streamNamesData,
        'options' => [
          'placeholder' => '',
        ],
        'pluginOptions'=> [
          'allowClear' => true,
          'width' => '120px'
        ]
      ]),
      'format' => 'raw',
      'value' => 'streamLink',
      'width' => '120px',
      'contentOptions' => ['style'=>'max-width: 120px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'addPrelandOperatorNames',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'addPrelandOperators',
        'options' => [
          'multiple' => true
        ],
        'useSelect2' => true,
      ]),
      'width' => '170px',
      'contentOptions' => ['style'=>'max-width: 170px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'offPrelandOperatorNames',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'offPrelandOperators',
        'options' => [
          'multiple' => true
        ],
        'useSelect2' => true,
      ]),
      'width' => '170px',
      'contentOptions' => ['style'=>'max-width: 170px; overflow: auto; word-wrap: break-word;'],
    ],
    [
      'attribute' => 'blockedOperatorNames',
      'format' => 'raw',
      'filter' => OperatorsDropdown::widget([
        'model' => $searchModel,
        'attribute' => 'blockedOperators',
        'options' => [
          'multiple' => true
        ],
        'useSelect2' => true,
      ]),
      'width' => '170px',
      'contentOptions' => ['style'=>'max-width: 170px; overflow: auto; word-wrap: break-word;'],
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
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom', 'autoclose' => true]
      ]),
      'width' => '200px',
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{update}',
    ],
  ]
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>