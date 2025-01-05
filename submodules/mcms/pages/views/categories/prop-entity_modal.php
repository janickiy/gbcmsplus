<?php

use kartik\form\ActiveForm;
use mcms\common\multilang\widgets\input\InputWidget;
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\alert\Alert;
use yii\web\JsExpression;
use kartik\grid\GridView;
use mcms\pages\models\Category;
use yii\bootstrap\Html;
use yii\widgets\Pjax;
use mcms\pages\models\CategoryPropEntity;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $prop mcms\pages\models\CategoryProp */
/* @var $entity mcms\pages\models\CategoryPropEntity */
/* @var $entitiesDataProvider \yii\data\ActiveDataProvider */

$pjaxContainer = 'prop-entities-pjax';
?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?=  Category::translate('prop_entities') . ': ' . $prop->name ?></h4>
</div>

<div class="modal-body">

  <?php Pjax::begin([
    'id' => $pjaxContainer,
    'enablePushState' => false
  ])?>

  <?php $form = AjaxActiveKartikForm::begin([
    'ajaxSuccess' => new JsExpression('function(response){
        $.pjax({url: "' . Url::to(CategoryPropEntity::getModalLink($prop->id)) . '", container: "#' . $pjaxContainer . '", timeout: false, push: false});
      }'),
  ]); ?>

  <div class="row">
      <div class="col-lg-5">
        <?= $form->field($entity, 'label')->widget(InputWidget::class, [
          'class' => 'form-control',
          'form' => $form
        ])->label(false) ?>
      </div>
      <div class="col-lg-4">
        <?= $form->field($entity, 'code')->label(false)->textInput([
          'placeholder' => $entity->getAttributeLabel('code')
        ]) ?>
      </div>
      <div class="col-lg-1">
        <?= Html::submitButton(
          $entity->isNewRecord ? Html::icon('plus') : Html::icon('floppy-disk'),
          [
            'class' => $entity->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            'title' => Yii::_t('app.common.Save')
          ]
        ) ?>
      </div>

      <?php if(!$entity->isNewRecord): ?>
        <div class="col-lg-1">
          <?= Html::a(
            Html::icon('ban-circle'),
            CategoryPropEntity::getModalLink($entity->page_category_prop_id),
            [
              'class' => 'btn btn-default',
              'data-pjax' => 1,
              'data-push' => 0,
              'title' => Yii::_t('app.common.cancel')
            ]
          ) ?>
        </div>
      <?php endif; ?>
  </div>


  <?php AjaxActiveKartikForm::end(); ?>

  <hr>

  <?= GridView::widget([
    'dataProvider' => $entitiesDataProvider,
    'layout' => '{items}',
    'export' => false,
    'bordered' => false,
    'condensed' => true,
    'columns' => [
      'id',
      'label',
      'code',
      [
        'class' => 'mcms\common\grid\ActionColumn',
        'template' => '{prop-entity-modal} {prop-entity-delete}',
        'buttons' => [
          'prop-entity-modal' => function ($url, $entity) {
            /* @var $entity mcms\pages\models\CategoryPropEntity */
            return Html::a(
              Html::icon('pencil'),
              CategoryPropEntity::getModalLink($entity->page_category_prop_id, $entity->id),
              [
                'title' => Yii::t('yii', 'Update'),
                'aria-label' => Yii::t('yii', 'Update'),
                'data-pjax' => 1,
                'data-push' => 0,
                'class' => 'btn btn-xs btn-default'
              ]
            );
          },
          'prop-entity-delete' => function ($url, $entity) {
            $options = [
              'title' => Yii::t('yii', 'Delete'),
              'aria-label' => Yii::t('yii', 'Delete'),
              AjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
              'data-ajaxable-entity' => 1,
              'class' => 'btn btn-xs btn-default'
            ];
            return Html::a(Html::icon('trash'), $url, $options);
          }

        ]
      ],
    ]
  ]); ?>



  <?php

  // TODO: не получается нормально обновить грид после операции при помощи аякс-кнопок
  // грида. Пришлось пока перезаписать обработчик кнопки
  $this->registerJs('
  if (!window.gridAjaxButtonsBindedEntity) {
    window.gridAjaxButtonsBindedEntity = true;
    $(document).on("click", "[data-ajaxable-entity=1]", function (event) {
      event.preventDefault();
      var btn = this;

      var confirmText = $(btn).data("confirm-text");
      if (confirmText && !confirm(confirmText)) return false;
  
      $.post(btn.href)
        .done(function (data) {
  
          if (!data["success"]) {
            var failText = data["success"] ? data["error"] : "{{failText}}";
            $.smallBox({
              "color": "rgb(196, 106, 105)",
              "timeout" : 4000,
              "title": failText,
              "sound": false,
              "iconSmall": "miniPic fa fa-warning shake animated"
            });
            return;
          }
          
          '.Alert::success(Yii::_t('app.common.operation_success')).'
  
           $.pjax({url: "' . Url::to(CategoryPropEntity::getModalLink($prop->id)) . '", container: "#' . $pjaxContainer . '", timeOut: 10000, push: false});

        })
        .fail(function (data, data2, data3) {
          '.Alert::danger(Yii::_t('app.common.operation_failure')).'
        });
    });
  }')?>

  <?php Pjax::end(); ?>

</div>

