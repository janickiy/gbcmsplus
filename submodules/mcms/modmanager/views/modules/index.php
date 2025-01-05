<?php
/**
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use yii\widgets\Pjax;
use yii\bootstrap\Html;
use yii\helpers\Url;
use mcms\modmanager\models\Module;
use mcms\common\helpers\Html as CustomHtml;

?>
<?php Pjax::begin(); ?>

<?php ContentViewPanel::begin([
  'padding' => false,
]) ?>
<?= AdminGridView::widget([
  'dataProvider' => $dataProvider,
  'dataColumnClass' => 'kartik\grid\DataColumn',
  'export' => false,
  'hover' => true,
  'columns' => [
    'id',
    [
      'attribute' => 'module_id',
      'filter' => false,
    ],
    [
      'attribute' => 'name',
      'filter' => false,
      'value' => function ($model) {
        return Yii::_t($model['name']);
      }
    ],
    [
      'attribute' => 'is_disabled',
      'format' => 'raw',
      'filter' => false,
      'value' => function ($model) {
        if (isset($model['available'])) {
          return AdminGridView::ICON_INACTIVE;
        }
        return !$model['is_disabled'] ? AdminGridView::ICON_ACTIVE : AdminGridView::ICON_INACTIVE;
      },
    ],
    [
      'attribute' => 'created_at',
      'value' => function ($model) {
        return !isset($model['available']) ? Yii::$app->formatter->asDatetime($model['created_at']) : false;
      },
      'filter' => false,
    ],
    [
      'attribute' => 'updated_at',
      'value' => function ($model) {
        return !isset($model['available']) ? Yii::$app->formatter->asDatetime($model['updated_at']) : false;
      },
      'filter' => false,
    ],
    [
      'class' => 'mcms\common\grid\ActionColumn',
      'template' => '{settings} {messages} {install}',
      'buttonsPath' => [
        'messages' => '/modmanager/messages/edit',
      ],
      'buttons' => [
        'settings' => function ($url, $model) {
          if (isset($model['available'])) {
            return false;
          }

          return Module::canEdit($model['module_id'])
            ? CustomHtml::a(Html::icon('cog'), $url, ['class' => 'btn btn-xs btn-default', 'data-pjax' => 0])
            : false;
        },
        'messages' => function ($url, $model) {
          if (isset($model['available'])) {
            return false;
          }
          return Module::canEditTranslations($model->module_id)
            ? CustomHtml::a(Html::icon('book'), $url, ['class' => 'btn btn-xs btn-default', 'data-pjax' => 0])
            : false;
        },
        'install' => function ($url, $model) {
          if (!isset($model['available'])) {
            return false;
          }
          return CustomHtml::a(
            Html::icon('download-alt') . ' ' . Yii::_t('modules.install'),
            ['install', 'id' => $model['module_id']],
            ['class' => 'btn btn-xs btn-default']
          );
        }
      ],
      'urlCreator' => function ($action, $model, $key, $index) {
        return $action === 'messages'
          ? ['messages/edit', 'module_id' => $model['module_id']]
          : [$action, 'id' => $model['id']];
      },
      'contentOptions' => ['style' => 'min-width: 120px']
    ],
  ],
]) ?>
<?php ContentViewPanel::end() ?>
<?php Pjax::end(); ?>
