<?php

use yii\widgets\Pjax;
use mcms\partners\assets\DomainsAsset;
use mcms\partners\components\widgets\DomainCreateFormWidget;

DomainsAsset::register($this);

/* @var mcms\common\web\View $this */
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-xs-7 left-col">
      <div class="bgf">
        <div class="title">
          <h2 class="h_link"><?= Yii::_t('main.domains') ?></h2>
          <a href="#" data-toggle="modal" data-target="#parkingModal" class="h_link"><i class="icon-plus1"></i><?= Yii::_t('domains.add_domain') ?></a>
        </div>
        <?php Pjax::begin(['id' => 'domainsPjaxContainer']); ?>

          <?php echo $sourcesDataProvider->totalCount === 0
          ? $this->render('_empty')
          : $this->render('_grid', compact('sourcesDataProvider')); ?>

        <?php Pjax::end(); ?>
      </div>

    </div>
    <div class="col-xs-5 right-col">
      <div class="bgf">
        <div class="title">
          <h2><?= Yii::_t('domains.domains_faq_title') ?></h2>
        </div>
        <div class="content__position">
          <div class="gray" style="margin-bottom: -10px;"><?= Yii::_t('domains.domains_faq_line_1') ?></div>
          <ul class="decor gray">
            <li><?= Yii::_t('domains.domains_faq_list_2') ?></li>
          </ul>
        </div>
      </div>

    </div>
  </div>

  <?= DomainCreateFormWidget::widget() ?>
</div>
