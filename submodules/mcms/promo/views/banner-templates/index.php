<?php

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use yii\widgets\Pjax;
use mcms\common\helpers\Html;
use yii\helpers\Url;
use mcms\promo\models\search\BannerTemplateSearch;


/* @var $this yii\web\View */
/* @var $searchModel mcms\promo\models\search\BannerTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->beginBlock('actions');
echo Html::a(
  '<i class="glyphicon glyphicon-plus"></i> ' . BannerTemplateSearch::translate('create'),
  BannerTemplateSearch::getCreateLink(),
  ['class' => 'btn btn-success']
);
$this->endBlock();

$searchFormName = (new BannerTemplateSearch)->formName();

?>

<?= Html::beginTag('section',['id'=>'widget-grid']);
ContentViewPanel::begin([
  'header' => $this->title,
  'padding' => false,
]);
?>
<div class="category-index">
  <?php Pjax::begin(); ?>
  <?= AdminGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
      [
        'attribute' => 'id',
        'contentOptions' => ['style' => 'width : 110px']
      ],
      'name',
      'code',
      [
        'header' => BannerTemplateSearch::translate('banners'),
        'format' => 'raw',
        'value' => function ($model) use ($searchFormName) {
          /** @var $model \mcms\promo\models\BannerTemplate */
          return Html::a(
            BannerTemplateSearch::translate('banners') . ' <span class="badge">' . $model->getActivePagesCount() . '</span>',
            ['banners/index', $searchFormName => ['template_id' => $model->id]],
            ['data-pjax' => 0]
          );
        }
      ],
      [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => '{create} {delete}',
        'buttons' => [
          'create' => function ($url, $template) {

            if (!Yii::$app->user->can('PromoBannerTemplatesCreate')) return '';

            /* @var $template mcms\promo\models\BannerTemplate */
            return \yii\helpers\Html::a(\yii\bootstrap\Html::icon('pencil'), Url::to($template::getCreateLink($template->id)), [
              'title' => Yii::t('yii', 'Update'),
              'aria-label' => Yii::t('yii', 'Update'),
              'data-pjax' => '0',
              'class' => 'btn btn-xs btn-default'
            ]);
          }
        ]
      ],
    ],
    'export' => false,
  ]); ?>
  <?php Pjax::end(); ?>
</div>
<?php ContentViewPanel::end() ?>
<?= Html::endTag('section');?>