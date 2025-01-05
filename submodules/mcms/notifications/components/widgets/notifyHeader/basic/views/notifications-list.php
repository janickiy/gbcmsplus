<?php
use mcms\common\helpers\Html;
use yii\widgets\ListView;
use kop\y2sp\ScrollPager;

?>

<li class="notify-header" data-read-all-url="<?= $readAllUrl ?>" data-settings-url="<?= $settingsUrl ?>">
  <span class="notify-header_top">
    <i class="icon-news">
      <?php if ($unViewedCount): ?>
        <span class="count_notify"><?= $unViewedCount ?></span>
      <?php endif; ?>
    </i>
  </span>

  <div class="header-notify__collapse <?= $notificationsDataProvider->getTotalCount() == 0 ? 'empty' : ''; ?>">
    <div class="header-notify__wrap">
      <div class="header-notify__wrap-header">
        <h4><?= Yii::_t('partners.main.notification') ?></h4>

        <div class="settings_bar">
          <span id="notify-settings"><i class="icon-options"></i></span>
          <span id="notify-close"><i class="icon-cancel_4"></i></span>
        </div>
      </div>
      <div class="header-notify__list">
        <div class="if_empty" >
          <span><i class="icon-news"><i
                class="icon-cancel_4"></i></i><?= Yii::_t("notifications.main.empty_notifications") ?></span>
        </div>
        <?= ListView::widget([
          'dataProvider' => $notificationsDataProvider,
          'options' => [
            'tag' => 'ul',
          ],
          'itemOptions' => [
            'class' => 'item',
          ],
          'emptyText' => '',
          'layout' => "{items}",
          'itemView' => "notify",
          'viewParams' => [
            'modules' => $modules
          ],
          'pager' => [
            'class' => ScrollPager::class,
            'container' => '.header-notify__list',
            'triggerTemplate' => '<div class="col-md-12 load-more-notify"><a class="btn btn-default">{text}</a></div>',
            'triggerText' => Yii::_t('notifications.main.load_more'),
            'noneLeftText' => '',
            'next' => '.notify-header .next a',
            'enabledExtensions' => [
              ScrollPager::EXTENSION_TRIGGER,
              ScrollPager::EXTENSION_SPINNER,
              ScrollPager::EXTENSION_NONE_LEFT,
            ],
            'eventOnRendered' => 'function() { this.next(); }',
          ]
        ]); ?>
      </div>
      <div class="header-notify__wrap-footer">
        <?= $showAllUrl
          ? Html::a(Yii::_t('partners.main.show_all'), $showAllUrl, ['class' => 'btn btn-success pull-left', 'id' => 'read-all-button']) : ''; ?>
        <?= Html::button('<span class="icon-delete"></span>' . Yii::_t('partners.main.clear'), [
          'class' => 'clear_notify pull-right',
          'type' => 'button',
          'id' => 'notify-clear',
          'data-url' => $clearUrl,
        ]) ?>
      </div>
    </div>
  </div>
</li>