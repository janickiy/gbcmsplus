<?php
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use yii\widgets\Pjax;
use yii\bootstrap\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use mcms\promo\models\BannerTemplateAttribute;
use mcms\promo\models\BannerTemplate;

/* @var $model mcms\pages\models\Category */
/* @var $form yii\widgets\ActiveForm */
/* @var $attributesDataProvider \yii\data\ActiveDataProvider */

\mcms\common\grid\ActionColumnAsset::register($this);
?>

<?php Pjax::begin(['id' => 'templateAttributesContainer'])?>
<?php if(!$model->isNewRecord) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title pull-left"><?= BannerTemplate::translate('attributes')?>:
      <?= Modal::widget([
        'toggleButtonOptions' => [
          'tag' => 'a',
          'label' => Html::icon('plus'),
          'class' => 'btn btn-xs btn-success',
          'data-pjax' => 0,
        ],
        'url' => Url::to(BannerTemplateAttribute::getModalLink($model->id)),
      ]) ?>
    </h3>
    <div class="clearfix"></div>
  </div>
  <div class="panel-body">
  <?= GridView::widget([
    'dataProvider' => $attributesDataProvider,
    'layout' => '{items}',
    'export' => false,
    'bordered' => false,
    'columns' => [
      'id',
      'name',
      [
        'attribute' => 'type',
        'value' => function($prop) {
          /* @var $prop mcms\promo\models\BannerTemplateAttribute */
          return $prop->getTypesLabels($prop->type);
        }
      ],
      'code',
      [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => '{attribute-modal} {attribute-delete}',
        'buttons' => [
          'attribute-modal' => function ($url, $attribute) {
            if (!Yii::$app->user->can('PromoBannerTemplatesAttributeModal')) return '';
            /* @var $attribute mcms\promo\models\BannerTemplateAttribute */
            return Modal::widget([
              'toggleButtonOptions' => [
                'tag' => 'a',
                'label' => Html::icon('pencil'),
                'title' => Yii::t('yii', 'Update'),
                'class' => 'btn btn-xs btn-default',
                'data-pjax' => 0,
              ],
              'url' => Url::to($attribute::getModalLink($attribute->template_id, $attribute->id)),
            ]);
          },
          'attribute-delete' => function ($url, $attribute) {
            if (!Yii::$app->user->can('PromoBannerTemplatesAttributeDelete')) return '';
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
</div>
</div>
<?php } ?>
<?php Pjax::end()?>



