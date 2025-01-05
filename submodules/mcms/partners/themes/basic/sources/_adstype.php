<?php
/** @var $model \mcms\promo\models\AdsType */

if (!isset($source)) {
    $source = null;
}
?>

<div class="row">
  <div class="col-xs-6 option__list-scale">
    <span class="scale__title" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= Yii::_t('sources.profit_hint') ?>"><?= Yii::_t('sources.profit') ?></span>
    <div class="scale__col" data-color="1" data-count="<?= $model->profit ?>">
      <?php for ($i = 1; $i <= 5; $i++) : ?>
        <span <?= ($model->profit <= $i) ? 'class="orange-' . $model->profit . '"' : '' ?>></span>
      <?php endfor; ?>
    </div>
  </div>

  <div class="col-xs-6 option__list-scale">
    <span class="scale__title" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= Yii::_t('sources.security_hint') ?>"><?= Yii::_t('sources.security') ?></span>
    <div class="scale__col" data-color="2" data-count="<?= $model->security ?>">
      <?php for ($i = 1; $i <= 5; $i++) : ?>
        <span <?= ($model->security <= $i) ? 'class="brown"' : '' ?>></span>
      <?php endfor; ?>
    </div>
  </div>
</div>

<span class="option__list-description">
  <?= $model->getAdditionalDescription($source) ?>
</span>