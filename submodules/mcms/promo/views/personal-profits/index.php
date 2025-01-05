<?php
use mcms\promo\components\widgets\PersonalProfitWidget;
use \yii\bootstrap\Html;
use mcms\common\widget\AjaxButtons;
use mcms\promo\models\PersonalProfit;

/** @var array $ignoreIds */
?>

<?php $this->beginBlock('actions'); ?>
<?= Html::a(PersonalProfit::t('actualize-courses'), ['personal-profits/actualize-courses'], [
  'class' => 'btn btn-primary',
  AjaxButtons::CONFIRM_ATTRIBUTE => PersonalProfit::t('actualize-courses-confirm'),
  AjaxButtons::AJAX_ATTRIBUTE => 1
]); ?>
  <?= $this->render('_create_button', ['userId' => null]) ?>
<?php $this->endBlock() ?>

<?= PersonalProfitWidget::widget([
  'options' => [
    'renderCreateButton' => false,
    'ignoreIds' => $ignoreIds,
  ]
]);
