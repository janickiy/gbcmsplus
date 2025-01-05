<?php
use kop\y2sp\ScrollPager;
use mcms\common\helpers\Link;
use yii\helpers\Html;
use mcms\common\helpers\Html as OurHtml;
use yii\widgets\ListView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var yii\data\ActiveDataProvider $notificationsDataProvider Дата провайдер видимых сообщений.
 * @var integer $unViewedCount Количество непросмотренных сообщений
 * @var array $modules
 * @var string $clearUrl
 * @var string $readAllUrl
 * @var string|null $showAllUrl
 */
?>

<div class="notification-wrapper pull-right">
  <span id="activity" class="activity-dropdown">
    <i class="icon-news"></i>
    <?php if ($unViewedCount > 0): ?>
      <b class="badge bounceIn animated" id="activity-count"><?= $unViewedCount ?></b>
    <?php endif; ?>
  </span>
  
  <div class="ajax-dropdown" id="notification-list" data-unread-count-selector="#activity-count" data-pjax-notify-list-container="#notificationListPjax">
    
    <div class="clearfix">
      <h4 class="pull-left"><?= Yii::_t('partners.main.notification') ?></h4>
      <?=Html::a(Yii::_t('notifications.main.view_all'), ['/notifications/taken-notifications/view'], ['class'=>'btn btn-info btn-sm pull-right']) ?>
    </div>
    
    <div class="ajax-notifications custom-scroll">
      <?php Pjax::begin(['options' => ['id' => 'notificationListPjax']]); ?>
      <?= ListView::widget([
        'dataProvider' => $notificationsDataProvider,
        'options' => [
          'tag' => 'ul',
          'class' => 'notification-body',
        ],
        'itemOptions' => [
          //        'class' => 'item',
          'tag' => false,
        ],
        'emptyText' => '',
        'layout' => "{items}\n{pager}",
        'itemView' => "notify",
        'viewParams' => [
          'modules' => $modules,
        ],
        'pager' => [
          'class' => ScrollPager::class,
          'container' => '.header-notifications-list',
          'paginationSelector' => '.header-notify__list .pagination',
          'triggerTemplate' => '<div class="col-md-12 load-more-notify"><a class="btn btn-default">{text}</a></div>',
          'triggerText' => Yii::_t('notifications.main.load_more'),
          'noneLeftText' => '',
          'next' => '.header-notify .next a',
          'enabledExtensions' => [
            ScrollPager::EXTENSION_TRIGGER,
            ScrollPager::EXTENSION_SPINNER,
            ScrollPager::EXTENSION_NONE_LEFT,
          ],
          'eventOnRendered' => 'function() { this.next(); }',
        ],
      ]); ?>
      <?php Pjax::end(); ?>
    </div>
    
    <?= $showAllUrl ? Link::get($showAllUrl, [], [], Html::button(Yii::_t('partners.main.show_all'), ['class' => 'btn btn-success pull-left'])) : '' ?>
    <span class="btn-group pull-right">
      <?php if (OurHtml::hasUrlAccess(['/notifications/notifications/read-all'])): ?>
        <?= Html::a('<i class="fa fa-check-circle"></i>',  '#', [
          'class' => 'btn btn-xs btn-default',
          'data-pjax' => 1,
          'title' => Yii::_t('notifications.notifications.mark_all_as_read'),
          'id' => 'notify-mark-all-as-read',
          'data-read-all-url' => Url::to(['/notifications/notifications/read-all']),
        ])?>
      <?php endif; ?>
      <?= Html::a('<i class="fa fa-refresh"></i>',  '#', [
        'class' => 'btn btn-xs btn-default',
        'data-pjax' => 1,
        'id' => 'notify-refresh',
      ])?>
    </span>
  
  </div>
</div>