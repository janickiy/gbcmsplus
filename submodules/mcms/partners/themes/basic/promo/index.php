<?php
use mcms\partners\assets\PromoListAsset;
use yii\helpers\Url;
PromoListAsset::register($this);

/** @var string $typeWebmaster */
/** @var string $typeArbitrary */

$projectName = Yii::$app->getModule('partners')->getProjectName();

?>
<div class="container-fluid">
  <div class="row">
    <div class="col-lg-8 col-lg-offset-2">
      <div class="bgf">
        <div class="title text-center">
          <h2><?= Yii::_t('promo.promo-index-subtitle')?></h2>
        </div>
        <div class="promo_main">
          <div class="row text-center">
            <div class="col-xs-6">
              <img src="<?= Yii::getAlias('@web')?>/img/index_window_l.png" alt="">
              <a href="<?= Url::to(['index', 'choose' => $typeWebmaster]); ?>" class="btn btn-default btn-lg"><?= Yii::_t('promo.promo-index-i-am-webmaster')?></a>
              <p><?= Yii::_t('promo.promo-index-webmaster-description', $projectName)?></p>
            </div>
            <div class="col-xs-6 border-left">
              <img src="<?= Yii::getAlias('@web')?>/img/index_window_r.png" alt="">
              <a href="<?= Url::to(['index', 'choose' => $typeArbitrary]); ?>" class="btn btn-default btn-lg"><?= Yii::_t('promo.promo-index-i-am-arbitrary')?></a>
              <p><?= Yii::_t('promo.promo-index-arbitrary-description', $projectName)?></p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>