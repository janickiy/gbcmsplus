<?php
use kop\y2sp\ScrollPager;
use mcms\common\helpers\Link;
use yii\helpers\Html;
use yii\widgets\ListView;

/**
 * @var yii\data\ActiveDataProvider $notificationsDataProvider Дата провайдер видимых сообщений.
 * @var integer $unViewedCount Количество непросмотренных сообщений
 * @var array $modules
 * @var string $clearUrl
 * @var string $readAllUrl
 * @var string|null $showAllUrl
 */
?>
<ul class="navbar-nav nav">
  <li class="header-notify">
    <a href="#"><i class="glyphicon glyphicon-bullhorn"><?php if ($unViewedCount > 0): ?><span
          class="badge "><?= $unViewedCount ?><?php endif; ?></span></i></a>

    <div class="header-notify__collapse right">
      <div class="header-notify__wrap">
        <div class="header-notify__wrap-header">
          <h4><?= Yii::_t('partners.main.notification') ?></h4>

          <div class="pull-right">
            <div class="" data-toggle="buttons">
              <label id="notify-close" class="btn btn-default btn-sm" data-read-all-url="<?= $readAllUrl ?>">
                <i class="glyphicon glyphicon-remove"></i>
              </label>
            </div>
          </div>
        </div>
        <div class="header-notify__list">
          <?= ListView::widget([
            'dataProvider' => $notificationsDataProvider,
            'options' => [
              'tag' => 'ul',
              'class' => 'header-notifications-list',
            ],
            'itemOptions' => [
              'class' => 'item',
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
        </div>
        <div class="header-notify__wrap-footer">
          <?= $showAllUrl ? Link::get($showAllUrl, [], [], Html::button(Yii::_t('partners.main.show_all'), ['class' => 'btn btn-success pull-left'])) : '' ?>
          <?= Html::button('<span class="glyphicon glyphicon-trash"></span>' . Yii::_t('partners.main.clear'), [
            'class' => 'btn btn-default pull-right',
            'type' => 'button',
            'id' => 'notify-clear',
            'data-url' => $clearUrl,
          ]) ?>
        </div>
      </div>
    </div>
  </li>
</ul>