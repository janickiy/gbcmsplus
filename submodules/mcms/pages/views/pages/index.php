<?php
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AdminGridView;
use mcms\common\widget\modal\Modal;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\widgets\ListView;
use kartik\date\DatePicker;
use mcms\pages\models\Category;
use mcms\common\helpers\Html;
use mcms\pages\models\PageSearch;

/** @var $categoriesDataProvider \yii\data\ActiveDataProvider */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $searchModel PageSearch */

$this->render('actions', ['id' => false]);
$this->beginBlock('actions');
if (isset($this->blocks['categories_button'])) {
  echo $this->blocks['categories_button'];
}
$this->endBlock();

$formName = (new PageSearch())->formName();
?>

<div class="row">

    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title pull-left"><?= Category::translate('list') ?></h3>
                <div class="clearfix"></div>
            </div>
          <?= ListView::widget([
            'layout' => '{items}',
            'dataProvider' => $categoriesDataProvider,
            'itemOptions' => ['tag' => false],
            'options' => ['class' => 'list-group'],
            'itemView' => function ($model, $key, $index, $widget) use ($formName, $searchModel) {
              /** @var $model Category */
              $text = Html::tag('span', $model->getActivePagesCount(), ['class' => 'badge']) . $model->name;
              $url = ['', $formName => ['page_category_id' => $model->id]];
              return Html::a($text, $url, [
                'class' => 'list-group-item' . ($searchModel->page_category_id == $model->id ? ' active' : ''),
              ]);
            },
          ]); ?>
        </div>
    </div>
    <div class="col-md-9">
      <?php $category = $searchModel->getCategory()->one(); ?>
      <?php ContentViewPanel::begin([
        'header' => $category->name,
        'padding' => false,
        'toolbar' => $category
          ? (Html::a(
              '<i class="fa fa-cog"></i> ' .
              Category::translate('update'),
              Category::getCreateLink($category->id),
              ['class' => 'btn btn-default btn-labeled', 'data-pjax' => 0]) .
            Html::a(
              '<i class="fa fa-plus"></i> ' .
              Yii::_t('main.create_page'),
              ['create', 'categoryId' => $category->id],
              ['class' => 'btn btn-success btn-labeled', 'data-pjax' => 0]
            ))
          : null,
      ]);
      ?>
      <?php Pjax::begin([
        'id' => 'pages-pjax',
      ]); ?>
      <?php if ($searchModel->page_category_id): ?>
        <?= AdminGridView::widget([
          'id' => 'pages-grid',
          'dataProvider' => $dataProvider,
          'filterModel' => $searchModel,
          'export' => false,
          'resizableColumns' => false,
          'columns' => [
            'id',
            'name',
            [
              'attribute' => 'is_disabled',
              'class' => '\kartik\grid\BooleanColumn',
              'trueLabel' => Yii::_t("pages.main.no"),
              'falseLabel' => Yii::_t("pages.main.yes"),
              'trueIcon' => GridView::ICON_INACTIVE,
              'falseIcon' => GridView::ICON_ACTIVE,
            ],
            'sort',
            [
              'class' => 'mcms\common\grid\ActionColumn',
              'template' => '{update} {view} {disable} {enable} {delete}',
              'contentOptions' => ['style' => 'min-width : 110px'],
              'buttons' => [
                'view' => function ($url) {
                  return Modal::widget([
                    'toggleButtonOptions' => [
                      'tag' => 'a',
                      'label' => \yii\bootstrap\Html::icon('eye-open'),
                      'title' => Yii::t('yii', 'View'),
                      'class' => 'btn btn-xs btn-default',
                      'data-pjax' => 0,
                    ],
                    'url' => $url,
                  ]);
                },
              ],
            ],
          ],
        ]); ?>
      <?php else: ?>
          <code><?= Yii::_t('main.category_not_selected') ?></code>
      <?php endif; ?>
      <?php Pjax::end(); ?>
      <?php ContentViewPanel::end() ?>
    </div>

</div>



