<?php

use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use mcms\common\widget\AdminGridView;
use mcms\promo\models\BannerTemplate;
use mcms\promo\models\search\BannerSearch;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\Html as BtHtml;
use yii\helpers\Url;

/** @var $this \mcms\common\web\View */
/** @var $templatesDataProvider \yii\data\ActiveDataProvider */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $searchModel BannerSearch */

$formName = (new BannerSearch())->formName();
?>



    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title pull-left"><?= BannerTemplate::translate('list') ?></h3>
                    <div class="clearfix"></div>
                </div>
              <?= ListView::widget([
                'dataProvider' => $templatesDataProvider,
                'layout' => "{items}",
                'itemOptions' => ['tag' => false],
                'options' => ['class' => 'list-group'],
                'itemView' => function ($model, $key, $index, $widget) use ($formName, $searchModel) {
                  /** @var $model BannerTemplate */

                  $text = $model->name;
                  $text .= Html::tag('span', $model->getActivePagesCount(), ['class' => 'badge']);

                  $url = ['', $formName => ['template_id' => $model->id]];

                  return Html::a($text, $url, [
                    'class' => 'list-group-item' . ($searchModel->template_id == $model->id ? ' active' : ''),
                  ]);
                },
              ]); ?>
            </div>
        </div>
        <div class="col-md-9">
          <?php $template = $searchModel->getTemplate()->one(); ?>
          <?php ContentViewPanel::begin([
            'header' => $template->name,
            'padding' => false,
            'toolbar' => $template
              ? (Html::a(
                  '<i class="fa fa-cog"></i> ' .
                  BannerTemplate::translate('update'),
                  BannerTemplate::getCreateLink($template->id),
                  ['class' => 'btn btn-success btn-labeled', 'data-pjax' => 0]) .
                Html::a(
                  '<i class="fa fa-plus"></i> ' .
                  Yii::_t('banners.create_banner'),
                  ['create', 'templateId' => $template->id],
                  ['class' => 'btn btn-success btn-labeled', 'data-pjax' => 0]
                ))
              : null,
          ]);
          ?>
          <?php Pjax::begin([
            'id' => 'pages-pjax',
          ]); ?>
          <?php if ($searchModel->template_id): ?>
            <?= AdminGridView::widget([
              'id' => 'pages-grid',
              'dataProvider' => $dataProvider,
              'filterModel' => $searchModel,
              'export' => false,
              'resizableColumns' => false,
              'tableOptions' => ['class' => 'dataTable'],
              'columns' => [
                [
                  'attribute' => 'id',
                  'contentOptions' => ['style' => 'width : 70px'],
                ],
                'name',
                [
                  'attribute' => 'is_disabled',
                  'label' => Yii::_t('banners.status'),
                  'class' => '\kartik\grid\BooleanColumn',
                  'trueIcon' => GridView::ICON_INACTIVE,
                  'falseIcon' => GridView::ICON_ACTIVE,

                  'trueLabel' => Yii::t('kvgrid', 'Inactive'),
                  'falseLabel' => Yii::t('kvgrid', 'Active'),
                ],
                [
                  'class' => 'mcms\common\grid\ActionColumn',
                  'template' => '{view} {update} {disable} {enable} {delete}',
                  'contentOptions' => ['style' => 'min-width : 110px'],
                  'buttons' => [
                    'view' => function ($url, $banner) {
                      return implode('', array_map(function ($language) use ($banner) {
                          return Html::a(BtHtml::icon('eye-open') . ' ' . $language,
                            ['banners/view', 'id' => $banner->id, 'language' => $language], [
                              'data-pjax' => '0',
                              'class' => 'btn btn-xs btn-default',
                              'target' => '_blank',
                            ]);
                        }, Yii::$app->params['languages'])
                      );
                    },
                  ],
                ],
              ],
            ]); ?>
          <?php else: ?>
            <?= Yii::_t('banners.template_not_selected') ?>
          <?php endif; ?>
          <?php Pjax::end(); ?>
          <?php ContentViewPanel::end() ?>
        </div>
    </div>

