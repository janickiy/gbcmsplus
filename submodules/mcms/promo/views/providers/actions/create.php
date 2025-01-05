<?php
use mcms\common\helpers\Link;
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;

?>

<?php $link = Link::get('/promo/providers/create');?>
<?php if ($link) : ?>
  <?= Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'label' => Html::icon('plus') . ' ' . Yii::_t('promo.providers.create'),
      'class' => 'btn btn-success',
      'data-pjax' => 0,
    ],
    'url' => Url::to(['/promo/providers/create']),
  ]) ?>
<?php else : ?>
  <?= $link ?>
<?php endif ?>
