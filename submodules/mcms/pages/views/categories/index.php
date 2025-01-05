<?php

use mcms\common\widget\AdminGridView;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Html;
use yii\helpers\Url;
use mcms\pages\models\PageSearch;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html as HtmlLink;


/* @var $this yii\web\View */
/* @var $searchModel mcms\pages\models\search\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->render('actions');

$this->beginBlock('actions');
echo isset($this->blocks['create_button']) ? $this->blocks['create_button'] : '';
$this->endBlock();

$pageSearchFormName = (new PageSearch)->formName();

?>

<?php ContentViewPanel::begin([
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
        'header' => Yii::_t('pages.main.pages'),
        'format' => 'raw',
        'value' => function($model) use ($pageSearchFormName) {
          /** @var $model \mcms\pages\models\Category */
          return HtmlLink::a(
            Yii::_t('pages.main.pages') . ' <span class="badge">' . $model->getActivePagesCount() . '</span>',
            ['pages/index', $pageSearchFormName => ['page_category_id' => $model->id]],
            ['data-pjax' => 0]
          );
        }
      ],
      [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => '{create} {delete}',
        'buttons' => [
          'create' => function ($url, $category) {
            /* @var $category mcms\pages\models\Category */
            return HtmlLink::a(Html::icon('pencil'), $category::getCreateLink($category->id), [
              'title' => Yii::t('yii', 'Update'),
              'aria-label' => Yii::t('yii', 'Update'),
              'data-pjax' => '0',
              'class' => 'btn btn-xs btn-default'
            ]);
          }
        ]
      ],
    ],
  ]); ?>
  <?php Pjax::end(); ?>
</div>
<?php ContentViewPanel::end() ?>