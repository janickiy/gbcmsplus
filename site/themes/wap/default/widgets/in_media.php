<?php
/** @var $data \mcms\pages\models\Page[] */
?>

<?php /** @var \mcms\pages\models\Page $page */
foreach ($data as $page) { ?>
    <li class="media__item">
        <a class="media__item-link" href="<?=\yii\helpers\ArrayHelper::getValue($page->getPropByCode('media_url'), 'multilang_value');?>">
            <div class="media__item-logo-wrap">
              <?php
              $images = unserialize($page->images);

              $image =  \yii\helpers\ArrayHelper::getValue($images,key($images));
              if (!empty($image)) {
                echo \yii\helpers\Html::img($image, [
                  'class' => 'media__item-logo'
                ]);
              }
              ?>
            </div>
            <svg class="media__item-arrow" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="m1 1 5 5-5 5" stroke="#50B65A" stroke-width="1.5"></path>
            </svg>
            <p class="media__item-subject"><?=\yii\helpers\Html::encode($page->name)?></p>
            <p class="media__item-text"><?=strip_tags($page->text,'<br><b><i><del>')?></p>
        </a>
    </li>
<?php } ?>

