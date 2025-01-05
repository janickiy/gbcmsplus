<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\helpers\Html;
use yii\bootstrap\Html as BHtml;
use mcms\common\widget\modal\Modal;
use mcms\pages\models\PartnerCabinetStyleCategory;
use mcms\common\widget\AjaxButtons;

/** @var PartnerCabinetStyleCategory[] $categories */
/** @var AjaxActiveKartikForm $form */
/** @var integer $styleId */
?>

<?php foreach ($categories as $category): ?>
  <h2>
    <?= $category->name ?>
    <div class="btn-group pull-right">
      <?= Modal::widget([
        'toggleButtonOptions' => [
          'tag' => 'a',
          'label' => BHtml::icon('pencil'),
          'title' => Yii::t('yii', 'Update'),
          'class' => 'btn btn-xs btn-default',
          'data-pjax' => 0,
        ],
        'url' => ['update-category-modal', 'id' => $category->id],
      ]); ?>

      <?= Html::a(BHtml::icon('trash'), ['delete-category', 'id' => $category->id],
        [
          'class' => 'btn btn-xs btn-danger',
          AjaxButtons::CONFIRM_ATTRIBUTE => Yii::t('yii', 'Are you sure you want to delete this item?'),
          AjaxButtons::AJAX_ATTRIBUTE => 1,
          'data-pjax' => 0,
        ]) ?>
    </div>
  </h2>
  <hr>

  <?= $this->render('_category_fields', [
    'form' => $form,
    'styleId' => $styleId,
    'category' => $category
  ]); ?>
<?php endforeach; ?>