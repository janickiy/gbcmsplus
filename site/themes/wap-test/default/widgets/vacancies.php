<?php
/** @var $data \mcms\pages\models\Page[] */
?>

<?php /** @var \mcms\pages\models\Page $page */
foreach ($data as $page) { ?>
    <li class="vacancies__item">
        <a class="vacancies__link" href="<?=\yii\helpers\ArrayHelper::getValue($page->getPropByCode('vacancy_url'), 'multilang_value');?>">
            <h2 class="vacancies__link-heading"><?=\yii\helpers\Html::encode($page->name)?></h2>
            <svg class="vacancies__link-arrow" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="m1 1 5 5-5 5" stroke-width="1.5"></path>
            </svg>
            <span class="vacancies__link-salary"><?=\yii\helpers\ArrayHelper::getValue($page->getPropByCode('vacancy_price'), 'multilang_value');?></span>
        </a>
    </li>
<?php } ?>

