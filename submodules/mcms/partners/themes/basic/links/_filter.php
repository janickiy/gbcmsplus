<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var mcms\common\web\View $this */

?>

<?php $form = ActiveForm::begin([
    'id' => 'linksListFilter',
    'action' => ['index'],
    'method' => 'post',
    'options' => [
      'data-pjax' => true,
    ],
    'successCssClass' => '',
    'errorCssClass' => '',
  ]); ?>

<div class="statistics_collapsed">
  <div class="row">
    <div class="col-xs-20">
      <div class="filter">
        <div class="filter-header">
          <span><?= Yii::_t('main.streams') ?><i></i></span>
          <div class="caret_wrap">
            <i class="caret"></i>
          </div>

        </div>
        <div class="filter-body filter-body_left">
          <div class="filter-body_search">
            <i class="icon-search"></i>
            <input type="text" class="form-control" placeholder="<?= Yii::_t('main.quick_search') ?>">
            <span class="reset_search">
              <i class="icon-cancel_4"></i>
            </span>
          </div>
          <div class="filter-body_selected">
            <div class="hidden_text"><?= Yii::_t('main.no_stream_selected') ?></div>
          </div>
          <div class="filter-body_deselected">
            <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>

            <?= $form->field($searchModel, 'stream_ids', [
              'inputOptions' => [
                'class' => 'styled',
              ]
            ])->checkboxList($streams, [
              'unselect' => null,
              'item' => function($index, $label, $name, $checked, $value) {
                $id = 'filter-stream-' . $value;
                return Html::tag('div', Html::checkbox($name, $checked, ['value' => $value, 'id' => $id]) .
                  Html::label(Yii::$app->formatter->asText($label), $id), ['class' => 'checkbox checkbox-inline']);
              }
            ])->label(false); ?>

          </div>
        </div>
      </div>
    </div>
    <div class="col-xs-20">
      <div class="filter">
        <div class="filter-header">
          <span><?= Yii::_t('main.domains') ?><i></i></span>
          <div class="caret_wrap">
            <i class="caret"></i>
          </div>

        </div>
        <div class="filter-body filter-body_left">
          <div class="filter-body_search">
            <i class="icon-search"></i>
            <input type="text" class="form-control" placeholder="<?= Yii::_t('main.quick_search') ?>">
            <span class="reset_search">
              <i class="icon-cancel_4"></i>
            </span>
          </div>
          <div class="filter-body_selected">
            <div class="hidden_text"><?= Yii::_t('main.no_domain_selected') ?></div>
          </div>
          <div class="filter-body_deselected">
            <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>

            <?= $form->field($searchModel, 'domain_ids', [
              'inputOptions' => [
                'class' => 'styled',
              ]
            ])->checkboxList($domains, [
              'unselect' => null,
              'item' => function($index, $label, $name, $checked, $value) {
                $id = 'filter-domain-' . $value;
                return Html::tag('div', Html::checkbox($name, $checked, ['value' => $value, 'id' => $id]) .
                  Html::label(Yii::$app->formatter->asText($label), $id), ['class' => 'checkbox checkbox-inline']);
              }
            ])->label(false); ?>

          </div>
        </div>
      </div>
    </div>
    <div class="col-xs-20">
      <div class="form-group">
        <?= $form->field($searchModel, 'name', [
          'template' => '{input}',
          'inputOptions' => [
            'class' => 'form-control',
            'placeholder' => Yii::_t('links.link_name'),
          ]
        ]) ?>
      </div>
    </div>
    <div class="col-xs-20">
      <div class="form-group">
        <?= $form->field($searchModel, 'link', [
          'template' => '{input}',
          'inputOptions' => [
            'class' => 'form-control',
            'placeholder' => Yii::_t('links.our_link'),
          ]
        ]) ?>
      </div>
    </div>
    <div class="col-xs-20">
      <button class="btn btn-primary btn-block"><?= Yii::_t('main.apply') ?></button>
    </div>
  </div>
</div>

<?php ActiveForm::end(); ?>