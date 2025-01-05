<?php
use mcms\common\helpers\Link;
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;

?>

<?php $link = Link::get('/promo/providers/update', ['id' => $model->id]); ?>
<?php if ($link) : ?>
  <?= Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'label' => Html::icon('pencil') . ' ' . Yii::_t('promo.providers.update'),
      'class' => 'btn btn-warning',
      'data-pjax' => 0,
    ],
    'url' => Url::to(['/promo/providers/update', 'id' => $model->id]),
  ]) ?>
<?php else : ?>
  <?= $link ?>
<?php endif ?>
