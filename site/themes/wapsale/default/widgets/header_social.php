<?php
use yii\helpers\Html;
use mcms\common\helpers\ArrayHelper;

/** @var $data \mcms\pages\models\Page[] */
/** @var $category \mcms\pages\models\Category */
$module = Yii::$app->getModule('pages');


$twitter = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'twitter_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$skype = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'skype_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$linkedin = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'linkedin_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$googleplus = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'googleplus_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$youtube = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'youtube_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$flickr = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'flickr_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$facebook = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'facebook_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$pinterest = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'pinterest_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$vk = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'vk_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();
$ok = $module->api('pagesWidget', ['categoryCode' => 'common', 'pageCode' => 'landing', 'propCode' => 'ok_link', 'viewBasePath' => $this->context->viewBasePath, 'view' => 'widgets/prop_multivalue' ])->getResult();

?>

<!-- header-top-first start -->
<!-- ================ -->
<div class="header-top-first clearfix">
  <ul class="social-links clearfix hidden-xs">    
    <?php if (!empty($twitter)): ?><li class="twitter"><a target="_blank" href="<?= $twitter ?>"><i class="fa fa-twitter"></i></a></li><?php endif; ?>
    <?php if (!empty($skype)): ?><li class="skype"><a target="_blank" href="<?= $skype ?>"><i class="fa fa-skype"></i></a></li><?php endif; ?>
    <?php if (!empty($linkedin)): ?><li class="linkedin"><a target="_blank" href="<?= $linkedin ?>"><i class="fa fa-linkedin"></i></a></li><?php endif; ?>
    <?php if (!empty($googleplus)): ?><li class="googleplus"><a target="_blank" href="<?= $googleplus ?>"><i class="fa fa-google-plus"></i></a></li><?php endif; ?>
    <?php if (!empty($youtube)): ?><li class="youtube"><a target="_blank" href="<?= $youtube ?>"><i class="fa fa-youtube-play"></i></a></li><?php endif; ?>
    <?php if (!empty($flickr)): ?><li class="flickr"><a target="_blank" href="<?= $flickr ?>"><i class="fa fa-flickr"></i></a></li><?php endif; ?>
    <?php if (!empty($facebook)): ?><li class="facebook"><a target="_blank" href="<?= $facebook ?>"><i class="fa fa-facebook"></i></a></li><?php endif; ?>
    <?php if (!empty($pinterest)): ?><li class="pinterest"><a target="_blank" href="<?= $pinterest ?>"><i class="fa fa-pinterest"></i></a></li><?php endif; ?>
    <?php if (!empty($vk)): ?><li class="vk"><a target="_blank" href="<?= $vk ?>"><i class="fa fa-vk"></i></a></li><?php endif; ?>
    <?php if (!empty($ok)): ?><li class="ok"><a target="_blank" href="<?= $ok ?>"><i class="fa fa-odnoklassniki"></i></a></li><?php endif; ?>
  </ul>
  <div class="social-links hidden-lg hidden-md hidden-sm">
    <div class="btn-group dropdown">
      <button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><i class="fa fa-share-alt"></i></button>
      <ul class="dropdown-menu dropdown-animation">
        <?php if (!empty($twitter)): ?><li class="twitter"><a target="_blank" href="<?= $twitter ?>"><i class="fa fa-twitter"></i></a></li><?php endif; ?>
        <?php if (!empty($skype)): ?><li class="skype"><a target="_blank" href="<?= $skype ?>"><i class="fa fa-skype"></i></a></li><?php endif; ?>
        <?php if (!empty($linkedin)): ?><li class="linkedin"><a target="_blank" href="<?= $linkedin ?>"><i class="fa fa-linkedin"></i></a></li><?php endif; ?>
        <?php if (!empty($googleplus)): ?><li class="googleplus"><a target="_blank" href="<?= $googleplus ?>"><i class="fa fa-google-plus"></i></a></li><?php endif; ?>
        <?php if (!empty($youtube)): ?><li class="youtube"><a target="_blank" href="<?= $youtube ?>"><i class="fa fa-youtube-play"></i></a></li><?php endif; ?>
        <?php if (!empty($flickr)): ?><li class="flickr"><a target="_blank" href="<?= $flickr ?>"><i class="fa fa-flickr"></i></a></li><?php endif; ?>
        <?php if (!empty($facebook)): ?><li class="facebook"><a target="_blank" href="<?= $facebook ?>"><i class="fa fa-facebook"></i></a></li><?php endif; ?>
        <?php if (!empty($pinterest)): ?><li class="pinterest"><a target="_blank" href="<?= $pinterest ?>"><i class="fa fa-pinterest"></i></a></li><?php endif; ?>
        <?php if (!empty($vk)): ?><li class="vk"><a target="_blank" href="<?= $vk ?>"><i class="fa fa-vk"></i></a></li><?php endif; ?>
        <?php if (!empty($ok)): ?><li class="ok"><a target="_blank" href="<?= $ok ?>"><i class="fa fa-odnoklassniki"></i></a></li><?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<!-- header-top-first end -->

