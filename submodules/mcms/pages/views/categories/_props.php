<?php
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use yii\widgets\Pjax;
use mcms\pages\models\Category;
use yii\bootstrap\Html;
use yii\helpers\Url;
use mcms\pages\models\CategoryProp;
use kartik\grid\GridView;
use mcms\pages\models\CategoryPropEntity;

/* @var $model mcms\pages\models\Category */
/* @var $form yii\widgets\ActiveForm */
/* @var $propsDataProvider \yii\data\ActiveDataProvider */

\mcms\common\grid\ActionColumnAsset::register($this);
?>

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title pull-left"><?= Category::translate('props')?>:
      <?= Modal::widget([
        'toggleButtonOptions' => [
          'tag' => 'a',
          'label' => Html::icon('plus'),
          'class' => 'btn btn-xs btn-success',
          'data-pjax' => 0,
        ],
        'url' => Url::to(CategoryProp::getModalLink($model->id)),
      ]) ?>
    </h3>
    <div class="clearfix"></div>
  </div>
  <div class="panel-body">
<?php Pjax::begin([
  'id' => 'categoryPropsContainer',
])?>
  <?php if($model->isNewRecord) {
      Pjax::end();
      return;
  } ?>

  <?= GridView::widget([
    'dataProvider' => $propsDataProvider,
    'layout' => '{items}',
    'export' => false,
    'bordered' => false,
    'columns' => [
      'id',
      'name',
      [
        'attribute' => 'type',
        'value' => function($prop) {
          /* @var $prop mcms\pages\models\CategoryProp */
          return $prop->getTypesLabels($prop->type);
        }
      ],
      'code',
      [
        'attribute' => 'is_multivalue',
        'class' => '\kartik\grid\BooleanColumn'
      ],
      [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => '{prop-modal} {prop-entity-modal} {prop-delete}',
        'buttons' => [
          'prop-modal' => function ($url, $prop) {
            /* @var $prop mcms\pages\models\CategoryProp */
            return Modal::widget([
              'toggleButtonOptions' => [
                'tag' => 'a',
                'label' => Html::icon('pencil'),
                'title' => Yii::t('yii', 'Update'),
                'class' => 'btn btn-xs btn-default',
                'data-pjax' => 0,
              ],
              'url' => Url::to($prop::getModalLink($prop->page_category_id, $prop->id)),
            ]);
          },
          'prop-entity-modal' => function ($url, $prop) {
            /* @var $prop mcms\pages\models\CategoryProp */

            if ($prop->type != CategoryProp::TYPE_SELECT) return null;

            return Modal::widget([
              'toggleButtonOptions' => [
                'tag' => 'a',
                'label' => Html::icon('th-list'),
                'title' => Category::translate('prop_entities'),
                'class' => 'btn btn-xs btn-default',
                'data-pjax' => 0,
              ],
              'url' => Url::to(CategoryPropEntity::getModalLink($prop->id)),
            ]);
          },
          'prop-delete' => function ($url, $entity) {
            $options = [
              'title' => Yii::t('yii', 'Delete'),
              'aria-label' => Yii::t('yii', 'Delete'),
              AjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
              AjaxButtons::AJAX_ATTRIBUTE => 1,
              'class' => 'btn btn-xs btn-default'
            ];
            return Html::a(Html::icon('trash'), $url, $options);
          }

        ]
      ],
    ]
  ]); ?>

    <?php Pjax::end()?>
  </div>
</div>


