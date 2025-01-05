<?php

use mcms\common\helpers\Link;
use mcms\partners\assets\PromoListAsset;
use yii\helpers\Url;
use yii\widgets\Pjax;

PromoListAsset::register($this);

/* @var mcms\common\web\View $this */
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-xs-12">
      <div class="bgf">
        <div class="title">
          <div class="change_param pull-right">
            <div class="toggle__page">
              <span class="active"><?= Yii::_t('promo.promo-webmaster'); ?></span>
              <a href="<?= Url::to(['promo/index', 'choose' => $typeArbitrary]); ?>" class="toggle__box"><i><span></span><span></span><span></span></i></a>
              <span onclick="location.href='<?= Url::to(['promo/index', 'choose' => $typeArbitrary]); ?>'"><?= Yii::_t('promo.promo-arbitrary'); ?></span>
            </div>
          </div>
          <?= Link::get('', [], ['class' => 'active h_link'], Yii::_t('sources.sources_list')) ?>
          <?= Link::get('add', [], ['class' => 'h_link'], '<i class="icon-plus1"></i>' . Yii::_t('sources.add_source')) ?>
          <div class="filter_col">
            <div class="collapse_filters"><i class="icon-filter"></i><span><?= Yii::_t('main.filters') ?></span></div>
          </div>
        </div>

        <?= $this->render('_filter', compact('searchModel', 'categories', 'statuses', 'adsTypes')) ?>

        <?php Pjax::begin(['id' => 'linksFormPjax']); ?>

        <?php echo $sourcesDataProvider->totalCount === 0
        ? $this->render('_empty')
        : $this->render('_grid', compact('sourcesDataProvider')); ?>

        <?php Pjax::end(); ?>
      </div>
    </div>
  </div>
</div>
