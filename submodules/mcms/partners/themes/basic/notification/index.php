<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use yii\widgets\ListView;
use yii\widgets\LinkPager;
use mcms\partners\assets\basic\NotificationAsset;

/** @var array $filterDatePeriods */
/** @var \mcms\partners\models\NotificationForm $notificationForm */
NotificationAsset::register($this);
?>

<div class="container">
  <div class="bgf news">

    <?php Pjax::begin(['id' => 'notify-list']); ?>

    <?php $form = ActiveForm::begin([
      'options' => [
        'data-pjax' => true
      ],
      'id' => 'notification-form',
    ]); ?>

    <div class="row no_m">
      <div class="col-xs-4 no_p">
          <?= $form->field($notificationForm, 'typeId', [
            'options' => ['class' => 'btn-group btn-group_primary'],
            'template' => "{label}\n{beginWrapper}\n{input}\n\n{endWrapper}\n{hint}",
          ])->radioList($notificationTypes, [
            'id' => 'notifications_type',
            'data-toggle' => 'buttons',
            'item' => function ($index, $label, $name, $checked, $value) {
              return '<label class="btn btn-sm ' . ($checked ? ' active' : '') . '">' .
              Html::radio($name, $checked, ['value' => $value]) . $label . '</label>';
            },
          ])->label(false) ?>
      </div>

      <div class="col-xs-4 text-center no_p">
        <div class="btn-group btn-group_custom change_date-period" data-toggle="buttons">
          <label class="btn btn-sm<?= $notificationForm->dateperiod == 'today' ? ' active' : '' ?>">
            <input
              type="radio"
              name="NotificationForm[dateperiod]"
              value="today"
              data-start="0"
              data-end="0"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
              autocomplete="off"
              <?= $notificationForm->dateperiod == 'today' ? 'checked' : '' ?>
            ><?= Yii::_t('partners.notifications.today') ?>
          </label>
          <label class="btn btn-sm<?= $notificationForm->dateperiod == 'yesterday' ? ' active' : '' ?>">
            <input
              type="radio"
              name="NotificationForm[dateperiod]"
              value="yesterday"
              data-start="-1d"
              data-end="-1d"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
              autocomplete="off"
              <?= $notificationForm->dateperiod == 'yesterday' ? 'checked' : '' ?>
            ><?= Yii::_t('partners.notifications.yesterday') ?>
          </label>
          <label class="btn btn-sm<?= $notificationForm->dateperiod == 'week' ? ' active' : '' ?>">
            <input
              type="radio"
              name="NotificationForm[dateperiod]"
              value="week"
              data-start="-1w"
              data-end="0"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
              autocomplete="off"
              <?= $notificationForm->dateperiod == 'week' ? 'checked' : '' ?>
            ><?= Yii::_t('partners.notifications.week') ?>
          </label>
          <label class="btn btn-sm<?= $notificationForm->dateperiod == 'month' ? ' active' : '' ?>">
            <input
              type="radio"
              name="NotificationForm[dateperiod]"
              value="month"
              data-start="-1m"
              data-end="0"
              data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
              data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
              autocomplete="off"
              <?= $notificationForm->dateperiod == 'month' ? 'checked' : '' ?>
            ><?= Yii::_t('partners.notifications.month') ?>
          </label>
        </div>
      </div>
      <div class="col-xs-4 no_p">
        <?php
        $notificationForm->dateBegin = date('d.m.Y', strtotime($notificationForm->dateBegin));
        $notificationForm->dateEnd = date('d.m.Y', strtotime($notificationForm->dateEnd));
        echo DatePicker::widget([
          'model' => $notificationForm,
          'attribute' => 'dateBegin',
          'attribute2' => 'dateEnd',
          'type' => DatePicker::TYPE_RANGE,
          'layout' => '<span class="input-group-addon">' .
            $notificationForm->getAttributeLabel('dateBegin') .
            '</span>' .
            '{input1}{separator}{input2}',
          'options' => [
            'class' => 'hidden_mobile'
          ],
          'options2' => [
            'class' => 'hidden_mobile'
          ],
          'separator' =>  Yii::_t('main.to'),
          'pluginOptions' => [
            'format' => 'dd.mm.yyyy',
          ],
          'pluginEvents' => [
            'changeDate' => 'function(e) { setDpDate(e.target.id, true); }'
          ],
        ]); ?>
        <div id="dp_mobile" class="input-group input-daterange date_filter">
          <input id="m_notificationform-datebegin" type="date" class="form-control" value="">
          <input id="m_notificationform-dateend" type="date" class="form-control" value="">
        </div>
      </div>
    </div>


    <div class="content__position">
      <div class="row">
        <div class="col-xs-4">
          <h2 class="notify-count"><?= Yii::_t('partners.notifications.notifications', ['n' => $notificationsDataProvider->totalCount]) ?></h2>
        </div>
        <div class="col-xs-8 text-right news_category">

            <?= $form->field($notificationForm, 'categoryId')->checkboxList($notificationCategories, [
              'id' => 'notifications_category',
              'class' => 'btn-group',
              'data-toggle' => 'buttons',
              'item' => function ($index, $label, $name, $checked, $value) {
                return '<label class="btn' . ($checked ? ' active' : '') . '">' .
                Html::checkbox($name, $checked, ['value' => $value]) . $label . '</label>';
              },
            ])->label(false) ?>

      </div>
      </div>
      <?php if(!$notificationsDataProvider->totalCount):?>

        <div class="empty_data">
          <i class="icon-no_data"></i>
          <span><?= Yii::_t('main.no_results_found') ?></span>
        </div>

      <?php else:?>

      <div class="news_list-wrap">
        <div class="row">
          <div class="col-xs-7">
            <?= ListView::widget([
              'dataProvider' => $notificationsDataProvider,
              'options' => [
                'tag' => 'ul',
                'class' => 'news_list',
              ],
              'itemOptions' => [
                'tag' => false,
              ],
              'emptyText' => '',
              'layout' => "{items}",
              'itemView' => "notify",
              'viewParams' => [
                'modules' => $modules
              ]
            ]); ?>
          </div>
        </div>
      </div>
      <?php endif;?>
    </div>
    <?php ActiveForm::end(); ?>
    <div class="content__position news-footer">
      <div class="row">
        <div class="col-xs-7">
          <?= LinkPager::widget(
            [
              'pagination' => $notificationsDataProvider->pagination,
            ]
          ); ?>
        </div>
      </div>
    </div>



    <?php Pjax::end(); ?>
  </div>
</div>
