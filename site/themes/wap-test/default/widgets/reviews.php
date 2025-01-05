<?php
/** @var $data \mcms\pages\models\Page[] */
?>

<?php /** @var \mcms\pages\models\Page $page */
foreach ($data as $page) { ?>
  <li class="swiper-slide reviews__slide">
    <div class="reviews__slide-text-box">
      <p class="reviews__slide-text"><?=$page->text?></p>
      <div class="reviews__slide-author">
        <h2 class="reviews__slide-author-name"><?=\yii\helpers\Html::encode($page->name)?></h2>
        <span class="reviews__slide-author-job"><?=\yii\helpers\ArrayHelper::getValue($page->getPropByCode('author_position'), 'multilang_value');?></span>
        
          <?php
          $images = unserialize($page->images);

          $image =  \yii\helpers\ArrayHelper::getValue($images,key($images));
          if (!empty($image)) {
            echo \yii\helpers\Html::img($image, [
              'class' => 'reviews__slide-author-image'
            ]);
          }
          ?>
       
      </div>
      <svg class="reviews__slide-quotes" viewBox="0 0 26 25" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M15.312.175H25.88v18.158l-10.568 6.448V.175ZM.09.175h10.568v18.158L.09 24.781V.175Z" fill="#50B65A"></path>
      </svg>
    </div>
  </li>
<?php } ?>
