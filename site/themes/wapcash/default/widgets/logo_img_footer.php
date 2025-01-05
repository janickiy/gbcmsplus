<?php
/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */

$images = $data[0]->getPropByCode('logo');

$image = $images->getImageUrl();
?>


<div class="uk-width-medium-1-3 uk-flex sub-footer__logo uk-hidden-small">
  <a href="/" class="logo logo_footer">

    <?= \yii\helpers\Html::img($image, ['class' => 'logo__img logo__img_footer'])?>

  </a>
</div>



