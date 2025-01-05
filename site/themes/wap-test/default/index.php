<?php

use mcms\partners\assets\landings\FormAsset;
use mcms\partners\assets\landings\waptest\LandingAsset;
use yii\helpers\Html;

$modulePartners = Yii::$app->getModule('partners');
if ($favicon = $modulePartners->api('getFavicon')->getResult())
  $this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $favicon]);


LandingAsset::register($this);
$js = <<<JS
//Фикс показа ошибки
$('form').on('afterValidateAttribute', function (e) {
	$(e.target).find('.has-error .help-block').show();
});

$('.help-block').hover(function() {
	$(this).hide();
});
JS;
$this->registerJs($js);

/** @var $pagesModule \mcms\common\module\Module */
/** @var $data array */


$viewBasePath = '/' . $this->context->id . '/';
$moduleUser = Yii::$app->getModule('users');
$textCss = $pagesModule->api('pagesWidget', [
  'categoryCode' => 'translate',
  'pageCode' => 'learn',
  'fieldCode' => 'text',
  'viewBasePath' => $viewBasePath,
  'view' => 'widgets/field_value'
])->getResult();
$css = <<<CSS
@media (min-width:768px){.verticals__pagination::before{content:"{$textCss}...";position:absolute;top:0;left:20px;font-family:"Inter",sans-serif;font-size:14px;line-height:20px;font-weight:400;color:#a9a9ab}}
CSS;
$this->registerCss($css);

?>
<aside class="menu">
  <div class="container menu__container">
    <div class="menu__btns-box">
      <button class="btn menu__sign-in btn--signin"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'main',
          'propCode' => 'title_sign_in',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></button>
      <button class="btn btn--light menu__sign-up btn--signup"><?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'main',
          'propCode' => 'title_sign_up',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?></button>
    </div>
    <nav class="nav">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'menu',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/main_menu'
      ])->getResult(); ?>
    </nav>
    <div class="menu__contacts-socials-wrap">
      <div class="contacts menu__contacts">
                    <span class="contacts__label">

                     <?=$pagesModule->api('pagesWidget', [
                       'categoryCode' => 'translate',
                       'pageCode' => 'contact_us',
                       'fieldCode' => 'text',
                       'viewBasePath' => $viewBasePath,
                       'view' => 'widgets/field_value'
                     ])->getResult()?>

                    <svg class="contacts__label-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m13.025 1-2.847 2.828 6.176 6.176H0v3.992h16.354l-6.176 6.176L13.025 23 24 12z"></path></svg>

                </span>
        <ul class="contacts__list">
          <li class="contacts__item">
            <svg class="contacts__item-icon" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M19.593 16.5H2.407c-.476 0-.952-.373-.952-.933V3.5c0-.467.38-1 .952-1h17.186c.476 0 .953.44.953 1v12.067c0 .56-.381.933-.953.933Z" stroke="#fff" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="m18.273 5-6.788 6.766c-.324.312-.728.312-.97 0L3.727 5" stroke="#fff" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span class="contacts__item-label">E-mail:</span>
            <div class="contacts__item-link-wrap">
              <a class="contacts__item-link" href="<?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_email',
                'propCode' => 'contact_link',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_email',
                  'fieldCode' => 'name',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/field_value'
                ])->getResult(); ?></a>
            </div>
          </li>
          <li class="contacts__item">
            <svg class="contacts__item-icon" viewBox="0 0 20 18" fill="#fff" xmlns="http://www.w3.org/2000/svg">
              <path d="M19.472.337a1.485 1.485 0 0 0-1.498-.23L.912 6.927c-.276.111-.51.304-.673.551A1.444 1.444 0 0 0 .283 9.13c.176.238.42.418.702.515l3.682 1.266 1.995 6.53c.004.013.016.022.022.034a.467.467 0 0 0 .304.276c.01.004.016.012.026.014h.005l.003.001a.43.43 0 0 0 .222-.011c.008-.002.015-.002.024-.005a.471.471 0 0 0 .182-.115c.006-.007.015-.007.02-.013l2.87-3.136 4.188 3.21a1.474 1.474 0 0 0 2.336-.857l3.107-15.102a1.431 1.431 0 0 0-.5-1.401ZM7.703 12.15l-.673 3.24-1.405-4.6L12.592 7.2l-4.76 4.712a.468.468 0 0 0-.129.239Zm8.228 4.499a.506.506 0 0 1-.33.376.504.504 0 0 1-.49-.073l-4.536-3.479a.48.48 0 0 0-.644.057L7.934 15.71l.672-3.231 6.847-6.78a.47.47 0 0 0-.229-.791.48.48 0 0 0-.328.04l-9.869 5.09-3.73-1.285a.5.5 0 0 1-.344-.463.498.498 0 0 1 .318-.489L18.33.981a.515.515 0 0 1 .532.082.493.493 0 0 1 .173.488l-3.105 15.1v-.001Z"
                    fill="#fff"></path>
            </svg>
            <span class="contacts__item-label">Telegram:</span>
            <div class="contacts__item-link-wrap">
              <a class="contacts__item-link" href="<?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_tg',
                'propCode' => 'contact_link',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_tg',
                  'fieldCode' => 'name',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/field_value'
                ])->getResult(); ?></a>
            </div>
          </li>
          <li class="contacts__item">
            <svg class="contacts__item-icon" viewBox="0 0 20 20" fill="#fff" xmlns="http://www.w3.org/2000/svg">
              <path d="M12.73 10.22a4.315 4.315 0 0 0-1.108-.508 17.06 17.06 0 0 0-.82-.223 17.63 17.63 0 0 0-.987-.24 9.406 9.406 0 0 1-1.446-.429 1.892 1.892 0 0 1-.78-.573 1.282 1.282 0 0 1-.252-.83c0-.31.101-.61.29-.854a1.93 1.93 0 0 1 .878-.613 4.264 4.264 0 0 1 1.525-.237c.405-.006.809.05 1.197.162.283.08.55.211.785.388.175.127.321.288.432.473a.476.476 0 1 0 .839-.451 2.548 2.548 0 0 0-.676-.766c-.329-.25-.7-.438-1.097-.554-.48-.141-.98-.21-1.48-.204a5.192 5.192 0 0 0-1.868.3c-.51.183-.96.505-1.297.93-.312.409-.48.91-.48 1.425-.014.51.145 1.009.452 1.416.309.387.712.687 1.172.87.391.151.793.277 1.202.375.007.003.014.01.023.012.174.052.455.122.857.21.165.035.32.074.474.113.029.008.067.016.094.025.011.004.023.003.034.005.229.059.449.12.652.182.302.09.592.22.858.39.2.13.368.305.49.51.124.239.183.506.175.775.017.356-.087.707-.296.995a2.12 2.12 0 0 1-.96.713 4.07 4.07 0 0 1-1.605.28 3.975 3.975 0 0 1-1.842-.382 2.178 2.178 0 0 1-.763-.679 1.387 1.387 0 0 1-.272-.743.476.476 0 1 0-.953 0c.016.462.17.909.441 1.283.286.413.666.75 1.109.984a4.89 4.89 0 0 0 2.28.489 4.98 4.98 0 0 0 1.972-.353 3.034 3.034 0 0 0 1.37-1.04 2.501 2.501 0 0 0 .471-1.564 2.337 2.337 0 0 0-1.09-2.061Zm6.643 1.464A9.52 9.52 0 0 0 8.316.626a5.715 5.715 0 0 0-7.69 7.69 9.519 9.519 0 0 0 11.058 11.057 5.715 5.715 0 0 0 7.69-7.69Zm-2.789 6.775a4.762 4.762 0 0 1-4.594 0 .481.481 0 0 0-.323-.05A8.568 8.568 0 0 1 1.591 8.332a.475.475 0 0 0-.05-.322A4.763 4.763 0 0 1 8.01 1.543a.46.46 0 0 0 .323.05 8.568 8.568 0 0 1 10.075 10.074.476.476 0 0 0 .05.323 4.762 4.762 0 0 1-1.873 6.468Z"
                    fill="#fff"></path>
            </svg>
            <span class="contacts__item-label">Skype:</span>
            <div class="contacts__item-link-wrap">
              <a class="contacts__item-link" href="<?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_skype',
                'propCode' => 'contact_link',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_skype',
                  'fieldCode' => 'name',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/field_value'
                ])->getResult(); ?></a>
            </div>
          </li>
        </ul>
      </div>
      <div class="socials">
        <span class="socials__label"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'social_media',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?>:</span>
        <ul class="socials__list">
          <li class="socials__item">
            <a class="socials__link"  href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_vk',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_vk',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
              
              <svg class="socials__link-icon socials__link-icon--vk" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M19.541 1.407c.139-.496 0-.862-.662-.862H16.69c-.556 0-.813.316-.952.664 0 0-1.113 2.905-2.688 4.793-.51.547-.742.72-1.02.72-.14 0-.349-.173-.349-.67V1.407c0-.596-.153-.862-.616-.862h-3.44a.544.544 0 0 0-.557.54c0 .564.788.695.87 2.284v3.453c0 .757-.128.894-.407.894-.741 0-2.545-2.919-3.616-6.259-.208-.65-.418-.912-.977-.912H.75C.125.545 0 .861 0 1.21c0 .62.742 3.7 3.454 7.774 1.808 2.781 4.354 4.29 6.673 4.29 1.391 0 1.563-.335 1.563-.912v-2.103c0-.67.131-.803.572-.803.325 0 .881.174 2.18 1.515 1.483 1.59 1.727 2.303 2.562 2.303h2.187c.625 0 .939-.335.759-.997-.199-.658-.907-1.613-1.846-2.747-.51-.645-1.275-1.34-1.508-1.689-.324-.446-.231-.646 0-1.043 0 0 2.667-4.023 2.944-5.39h.001Z"></path></svg>
            
            </a>
          </li>
          <li class="socials__item">
            <a class="socials__link"  href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_fb',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_fb',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
              
              <svg class="socials__link-icon socials__link-icon--fb" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m10.74 11.25.566-3.62H7.768V5.281c0-.99.494-1.955 2.079-1.955h1.607V.244S9.996 0 8.6 0C5.687 0 3.784 1.734 3.784 4.872V7.63H.545v3.619h3.239V20h3.984v-8.75h2.972Z"></path></svg>
            
            </a>
          </li>
            <li class="socials__item">
                <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_linkedin',
                  'propCode' => 'contact_link',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                    'categoryCode' => 'contacts',
                    'pageCode' => 'contact_linkedin',
                    'fieldCode' => 'name',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                  ])->getResult(); ?>
                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="40" height="40"
                         viewBox="0 0 30 30"
                         style="fill:#fff;">
                        <path d="M10.496,8.403 c0.842,0,1.403,0.561,1.403,1.309c0,0.748-0.561,1.309-1.496,1.309C9.561,11.022,9,10.46,9,9.712C9,8.964,9.561,8.403,10.496,8.403z M12,20H9v-8h3V20z M22,20h-2.824v-4.372c0-1.209-0.753-1.488-1.035-1.488s-1.224,0.186-1.224,1.488c0,0.186,0,4.372,0,4.372H14v-8 h2.918v1.116C17.294,12.465,18.047,12,19.459,12C20.871,12,22,13.116,22,15.628V20z"></path>
                    </svg>

                </a>
            </li>
            <li class="socials__item">
                <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_instagram',
                  'propCode' => 'contact_link',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/prop_multivalue'
                ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                    'categoryCode' => 'contacts',
                    'pageCode' => 'contact_instagram',
                    'fieldCode' => 'name',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/field_value'
                  ])->getResult(); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="-6 -6 36 36" fill="none" style="fill:#FFF;">
                        <path d="M 8 3 C 5.243 3 3 5.243 3 8 L 3 16 C 3 18.757 5.243 21 8 21 L 16 21 C 18.757 21 21 18.757 21 16 L 21 8 C 21 5.243 18.757 3 16 3 L 8 3 z M 8 5 L 16 5 C 17.654 5 19 6.346 19 8 L 19 16 C 19 17.654 17.654 19 16 19 L 8 19 C 6.346 19 5 17.654 5 16 L 5 8 C 5 6.346 6.346 5 8 5 z M 17 6 A 1 1 0 0 0 16 7 A 1 1 0 0 0 17 8 A 1 1 0 0 0 18 7 A 1 1 0 0 0 17 6 z M 12 7 C 9.243 7 7 9.243 7 12 C 7 14.757 9.243 17 12 17 C 14.757 17 17 14.757 17 12 C 17 9.243 14.757 7 12 7 z M 12 9 C 13.654 9 15 10.346 15 12 C 15 13.654 13.654 15 12 15 C 10.346 15 9 13.654 9 12 C 9 10.346 10.346 9 12 9 z"></path>
                    </svg>

                </a>
            </li>
        </ul>
      </div>
    </div>
  </div>
</aside>

<header class="header">
  <div class="container header__container">
    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'main',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/logo_dark'
    ])->getResult(); ?>
    <button class="btn header__sign-in btn--signin"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'main',
        'propCode' => 'title_sign_in',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></button>
    <button class="btn btn--dark header__sign-up btn--signup"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'main',
        'propCode' => 'title_sign_up',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/prop_multivalue'
      ])->getResult(); ?></button>
    <button class="header__menu-btn">
      <?=$pagesModule->api('pagesWidget', [
        'categoryCode' => 'translate',
        'pageCode' => 'open_menu',
        'fieldCode' => 'text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/field_value'
      ])->getResult()?>
      <span class="header__menu-btn-icon">

                <span class="header__menu-btn-icon-line header__menu-btn-icon-line--top"></span>
                <span class="header__menu-btn-icon-line header__menu-btn-icon-line--mid"></span>
                <span class="header__menu-btn-icon-line header__menu-btn-icon-line--bottom"></span>
                </span>
    </button>

    <?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'languages_list',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/languages_list'
    ])->getResult(); ?>
    
    <ul class="header__socials">
        <li class="header__socials-item">
            <a class="header__socials-link" href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_linkedin',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_linkedin',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
            </a>
        </li>
        <li class="header__socials-item">
            <a class="header__socials-link" href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_instagram',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_instagram',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
            </a>
        </li>
      <li class="header__socials-item">
        <a class="header__socials-link" href="<?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'contacts',
          'pageCode' => 'contact_vk',
          'propCode' => 'contact_link',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'contacts',
            'pageCode' => 'contact_vk',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult(); ?></a>
      </li>
      <li class="header__socials-item">
        <a class="header__socials-link" href="<?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'contacts',
          'pageCode' => 'contact_fb',
          'propCode' => 'contact_link',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'contacts',
            'pageCode' => 'contact_fb',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult(); ?></a>
      </li>
    </ul>
  </div>
</header>

<section class="hero" id="hero">
  <div class="container hero__container">
    <h1 class="hero__heading">
      World Affiliate<br> Program
      <small class="hero__subheading">
          <span class="hero__subheading-text">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'hero_subheading',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult() ?>
        </span>
      </small>
    
    </h1>
    <ul class="hero__statistics">
      <li class="hero__statistics-item">
        <span class="hero__statistics-item-top"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'payout_amount',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
        <span class="hero__statistics-item-bottom"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'paid_for',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
      </li>
      <li class="hero__statistics-item">
        <span class="hero__statistics-item-top"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'number_of_partners',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
        <span class="hero__statistics-item-bottom"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'partners',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
      </li>
      <li class="hero__statistics-item">
        <span class="hero__statistics-item-top"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'number_of_conversions_per_day',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
        <span class="hero__statistics-item-bottom"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'conversions_per_day',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
      </li>
      <li class="hero__statistics-item">
        <span class="hero__statistics-item-top"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'number_years_on_the_market',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
        <span class="hero__statistics-item-bottom"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'years_on_the_market',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
      </li>
      <li class="hero__statistics-item">
        <button class="btn hero__become-partner btn--signup"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'become_a_partner',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></button>
      </li>
    </ul>
  </div>
  <div class="hero__bg-wrap">
    <div class="hero__bg"></div>
  </div>
  <div class="hero__bees">
    <?=$pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'main',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/picture_elm',
      'vector' => 'bee_money_vector',
      'raster' => 'bee_money_raster',
      'imageOptions' => ['class' => 'hero__bee hero__bee--1', 'alt' => "Пчела"]
    ])->getResult() ?>
  
    <?=$pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'main',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/picture_elm',
      'vector' => 'bee_money_vector',
      'raster' => 'bee_money_raster',
      'imageOptions' => ['class' => 'hero__bee hero__bee--2', 'alt' => "Пчела"]
    ])->getResult() ?>
  
    <?=$pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'main',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/picture_elm',
      'vector' => 'bee_money_vector',
      'raster' => 'bee_money_raster',
      'imageOptions' => ['class' => 'hero__bee hero__bee--3', 'alt' => "Пчела"]
    ])->getResult() ?>
  
    <?=$pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'main',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/picture_elm',
      'vector' => 'bee_money_vector',
      'raster' => 'bee_money_raster',
      'imageOptions' => ['class' => 'hero__bee hero__bee--4', 'alt' => "Пчела"]
    ])->getResult() ?>

    
  </div>
</section>

<section class="about" id="about">
  <h1 class="about__heading"><?=$pagesModule->api('pagesWidget', [
      'categoryCode' => 'translate',
      'pageCode' => 'why_us',
      'fieldCode' => 'text',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/field_value'
    ])->getResult()?></h1>
    <?=$pagesModule->api('pagesWidget', [
      'categoryCode' => 'common',
      'pageCode' => 'main',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/picture_elm',
      'vector' => 'bear_in_plane_vector',
      'raster' => 'bear_in_plane_raster',
      'imageOptions' => ['class' => 'about__plane', 'alt' => "Медвед в самолёте"]
    ])->getResult() ?>

  <!-- Slider main container -->
  <div class="swiper about__slider">
    <!-- Additional required wrapper -->
    <ul class="swiper-wrapper about__slider-wrapper">
      <!-- Slides -->
      <li class="swiper-slide about__slide about__slide--dark">
        <h3 class="about__slider-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'stable_payouts',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></h3>
        <ul class="about__slider-transfers">
          <li class="about__slider-transfer">
            <img class="about__slider-transfer-image" src="/img/wap/pay_t.png" alt="#">
          </li>
          <li class="about__slider-transfer">
            <img class="about__slider-transfer-image" src="/img/wap/pay_cilinder.png" alt="#">
          </li>
          <li class="about__slider-transfer">
            <img class="about__slider-transfer-image" src="/img/wap/pay_wiretransfer.png" alt="#">
          </li>
        </ul>
      </li>
      <li class="swiper-slide about__slide about__slide--border">
        <h3 class="about__slider-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'yours_applications',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></h3>
        <p class="about__slider-text"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'applications_for_partners_to_download',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></p>
      </li>
      <li class="swiper-slide about__slide about__slide--empty">
        <h3 class="about__slider-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'convenient_interface',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></h3>
        <p class="about__slider-text"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'title_convenient_interface',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></p>
      </li>
      <li class="swiper-slide about__slide about__slide--grey">
        <h3 class="about__slider-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'professional_support',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></h3>
        <p class="about__slider-text"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'text_professional_support',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></p>
      </li>
      <li class="swiper-slide about__slide about__slide--empty">
        <h3 class="about__slider-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'big_commissions',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></h3>
        <p class="about__slider-text"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'maximum_income_from_partners',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></p>
        <p class="about__slider-text"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'working_only_directly_with_advertisers',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></p>
        <p class="about__slider-text"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'high_conversion',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></p>
      </li>
    </ul>
    <!-- If we need scrollbar -->
    <div class="swiper-scrollbar about__scrollbar"></div>
  </div>
</section>

<section class="verticals" id="verticals">
  <h1 class="heading container verticals__heading"><?= $pagesModule->api('pagesWidget', [
      'categoryCode' => 'verticals',
      'viewBasePath' => $viewBasePath,
      'view' => 'widgets/category'
    ])->getResult();
    ?></h1>
  <div class="swiper verticals__list-wrap">
    <ul class="swiper-wrapper verticals__list">
      <li class="swiper-slide verticals__item">
        <h2 class="verticals__item-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'gambling',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult() ?></h2>
        <img class="verticals__item-podium" src="<?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'gambling',
          'propCode' => 'podium',
          'view' => 'prop_img'
        ])->getResult() ?>" alt="#">
        
        <img class="verticals__item-podium verticals__item-podium--active" src="<?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'gambling',
          'propCode' => 'active_podium',
          'view' => 'prop_img'
        ])->getResult() ?>" alt="#">

        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'gambling',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/picture_elm',
          'vector' => 'bear_podium_vector',
          'raster' => 'bear_podium_raster',
          'imageOptions' => ['class' => 'verticals__item-bear', 'alt' => "#"]
        ])->getResult() ?>
      </li>
      <li class="swiper-slide verticals__item">
        <h2 class="verticals__item-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'mobile_content',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult() ?></h2>
        <img class="verticals__item-podium" src="<?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'mobile_content',
          'propCode' => 'podium',
          'view' => 'prop_img'
        ])->getResult() ?>" alt="#">
        <img class="verticals__item-podium verticals__item-podium--active" src="<?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'mobile_content',
          'propCode' => 'active_podium',
          'view' => 'prop_img'
        ])->getResult() ?>" alt="#">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'mobile_content',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/picture_elm',
          'vector' => 'bear_podium_vector',
          'raster' => 'bear_podium_raster',
          'imageOptions' => ['class' => 'verticals__item-bear', 'alt' => "#"]
        ])->getResult() ?>
      </li>
      <li class="swiper-slide verticals__item">
        <h2 class="verticals__item-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'sp_bs',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult() ?></h2>
          <img class="verticals__item-podium" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'sp_bs',
            'propCode' => 'podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
          <img class="verticals__item-podium verticals__item-podium--active" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'sp_bs',
            'propCode' => 'active_podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'sp_bs',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/picture_elm',
          'vector' => 'bear_podium_vector',
          'raster' => 'bear_podium_raster',
          'imageOptions' => ['class' => 'verticals__item-bear', 'alt' => "#"]
        ])->getResult() ?>
      </li>
      <li class="swiper-slide verticals__item">
        <h2 class="verticals__item-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'betting',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult() ?></h2>
          <img class="verticals__item-podium" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'betting',
            'propCode' => 'podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
          <img class="verticals__item-podium verticals__item-podium--active" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'betting',
            'propCode' => 'active_podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'betting',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/picture_elm',
          'vector' => 'bear_podium_vector',
          'raster' => 'bear_podium_raster',
          'imageOptions' => ['class' => 'verticals__item-bear', 'alt' => "#"]
        ])->getResult() ?>
      </li>
      <li class="swiper-slide verticals__item">
        <h2 class="verticals__item-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'dating',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult() ?></h2>
          <img class="verticals__item-podium" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'dating',
            'propCode' => 'podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
          <img class="verticals__item-podium verticals__item-podium--active" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'dating',
            'propCode' => 'active_podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'dating',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/picture_elm',
          'vector' => 'bear_podium_vector',
          'raster' => 'bear_podium_raster',
          'imageOptions' => ['class' => 'verticals__item-bear', 'alt' => "#"]
        ])->getResult() ?>
      </li>
      <li class="swiper-slide verticals__item">
        <h2 class="verticals__item-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'crypto',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult() ?></h2>
          <img class="verticals__item-podium" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'crypto',
            'propCode' => 'podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
          <img class="verticals__item-podium verticals__item-podium--active" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'crypto',
            'propCode' => 'active_podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'crypto',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/picture_elm',
          'vector' => 'bear_podium_vector',
          'raster' => 'bear_podium_raster',
          'imageOptions' => ['class' => 'verticals__item-bear', 'alt' => "#"]
        ])->getResult() ?>
      </li>
      <li class="swiper-slide verticals__item">
        <h2 class="verticals__item-heading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'installs',
            'fieldCode' => 'name',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult() ?></h2>
          <img class="verticals__item-podium" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'installs',
            'propCode' => 'podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
          <img class="verticals__item-podium verticals__item-podium--active" src="<?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'verticals',
            'pageCode' => 'installs',
            'propCode' => 'active_podium',
            'view' => 'prop_img'
          ])->getResult() ?>" alt="#">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'installs',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/picture_elm',
          'vector' => 'bear_podium_vector',
          'raster' => 'bear_podium_raster',
          'imageOptions' => ['class' => 'verticals__item-bear', 'alt' => "#"]
        ])->getResult() ?>
      </li>
    </ul>
  </div>
  <div class="swiper verticals__text-list-wrap">
    <ul class="swiper-wrapper verticals__text-list">
      <li class="swiper-slide verticals__text-item">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'gambling',
          'propCode' => 'verticals_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </li>
      <li class="swiper-slide verticals__text-item">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'mobile_content',
          'propCode' => 'verticals_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </li>
      <li class="swiper-slide verticals__text-item">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'sp_bs',
          'propCode' => 'verticals_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </li>
      <li class="swiper-slide verticals__text-item">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'betting',
          'propCode' => 'verticals_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </li>
      <li class="swiper-slide verticals__text-item">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'dating',
          'propCode' => 'verticals_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </li>
      <li class="swiper-slide verticals__text-item">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'crypto',
          'propCode' => 'verticals_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </li>
      <li class="swiper-slide verticals__text-item">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'verticals',
          'pageCode' => 'installs',
          'propCode' => 'verticals_text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/prop_multivalue'
        ])->getResult(); ?>
      </li>
    </ul>
    <div class="verticals__pagination"></div>
      <div class="verticals__slider-btns">
          <button class="verticals__slider-btn verticals__slider-btn--prev">
              Предыдущий
              <svg class="verticals__slider-btn-icon" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="m1 1 5 5-5 5" stroke-width="1.5" />
              </svg>
          </button>
          <button class="verticals__slider-btn verticals__slider-btn--next">
              Следующий
              <svg class="verticals__slider-btn-icon" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="m1 1 5 5-5 5" stroke-width="1.5" />
              </svg>
          </button>
      </div>
  </div>
</section>

<section class="forums" id="forums">
  <div class="forums__container">
    <h1 class="forums__heading"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'we_are_on_forums',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/category'
      ])->getResult();
      ?>
      </h1>
    <div class="forums__arrows">
      <svg class="forums__arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="m13.025 1-2.847 2.828 6.176 6.176H0v3.992h16.354l-6.176 6.176L13.025 23 24 12z"></path>
      </svg>
      <svg class="forums__arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="m13.025 1-2.847 2.828 6.176 6.176H0v3.992h16.354l-6.176 6.176L13.025 23 24 12z"></path>
      </svg>
    </div>
    <ul class="forums__list">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'we_are_on_forums',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/we_are_on_forums'
      ])->getResult();
      ?>
    </ul>
    <div class="forums__media media" id="media">
      <h2 class="media__subheading">
        
        <span class="media__subheading-text"><?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'in_media',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/category'
          ])->getResult();
          ?></span>
      
      </h2>
      <ul class="media__list">
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'in_media',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/in_media'
        ])->getResult();
        ?>
      </ul>
    </div>
    <a class="forums__vacancies" href="#" id="vacancies">
      <div class="forums__vacancies-inner">
        <h2 class="forums__vacancies-heading">
          
          <span class="forums__vacancies-heading-number">04</span> <span class="forums__vacancies-heading-text"><?= $pagesModule->api('pagesWidget', [
              'categoryCode' => 'vacancies',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/category'
            ])->getResult(); ?></span>
        
        </h2>
        <p class="forums__vacancies-subheading"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'part_of_the_team',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></p>
        <span class="btn forums__vacancies-btn"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'i_want',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
        <picture>
          <source srcset="/img/wap/bear_newspaper.webp" type="image/webp">
          <source srcset="/img/wap/bear_newspaper.png" type="image/png">
          <img class="forums__vacancies-image" src="/img/wap/bear_newspaper.png" alt="Медвед с газетой">
        </picture>
      </div>
    </a>
  </div>
</section>

<section class="vacancies">
  <div class="vacancies__backdrop"></div>
  <div class="container vacancies__container">
    <button class="vacancies__close-btn">
      <?=$pagesModule->api('pagesWidget', [
        'categoryCode' => 'translate',
        'pageCode' => 'close',
        'fieldCode' => 'text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/field_value'
      ])->getResult()?>
      <svg class="vacancies__close-btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="m1 1 9 9m9 9-9-9m0 0 9-9L1 19" stroke-width="1.5"></path>
      </svg>
    </button>
    <h1 class="vacancies__heading"><?=
      $pagesModule->api('pagesWidget', [
        'categoryCode' => 'translate',
        'pageCode' => 'available_vacancies',
        'fieldCode' => 'text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/field_value'
      ])->getResult()?></h1>
    <ul class="vacancies__list">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'vacancies',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/vacancies'
      ])->getResult();
      ?>
    </ul>
  </div>
</section>
<?php $currentLang = (new \mcms\common\SystemLanguage())->getCurrent();
if($currentLang === 'ru'){ ?>
    <div class="ticker">
        <img class="ticker__body" src="img/wap/ticker.svg">
    </div>

  <?php
}else{
?>
    <div class="ticker">
        <img class="ticker__body" src="">
    </div>
<?php
}
?>

<section class="meetings" id="meetings">
  <div class="meetings__info-block">
    <h1 class="container heading meetings__heading"><?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'events',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/category'
      ])->getResult(); ?></h1>
    <p class="meetings__description">
      
      <?=$pagesModule->api('pagesWidget', [
        'categoryCode' => 'translate',
        'pageCode' => 'text_events_block',
        'fieldCode' => 'text',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/field_value'
      ])->getResult() ?></p>
    <div class="meetings__slider-btns">
      <button class="meetings__slider-btn meetings__slider-btn--prev">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'previous',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        <svg class="meetings__slider-btn-icon" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="m1 1 5 5-5 5" stroke-width="1.5"></path>
        </svg>
      </button>
      <button class="meetings__slider-btn meetings__slider-btn--next">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'next',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        <svg class="meetings__slider-btn-icon" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="m1 1 5 5-5 5" stroke-width="1.5"></path>
        </svg>
      </button>
    </div>
  </div>
  <div class="meetings__slider-wrap">
    <div class="swiper meetings__slider">
      <ul class="swiper-wrapper meetings__slider-wrapper">
        <li class="swiper-slide meetings__slide-placeholder"></li>
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'events',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/events'
        ])->getResult();
        ?>
        <li class="swiper-slide meetings__slide-placeholder"></li>
      </ul>
      <div class="swiper-scrollbar meetings__scrollbar"></div>
    </div>
  </div>
</section>

<section class="reviews" id="reviews">
  <div class="container review__container">
    <div class="reviews__slider-btns">
      <button class="reviews__slider-btn reviews__slider-btn--prev">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'previous',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        <svg class="reviews__slider-btn-icon" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="m1 1 5 5-5 5" stroke-width="1.5"></path>
        </svg>
      </button>
      <button class="reviews__slider-btn reviews__slider-btn--next">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'next',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        <svg class="reviews__slider-btn-icon" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="m1 1 5 5-5 5" stroke-width="1.5"></path>
        </svg>
      </button>
    </div>
    <div class="reviews__slider-box">
      <div class="swiper reviews__slider">
        <!-- Additional required wrapper -->
        <ul class="swiper-wrapper reviews__slider-wrapper">
          <!-- Slides -->
          <?= $pagesModule->api('pagesWidget', [
            'categoryCode' => 'reviews_about',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/reviews'
          ])->getResult();
          ?>
        </ul>
        <div class="swiper-pagination reviews__pagination"></div>
      </div>
    </div>
    <div class="reviews__heading-wrap">
      <div class="reviews__like-icon-box">
        <img class="reviews__like-icon" src="/img/wap/bear_like.svg" alt="Лапа медведа">
      </div>
      <h1 class="heading reviews__heading">
        
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'reviews',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        
        <span class="reviews__heading-shadow"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'reviews',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></span>
      
      </h1>
    </div>
  </div>
</section>
<?php
/*
<section class="faq" id="faq">
  <div class="container">
    <h1 class="faq__heading">
      
      <span class="faq__heading-text">FAQ</span>
      
      <a class="btn faq__btn" href="#">База знаний wap.wiki</a>
    
    </h1>
    <ul class="faq__list">
      <?= $pagesModule->api('pagesWidget', [
        'categoryCode' => 'wap_wiki_faq',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/faq_wap_wiki'
      ])->getResult();
      ?>
    </ul>
  </div>
</section>
 */ ?>
<footer class="footer" id="contacts">
  <div class="footer__inner">
    <div class="container footer__container">
      <a class="footer__logo-link" href="#">
        
        WAP.Click
        <?= $pagesModule->api('pagesWidget', [
          'categoryCode' => 'common',
          'pageCode' => 'main',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/logo_light',
          'imageOptions' => ['class' => 'footer__logo','alt' => "WAP.Click логотип"]
        ])->getResult(); ?>
      
      </a>
      <div class="socials">
        <span class="socials__label"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'social_media',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?>:</span>
        <ul class="socials__list">
          <li class="socials__item">
            <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_vk',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_vk',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
              
              <svg class="socials__link-icon socials__link-icon--vk" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M19.541 1.407c.139-.496 0-.862-.662-.862H16.69c-.556 0-.813.316-.952.664 0 0-1.113 2.905-2.688 4.793-.51.547-.742.72-1.02.72-.14 0-.349-.173-.349-.67V1.407c0-.596-.153-.862-.616-.862h-3.44a.544.544 0 0 0-.557.54c0 .564.788.695.87 2.284v3.453c0 .757-.128.894-.407.894-.741 0-2.545-2.919-3.616-6.259-.208-.65-.418-.912-.977-.912H.75C.125.545 0 .861 0 1.21c0 .62.742 3.7 3.454 7.774 1.808 2.781 4.354 4.29 6.673 4.29 1.391 0 1.563-.335 1.563-.912v-2.103c0-.67.131-.803.572-.803.325 0 .881.174 2.18 1.515 1.483 1.59 1.727 2.303 2.562 2.303h2.187c.625 0 .939-.335.759-.997-.199-.658-.907-1.613-1.846-2.747-.51-.645-1.275-1.34-1.508-1.689-.324-.446-.231-.646 0-1.043 0 0 2.667-4.023 2.944-5.39h.001Z"></path></svg>
            
            </a>
          </li>
          <li class="socials__item">
            <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_fb',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_fb',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
              
              <svg class="socials__link-icon socials__link-icon--fb" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m10.74 11.25.566-3.62H7.768V5.281c0-.99.494-1.955 2.079-1.955h1.607V.244S9.996 0 8.6 0C5.687 0 3.784 1.734 3.784 4.872V7.63H.545v3.619h3.239V20h3.984v-8.75h2.972Z"></path></svg>
            
            </a>
          </li>
          <li class="socials__item">
            <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_linkedin',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_linkedin',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="40" height="40"
                     viewBox="0 0 30 30"
                     style="fill:#fff;">
                    <path d="M10.496,8.403 c0.842,0,1.403,0.561,1.403,1.309c0,0.748-0.561,1.309-1.496,1.309C9.561,11.022,9,10.46,9,9.712C9,8.964,9.561,8.403,10.496,8.403z M12,20H9v-8h3V20z M22,20h-2.824v-4.372c0-1.209-0.753-1.488-1.035-1.488s-1.224,0.186-1.224,1.488c0,0.186,0,4.372,0,4.372H14v-8 h2.918v1.116C17.294,12.465,18.047,12,19.459,12C20.871,12,22,13.116,22,15.628V20z"></path>
                </svg>
            
            </a>
          </li>
          <li class="socials__item">
            <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
              'categoryCode' => 'contacts',
              'pageCode' => 'contact_instagram',
              'propCode' => 'contact_link',
              'viewBasePath' => $viewBasePath,
              'view' => 'widgets/prop_multivalue'
            ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_instagram',
                'fieldCode' => 'name',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/field_value'
              ])->getResult(); ?>
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="-6 -6 36 36" fill="none" style="fill:#FFF;">
                    <path d="M 8 3 C 5.243 3 3 5.243 3 8 L 3 16 C 3 18.757 5.243 21 8 21 L 16 21 C 18.757 21 21 18.757 21 16 L 21 8 C 21 5.243 18.757 3 16 3 L 8 3 z M 8 5 L 16 5 C 17.654 5 19 6.346 19 8 L 19 16 C 19 17.654 17.654 19 16 19 L 8 19 C 6.346 19 5 17.654 5 16 L 5 8 C 5 6.346 6.346 5 8 5 z M 17 6 A 1 1 0 0 0 16 7 A 1 1 0 0 0 17 8 A 1 1 0 0 0 18 7 A 1 1 0 0 0 17 6 z M 12 7 C 9.243 7 7 9.243 7 12 C 7 14.757 9.243 17 12 17 C 14.757 17 17 14.757 17 12 C 17 9.243 14.757 7 12 7 z M 12 9 C 13.654 9 15 10.346 15 12 C 15 13.654 13.654 15 12 15 C 10.346 15 9 13.654 9 12 C 9 10.346 10.346 9 12 9 z"></path>
                </svg>
            
            </a>
          </li>
        </ul>
      </div>
      <div class="contacts">
                    <span class="contacts__label">

                    <?=$pagesModule->api('pagesWidget', [
                      'categoryCode' => 'translate',
                      'pageCode' => 'social_media',
                      'fieldCode' => 'text',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/field_value'
                    ])->getResult()?>

                    <svg class="contacts__label-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m13.025 1-2.847 2.828 6.176 6.176H0v3.992h16.354l-6.176 6.176L13.025 23 24 12z"></path></svg>

                </span>
          <ul class="socials__list">
              <li class="socials__item">
                  <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
                    'categoryCode' => 'contacts',
                    'pageCode' => 'contact_vk',
                    'propCode' => 'contact_link',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                      'categoryCode' => 'contacts',
                      'pageCode' => 'contact_vk',
                      'fieldCode' => 'name',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/field_value'
                    ])->getResult(); ?>

                      <svg class="socials__link-icon socials__link-icon--vk" viewBox="0 0 20 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M19.541 1.407c.139-.496 0-.862-.662-.862H16.69c-.556 0-.813.316-.952.664 0 0-1.113 2.905-2.688 4.793-.51.547-.742.72-1.02.72-.14 0-.349-.173-.349-.67V1.407c0-.596-.153-.862-.616-.862h-3.44a.544.544 0 0 0-.557.54c0 .564.788.695.87 2.284v3.453c0 .757-.128.894-.407.894-.741 0-2.545-2.919-3.616-6.259-.208-.65-.418-.912-.977-.912H.75C.125.545 0 .861 0 1.21c0 .62.742 3.7 3.454 7.774 1.808 2.781 4.354 4.29 6.673 4.29 1.391 0 1.563-.335 1.563-.912v-2.103c0-.67.131-.803.572-.803.325 0 .881.174 2.18 1.515 1.483 1.59 1.727 2.303 2.562 2.303h2.187c.625 0 .939-.335.759-.997-.199-.658-.907-1.613-1.846-2.747-.51-.645-1.275-1.34-1.508-1.689-.324-.446-.231-.646 0-1.043 0 0 2.667-4.023 2.944-5.39h.001Z"></path></svg>

                  </a>
              </li>
              <li class="socials__item">
                  <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
                    'categoryCode' => 'contacts',
                    'pageCode' => 'contact_fb',
                    'propCode' => 'contact_link',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                      'categoryCode' => 'contacts',
                      'pageCode' => 'contact_fb',
                      'fieldCode' => 'name',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/field_value'
                    ])->getResult(); ?>

                      <svg class="socials__link-icon socials__link-icon--fb" viewBox="0 0 12 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m10.74 11.25.566-3.62H7.768V5.281c0-.99.494-1.955 2.079-1.955h1.607V.244S9.996 0 8.6 0C5.687 0 3.784 1.734 3.784 4.872V7.63H.545v3.619h3.239V20h3.984v-8.75h2.972Z"></path></svg>

                  </a>
              </li>
              <li class="socials__item">
                  <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
                    'categoryCode' => 'contacts',
                    'pageCode' => 'contact_linkedin',
                    'propCode' => 'contact_link',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                      'categoryCode' => 'contacts',
                      'pageCode' => 'contact_linkedin',
                      'fieldCode' => 'name',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/field_value'
                    ])->getResult(); ?>
                      <svg xmlns="http://www.w3.org/2000/svg"
                           width="40" height="40"
                           viewBox="0 0 30 30"
                           style="fill:#fff;">
                          <path d="M10.496,8.403 c0.842,0,1.403,0.561,1.403,1.309c0,0.748-0.561,1.309-1.496,1.309C9.561,11.022,9,10.46,9,9.712C9,8.964,9.561,8.403,10.496,8.403z M12,20H9v-8h3V20z M22,20h-2.824v-4.372c0-1.209-0.753-1.488-1.035-1.488s-1.224,0.186-1.224,1.488c0,0.186,0,4.372,0,4.372H14v-8 h2.918v1.116C17.294,12.465,18.047,12,19.459,12C20.871,12,22,13.116,22,15.628V20z"></path>
                      </svg>

                  </a>
              </li>
              <li class="socials__item">
                  <a class="socials__link" href="<?=$pagesModule->api('pagesWidget', [
                    'categoryCode' => 'contacts',
                    'pageCode' => 'contact_instagram',
                    'propCode' => 'contact_link',
                    'viewBasePath' => $viewBasePath,
                    'view' => 'widgets/prop_multivalue'
                  ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                      'categoryCode' => 'contacts',
                      'pageCode' => 'contact_instagram',
                      'fieldCode' => 'name',
                      'viewBasePath' => $viewBasePath,
                      'view' => 'widgets/field_value'
                    ])->getResult(); ?>
                      <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36" viewBox="-6 -6 36 36" fill="none" style="fill:#FFF;">
                          <path d="M 8 3 C 5.243 3 3 5.243 3 8 L 3 16 C 3 18.757 5.243 21 8 21 L 16 21 C 18.757 21 21 18.757 21 16 L 21 8 C 21 5.243 18.757 3 16 3 L 8 3 z M 8 5 L 16 5 C 17.654 5 19 6.346 19 8 L 19 16 C 19 17.654 17.654 19 16 19 L 8 19 C 6.346 19 5 17.654 5 16 L 5 8 C 5 6.346 6.346 5 8 5 z M 17 6 A 1 1 0 0 0 16 7 A 1 1 0 0 0 17 8 A 1 1 0 0 0 18 7 A 1 1 0 0 0 17 6 z M 12 7 C 9.243 7 7 9.243 7 12 C 7 14.757 9.243 17 12 17 C 14.757 17 17 14.757 17 12 C 17 9.243 14.757 7 12 7 z M 12 9 C 13.654 9 15 10.346 15 12 C 15 13.654 13.654 15 12 15 C 10.346 15 9 13.654 9 12 C 9 10.346 10.346 9 12 9 z"></path>
                      </svg>

                  </a>
              </li>
          </ul>
        <ul class="contacts__list">
          <li class="contacts__item">
            <svg class="contacts__item-icon" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M19.593 16.5H2.407c-.476 0-.952-.373-.952-.933V3.5c0-.467.38-1 .952-1h17.186c.476 0 .953.44.953 1v12.067c0 .56-.381.933-.953.933Z" stroke="#fff" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="m18.273 5-6.788 6.766c-.324.312-.728.312-.97 0L3.727 5" stroke="#fff" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span class="contacts__item-label">E-mail:</span>
            <div class="contacts__item-link-wrap">
              <a class="contacts__item-link" href="<?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_email',
                'propCode' => 'contact_link',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_email',
                  'fieldCode' => 'name',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/field_value'
                ])->getResult(); ?></a>
            </div>
          </li>
          <li class="contacts__item">
            <svg class="contacts__item-icon" viewBox="0 0 20 18" fill="#fff" xmlns="http://www.w3.org/2000/svg">
              <path d="M19.472.337a1.485 1.485 0 0 0-1.498-.23L.912 6.927c-.276.111-.51.304-.673.551A1.444 1.444 0 0 0 .283 9.13c.176.238.42.418.702.515l3.682 1.266 1.995 6.53c.004.013.016.022.022.034a.467.467 0 0 0 .304.276c.01.004.016.012.026.014h.005l.003.001a.43.43 0 0 0 .222-.011c.008-.002.015-.002.024-.005a.471.471 0 0 0 .182-.115c.006-.007.015-.007.02-.013l2.87-3.136 4.188 3.21a1.474 1.474 0 0 0 2.336-.857l3.107-15.102a1.431 1.431 0 0 0-.5-1.401ZM7.703 12.15l-.673 3.24-1.405-4.6L12.592 7.2l-4.76 4.712a.468.468 0 0 0-.129.239Zm8.228 4.499a.506.506 0 0 1-.33.376.504.504 0 0 1-.49-.073l-4.536-3.479a.48.48 0 0 0-.644.057L7.934 15.71l.672-3.231 6.847-6.78a.47.47 0 0 0-.229-.791.48.48 0 0 0-.328.04l-9.869 5.09-3.73-1.285a.5.5 0 0 1-.344-.463.498.498 0 0 1 .318-.489L18.33.981a.515.515 0 0 1 .532.082.493.493 0 0 1 .173.488l-3.105 15.1v-.001Z"
                    fill="#fff"></path>
            </svg>
            <span class="contacts__item-label">Telegram:</span>
            <div class="contacts__item-link-wrap">
              <a class="contacts__item-link" href="<?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_tg',
                'propCode' => 'contact_link',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_tg',
                  'fieldCode' => 'name',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/field_value'
                ])->getResult(); ?></a>
            </div>
          </li>
          <li class="contacts__item">
            <svg class="contacts__item-icon" viewBox="0 0 20 20" fill="#fff" xmlns="http://www.w3.org/2000/svg">
              <path d="M12.73 10.22a4.315 4.315 0 0 0-1.108-.508 17.06 17.06 0 0 0-.82-.223 17.63 17.63 0 0 0-.987-.24 9.406 9.406 0 0 1-1.446-.429 1.892 1.892 0 0 1-.78-.573 1.282 1.282 0 0 1-.252-.83c0-.31.101-.61.29-.854a1.93 1.93 0 0 1 .878-.613 4.264 4.264 0 0 1 1.525-.237c.405-.006.809.05 1.197.162.283.08.55.211.785.388.175.127.321.288.432.473a.476.476 0 1 0 .839-.451 2.548 2.548 0 0 0-.676-.766c-.329-.25-.7-.438-1.097-.554-.48-.141-.98-.21-1.48-.204a5.192 5.192 0 0 0-1.868.3c-.51.183-.96.505-1.297.93-.312.409-.48.91-.48 1.425-.014.51.145 1.009.452 1.416.309.387.712.687 1.172.87.391.151.793.277 1.202.375.007.003.014.01.023.012.174.052.455.122.857.21.165.035.32.074.474.113.029.008.067.016.094.025.011.004.023.003.034.005.229.059.449.12.652.182.302.09.592.22.858.39.2.13.368.305.49.51.124.239.183.506.175.775.017.356-.087.707-.296.995a2.12 2.12 0 0 1-.96.713 4.07 4.07 0 0 1-1.605.28 3.975 3.975 0 0 1-1.842-.382 2.178 2.178 0 0 1-.763-.679 1.387 1.387 0 0 1-.272-.743.476.476 0 1 0-.953 0c.016.462.17.909.441 1.283.286.413.666.75 1.109.984a4.89 4.89 0 0 0 2.28.489 4.98 4.98 0 0 0 1.972-.353 3.034 3.034 0 0 0 1.37-1.04 2.501 2.501 0 0 0 .471-1.564 2.337 2.337 0 0 0-1.09-2.061Zm6.643 1.464A9.52 9.52 0 0 0 8.316.626a5.715 5.715 0 0 0-7.69 7.69 9.519 9.519 0 0 0 11.058 11.057 5.715 5.715 0 0 0 7.69-7.69Zm-2.789 6.775a4.762 4.762 0 0 1-4.594 0 .481.481 0 0 0-.323-.05A8.568 8.568 0 0 1 1.591 8.332a.475.475 0 0 0-.05-.322A4.763 4.763 0 0 1 8.01 1.543a.46.46 0 0 0 .323.05 8.568 8.568 0 0 1 10.075 10.074.476.476 0 0 0 .05.323 4.762 4.762 0 0 1-1.873 6.468Z"
                    fill="#fff"></path>
            </svg>
            <span class="contacts__item-label">Skype:</span>
            <div class="contacts__item-link-wrap">
              <a class="contacts__item-link" href="<?=$pagesModule->api('pagesWidget', [
                'categoryCode' => 'contacts',
                'pageCode' => 'contact_skype',
                'propCode' => 'contact_link',
                'viewBasePath' => $viewBasePath,
                'view' => 'widgets/prop_multivalue'
              ])->getResult(); ?>"><?=$pagesModule->api('pagesWidget', [
                  'categoryCode' => 'contacts',
                  'pageCode' => 'contact_skype',
                  'fieldCode' => 'name',
                  'viewBasePath' => $viewBasePath,
                  'view' => 'widgets/field_value'
                ])->getResult(); ?></a>
            </div>
          </li>
        </ul>
      </div>
      <?php
      $str = "Wap.Click &copy; 2010-{year}. Все права защищены.1";
      ?>
        
        <p class="footer__copyright"><?=str_replace('{year}',date("Y"),
          $pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'footer_copyright',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult())?></p>
      <?php
     echo Html::a("","#",['class'=>"footer__notification"]);
        /*echo Html::a($pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'privacy_policy_text',
          'fieldCode' => 'name',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult(),"#",['class'=>"footer__notification"]);*/ ?>
  
      <?=$pagesModule->api('pagesWidget', [
        'categoryCode' => 'common',
        'pageCode' => 'main',
        'viewBasePath' => $viewBasePath,
        'view' => 'widgets/picture_elm',
        'vector' => 'bear_throne_vector',
        'raster' => 'bear_throne_raster',
        'imageOptions' => ['class' => 'footer__bear', 'alt' => "Медвед на троне"]
      ])->getResult() ?>

    </div>
  </div>
</footer>

<?= $moduleUser->api('signupForm')->getResult(); ?>
<?= $moduleUser->api('loginForm')->getResult(); ?>
<?= $moduleUser->api('passwordResetRequestForm')->getResult(); ?>

<form class="form form-step form-signin form-step-signin" action="#" method="#">
  <div class="form__backdrop"></div>
  <div class="container form__box" style="max-width: 544px">
    <div class="form__container">
      <button class="form__close-btn" type="button">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'close',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        <svg class="form__close-btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="m1 1 9 9m9 9-9-9m0 0 9-9L1 19" stroke-width="1.5"></path>
        </svg>
      </button>
      <h2 class="form__heading"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'sign_in',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></h2>
      <hr>
      <p class="form-step__text"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'title_workin_with_1',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></p>
      <button class="btn form__btn btn--login-form" style="text-align: center"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'authorization_here',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></button>
      <hr>
      <p class="form-step__text"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'title_workin_with_2',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></p>
      <a class="btn form__btn" href="https://partners.wap.click/login" style="text-align: center"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'authorization_here',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></a>
      <div class="form__bottom-block">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'dont_have_an_account_yet',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>&nbsp;-&nbsp;<a class="form__bottom-link" href="#"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'registration',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></a>
      </div>
    </div>
  </div>
</form>

<form class="form form-step form-signup form-step-signup" action="#" method="#">
  <div class="form__backdrop"></div>
  <div class="container form__box" style="max-width: 544px">
    <div class="form__container">
      <button class="form__close-btn" type="button">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'close',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        <svg class="form__close-btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="m1 1 9 9m9 9-9-9m0 0 9-9L1 19" stroke-width="1.5"></path>
        </svg>
      </button>
      <h2 class="form__heading"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'registration',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></h2>
      <hr>
      <p class="form-step__text"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'title_workin_with_1',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></p>
      <a class="btn form__btn btn--registration-form" style="text-align: center"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'register_here',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></a>
      <hr>
      <p class="form-step__text"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'title_workin_with_2',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></p>
      <a class="btn form__btn" href="https://partners.wap.click/registration" style="text-align: center"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'register_here',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?></a>
      <div class="form__bottom-block">
          <?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'already_have_an_account',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?>&nbsp;-&nbsp;<a class="form__bottom-link" href="#"><?=$pagesModule->api('pagesWidget', [
            'categoryCode' => 'translate',
            'pageCode' => 'sign_in',
            'fieldCode' => 'text',
            'viewBasePath' => $viewBasePath,
            'view' => 'widgets/field_value'
          ])->getResult()?></a>
      </div>
    </div>
  </div>
</form>

    
    <div class="modal fade actions form form-step form-signin form-modal-success" id="success-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="form__backdrop"></div>
        <div class="modal-dialog container form__box" role="document" style="max-width: 544px;min-height: auto">
            <div class="modal-content form__container " style="padding-top: 40px;padding-bottom: 40px;">
                <div class="modal-header">
                    <button class="form__close-btn" type="button">
                      <?=$pagesModule->api('pagesWidget', [
                        'categoryCode' => 'translate',
                        'pageCode' => 'close',
                        'fieldCode' => 'text',
                        'viewBasePath' => $viewBasePath,
                        'view' => 'widgets/field_value'
                      ])->getResult()?>
                        <svg class="form__close-btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="m1 1 9 9m9 9-9-9m0 0 9-9L1 19" stroke-width="1.5"></path>
                        </svg>
                    </button>
                    <h2 class="modal-title  success-title form__heading" id="myModalLabel"><i class="icon-login"></i></h2>
                    <hr />
                </div>
                <div class="modal-body">
                    <div class="success_message">
                        <div class="mess_title"><i class="icon-success"></i><span class="success-action form-step__text"></span></div>
                        <span class="success-message form-step__text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
<?php
$css = <<<CSS
.policy .form__container h3 {
  margin-bottom: 10px;
  font-family: "Inter",sans-serif;
  font-size: 16px;
  font-weight: 700;
  line-height: 22px;
  color: #28282e;
}

.policy .form__container p {
margin-bottom: 30px;
font-size: 14px;
font-weight: 400;
line-height: 20px;
font-family: "Inter",sans-serif;
color: #28282e;
}

.form-control {
 display:block;
 width:100%;
/* border:1px solid #D6D6D6;*/
 background-color:#F2F2F2;
/* padding-left:70px;*/
 padding-right:15px;
 height:50px;
 border-radius:5px;
 font-size:14px;
 font-weight:300;
 padding-top:11px;
 padding-bottom:10px;
/* transition:box-shadow 0.3s*/
}
/*.form-control:focus {
 box-shadow:0 0 10px rgba(0,0,0,0.2)
}*/
.form-group {
 margin-bottom:12px
}
.form-group:not(.checkbox) {
 position:relative
}
.form-group:not(.checkbox) .help-block {
 position:absolute;
 top:1px;
 left:43px;
 right:1px;
 line-height:48px;
 padding-left:5px;
 font-size:12px;
 display:none;
 background-color:#F2F2F2;
 border-radius:5px;
 white-space:nowrap;
 text-overflow:ellipsis;
 overflow:hidden;
 padding-right:10px;
 font-family: "Inter",sans-serif;
}
.form-group:not(.checkbox):after {
 content:"";
 display:block;
 height:48px;
/* width:50px;*/
 position:absolute;
 left:1px;
 top:1px;
/* border-right:1px solid #D6D6D6;
 background-color:#fff;*/
 border-radius:6px 0 0 6px;
 z-index:1
}
.form-group:not(.checkbox):before {
 position:absolute;
 left:2px;
 width:50px;
 text-align:center;
 top:50%;
 margin-top:-11px;
 z-index:2;
 font-size:20px;
 color:#ABABAB
}
.form-group:not(.checkbox).input-password:before {
 font-size:22px;
 margin-top:-12px
}
.form-group:not(.checkbox).input-contacts:before {
 font-size:22px;
 margin-top:-11px
}
.form-group:not(.checkbox).input-currency:before {
 font-size:22px;
 margin-top:-12px
}
.form-group:not(.checkbox).has-error .help-block {
 display:block;
 color:#DE2F44
}
.form-group:not(.checkbox).has-error:before {
 color:#DE2F44
}
.form-group:not(.checkbox).has-success:before {
 color:#76C263
}
CSS;

$this->registerCss($css);
?>

<div class="form policy">
  <div class="form__backdrop"></div>
  <div class="container form__box">
    <div class="form__container">
      <button class="form__close-btn" type="button">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'close',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult()?>
        <svg class="form__close-btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="m1 1 9 9m9 9-9-9m0 0 9-9L1 19" stroke-width="1.5"></path>
        </svg>
      </button>
      <h2 class="form__heading"><?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'privacy_policy_text',
          'fieldCode' => 'name',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult(); ?></h2>
      <div class="form__text-box" data-simplebar="" data-simplebar-auto-hide="false">
        <?=$pagesModule->api('pagesWidget', [
          'categoryCode' => 'translate',
          'pageCode' => 'privacy_policy_text',
          'fieldCode' => 'text',
          'viewBasePath' => $viewBasePath,
          'view' => 'widgets/field_value'
        ])->getResult() ?>
      </div>
    </div>
  </div>
</div>
