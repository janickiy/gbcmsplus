<?php

use kartik\helpers\Html;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\UserSelect2;
use mcms\common\widget\modal\Modal;
use mcms\payments\models\Company;
use mcms\payments\models\PartnerCompany;
use yii\helpers\Url;
use kartik\date\DatePicker;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel mcms\payments\models\search\PartnerCompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::_t('payments.partner-companies.title');
?>
<?php $this->beginBlock('actions'); ?>
<?= Modal::widget([
  'toggleButtonOptions' => [
    'tag' => 'a',
    'label' => Html::icon('plus') . ' ' . Yii::_t('payments.partner-companies.create'),
    'class' => 'btn btn-success',
    'data-pjax' => 0,
  ],
  'url' => Url::to(['/payments/partner-companies/create']),
]) ?>
<?php $this->endBlock() ?>

<?php ContentViewPanel::begin([
  'padding' => false,
]);
?>
<?php Pjax::begin(['id' => 'partnerCompaniesPjaxGrid', 'options' => ['class' => 'pjax-container']]); ?>

<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'filterModel' => $searchModel,
  'columns' => [
    [
      'attribute' => 'id',
      'width' => '10px',
    ],
    'name',
    [
      'attribute' => 'userIds',
      'format' => 'raw',
      'filter' => UserSelect2::widget(
        [
          'model' => $searchModel,
          'attribute' => 'userId',
          'initValueUserId' => $searchModel->userId,
          'options' => [
            'id' => 'user-select2-id',
            'placeholder' => '',
          ],
        ]
      ),
      'value' => 'userLink',
      'enableSorting' => false,
      'contentOptions' => ['style' => 'max-width: 200px; overflow: auto; word-wrap: break-word;'],
    ],
    'country',
    'address',
    'city',
    'post_code',
    [
      'attribute' => 'agreement',
      'format' => 'raw',
      'value' => function ($model) {
        return $model->agreement
          ? Html::a(
            Yii::_t('payments.partner-companies.download'),
            ['/payments/partner-companies/get-agreement/', 'id' => $model->id, 't' => time()],
            ['style' => 'max-width:100%; max-height:100%;',  'data-pjax' => 0]
          )
          : null;
      }
    ],
    [
      'attribute' => 'reseller_company_id',
      'format' => 'raw',
      'value' => function ($model) {
        if (!$model->resellerCompany) {
          return null;
        }
        /** @var PartnerCompany $model */
        return $model->resellerCompany->name;
      },
      'filter' => Company::getDropdownList(),
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
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom']
      ]),
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{view-modal} {update-modal} {delete}',
    ],
  ],
]); ?>
<?php Pjax::end(); ?>

<?php ContentViewPanel::end() ?>
