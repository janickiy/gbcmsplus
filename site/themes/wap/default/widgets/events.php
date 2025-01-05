<?php
/** @var $data \mcms\pages\models\Page[] */

 /** @var \mcms\pages\models\Page $page */
$pagesModule = Yii::$app->getModule('pages');
foreach ($data as $page) { ?>

<li class="swiper-slide meetings__slide card">
  <time class="card__date" datetime="2022-09-17"><?=\yii\helpers\ArrayHelper::getValue($page->getPropByCode('event_date'), 'multilang_value');?></time>
  <span class="card__city"><?=\yii\helpers\ArrayHelper::getValue($page->getPropByCode('event_city'), 'multilang_value');?></span>
  <?php
  $images = unserialize($page->images);
  
  $image =  \yii\helpers\ArrayHelper::getValue($images,key($images));
  if (!empty($image)) {
    echo \yii\helpers\Html::img($image, [
      'class' => 'card__logo'
    ]);
  }
  ?>
  <h2 class="card__heading"><?=\yii\helpers\Html::encode($page->name)?></h2>
  <div class="card__details">
    <h2 class="card__heading card__heading--light"><?=\yii\helpers\Html::encode($page->name)?></h2>
    <p class="card__description"><?=strip_tags($page->text);?></p>
      <?php
         $url = \yii\helpers\ArrayHelper::getValue($page->getPropByCode('create_meeting_url'), 'multilang_value');
         if(empty($url)){
             $url = "https://t.me/wap_click";
         }
      ?>
    <a target="_blank" href="<?=$url?>" class="btn card__btn" style="text-align: center">Назначить встречу</a>
  </div>
</li>
<?php } ?>
