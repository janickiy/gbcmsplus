<?php

use mcms\common\form\AjaxActiveForm;
use mcms\user\Module;
use rgk\export\ExportMenu;
use mcms\common\rbac\AuthItemsManager;
use mcms\common\widget\AdminGridView;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\common\widget\UserSelect2;
use mcms\user\models\User;
use yii\bootstrap\Html;
use mcms\common\helpers\Html as CustomHtml;
use kartik\date\DatePicker;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use kartik\editable\EditableAsset;
use kartik\editable\EditablePjaxAsset;
use kartik\popover\PopoverXAsset;
use yii\helpers\ArrayHelper;

EditableAsset::register($this);
EditablePjaxAsset::register($this);
PopoverXAsset::register($this);

/**
 * @var \mcms\common\web\View $this
 * @var array $statuses
 * @var bool $canViewUserPage
 */
/** @var \mcms\user\models\User $model */
/** @var string $exportWidgetId */

/** @var Module $usersModule */
$usersModule = Yii::$app->getModule('users');

$this->blocks['actions'] =
  Modal::widget([
    'toggleButtonOptions' => [
      'tag' => 'a',
      'id' => 'show-shortcut',
      'class' => 'btn btn-success',
      'label' => Html::icon('plus') . ' ' . Yii::_t('main.create_user')
    ],
    'url' => ['users/create'],
  ]);
?>

<?php
$gridColumns = [
  [
    'class' => 'yii\grid\CheckboxColumn',
    'headerOptions' => ['style' => 'padding-right: 0; padding-left: 0 !important'],
    'visible' => Yii::$app->user->can('UsersUsersMassActivate') || Yii::$app->user->can('UsersUsersMassDeactivate'),
  ],
  [
    'attribute' => 'id',
    'width' => '60px'
  ],
  'expand-menu' =>[
    'class' => 'kartik\grid\ExpandRowColumn',
    'header' => false,
    'visible' => function($model){ return Yii::$app->user->identity->canViewUser($model->id, true) && CustomHtml::hasUrlAccess('users/view/');},
    'allowBatchToggle' => false,
    'value' => function ($model, $key, $index, $column) {
      return AdminGridView::ROW_COLLAPSED;
    },
    'detailUrl' => Url::to(['view']),
    'detailOptions' => [
      'class' => 'kv-state-enable',
    ],
  ],
  [
    'attribute' => 'email',
    'width' => '200px',
    'options' => ['style' => 'width: 200px; max-width: 300px;'],
    'contentOptions' => ['style' => 'width: 200px; max-width: 300px; word-wrap: break-word;'],
    'content' => function(User $model) {

      $html = '<div class="user-list_email-container"><div>:email</div><div><strong class="super-small">:language</strong></div></div>';

      return strtr($html, [
        ':email' => Yii::$app->getFormatter()->asText($model->email),
        ':language' => Yii::$app->getFormatter()->asText(strtoupper($model->language))
      ]);
    }
  ],
  'manager_id' => [
    'attribute' => 'manager_id',
    'vAlign'=> 'middle',
    'width' => '200px',
    'format' => 'raw',
    'value' => 'managerLink',
    'enableSorting' => false,
    'visible' => $model->canViewManager(),
  ],
  [
    'attribute' => 'namesRoles',
    'label' => Yii::_t('forms.user_roles'),
    'filter' => $roles
  ],
  [
    'attribute' => 'status',
    'value' => function ($model) {
      return $model->getNamedStatus();
    },
    'filter' => $statuses
  ],
  
  [
    'attribute' => 'activeContact',
    'label' => Yii::_t('users.forms.user_contacts_title'),
    'format' => 'raw',
    'value' => function($model){
    $result = '';
    if(!empty($model->params->phone)){
        $result .= Html::tag('p',Html::a($model->params->phone,"tel:{$model->params->phone}"));
    }

    if(!empty($model->activeContacts)){
      foreach ($model->activeContacts as $contact) {
      $result .= $contact->type ? Html::tag('p',Html::a($contact->data,$contact->builtData, ['target' => '_blank'])) : Html::tag('p',$contact->data);
      }
    }
    return $result;
    }
  ],
  'created_at' => [
    'attribute' => 'created_at',
    'label' => Yii::_t('forms.user_created_at'),
    'format' => 'datetime',
    'contentOptions' => ['style' => 'width: 240px;']
  ],
  'online_at' => [
    'attribute' => 'online_at',
    'label' => Yii::_t('forms.user_online_at'),
    'format' => 'datetime',
    'value' => function ($model) {
      /** @var \mcms\user\models\User $model */
      return $model->online_at ?: null;
    },
    'contentOptions' => ['style' => 'width: 240px;']
  ],
  [
    'attribute' => 'online',
    'label' => Yii::_t('forms.user_online'),
    'value' => function ($model) {
      /** @var \mcms\user\models\User $model */
      return $model->isOnline()
        ? Yii::_t('controllers.online')
        : Yii::_t('controllers.offline');
    },
    'filter' => $online
  ],
  'actions' => [
    'class' => 'mcms\common\grid\ActionColumn',
    'template' => '{login-by-user} {update} {balance} {activate}',
    'buttonsPath' => [
      'balance' => '/payments/users/view/'
    ],
    'visibleButtons' => [
      'activate' => function ($model) {
        return $model->status == $model::STATUS_ACTIVATION_WAIT_HAND && Yii::$app->user->can((new AuthItemsManager)->getRolePermissionName($model->namesRoles));
      },
      'update' => function ($model) {
        return Yii::$app->user->can((new AuthItemsManager)->getRolePermissionName($model->namesRoles));
      }
    ],
    'buttons' => [
      'login-by-user' => function ($url, $model, $key) {
        $options = [
          'title' => Yii::_t('users.login.login_by_user'),
          'aria-label' => Yii::_t('users.login.login_by_user'),
          'data-pjax' => '0',
          'class' => 'btn btn-xs btn-default'
        ];

        return Html::a(Html::icon('log-in'), $url, $options);
      },
      'activate' => function ($url, $model, $key) {
        $options = array_merge([
          'title' => Yii::t('yii', 'On'),
          'aria-label' => Yii::t('yii', 'On'),
          'data-pjax' => '0',
          'class' => 'btn btn-xs btn-success',
          AjaxButtons::AJAX_ATTRIBUTE => 1
        ]);

        return Html::a(Html::icon('ok'), $url, $options);
      },
      'balance' => function ($url, $model, $key) {
        /** @var \mcms\payments\Module $paymentsModule */
        $paymentsModule = Yii::$app->getModule('payments');
        if (!$paymentsModule::canUserHaveBalance($model->id)) return;
        /** @var User $model */
        $options = array_merge([
          'title' => Yii::_t('users.main.balance'),
          'aria-label' => Yii::_t('users.main.balance'),
          'data-pjax' => '0',
          'class' => 'btn btn-xs btn-default'
        ]);

        return Html::a(Html::icon('briefcase'), $url, $options);
      }
    ],
    'contentOptions' => ['class' => 'col-min-width-150']
  ],
];
if ($usersModule->canExportUsers()) {
  $exportColumns = array_merge($gridColumns, ['skype']);
  unset($exportColumns['expand-menu']);
  unset($exportColumns['actions']);
  $toolbar = ExportMenu::widget([
    'id' => $exportWidgetId,
    'dataProvider' => $dataProvider,
    'isPartners' => true,
    'dropdownOptions' => ['class' => 'btn-xs btn-success', 'menuOptions' => ['class' => 'pull-right']],
    'columnSelectorOptions' => ['class' => 'btn-xs btn-success'],
    'columnSelectorMenuOptions' => ['class' => 'dropdown-menu pull-right js-status-update'],
    'columns' => $exportColumns,
    'target' => ExportMenu::TARGET_BLANK,
    'pjaxContainerId' => 'usersPjaxGrid',
    'filename' => Yii::_t('controllers.user_list'),
    'exportConfig' => [
      ExportMenu::FORMAT_HTML => false,
      ExportMenu::FORMAT_PDF => false,
      ExportMenu::FORMAT_EXCEL => false,
    ],
  ]);
} else {
  $toolbar = '';
}
$toolbar .= Html::tag('div',\mcms\common\widget\MassStatusWidget::widget([
    'url' => ['mass-activate'],
    'pjaxId' => '#usersPjaxGrid',
    'label' => \Yii::_t('commonMsg.main.mass-activate-label'),
    'confirm' => \Yii::_t('commonMsg.main.mass-activate-confirm'),
    'optionsClass' => 'btn btn-xs btn-success',
    'buttonClass' => 'mass-activate-button'
  ]),['class'=>'btn-group'])
.Html::tag('div',\mcms\common\widget\MassStatusWidget::widget([
    'url' => ['mass-deactivate'],
    'pjaxId' => '#usersPjaxGrid',
    'label' => \Yii::_t('commonMsg.main.mass-deactivate-label'),
    'confirm' => \Yii::_t('commonMsg.main.mass-deactivate-confirm'),
    'optionsClass' => 'btn btn-xs btn-danger',
    'buttonClass' => 'mass-deactivate-button'
  ]),['class'=>'btn-group']);
?>
<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' => '<div class="btn-group">' . $toolbar . '</div>',
]);
?>
<?php Pjax::begin(['id' => 'usersPjaxGrid']); ?>

  <div class="users-filters">
    <?php $form = ActiveForm::begin([
      'method' => 'GET',
//      'action' => ['/' . Yii::$app->controller->getRoute()],
      'options' => [
        'data-pjax' => true,
        'id' => 'statistic-filter-form',
        'class' => 'form-inline',
      ],
    ]); ?>
    <div class="dt-toolbar">
      <?=$form->field($model, 'walletString', ['template' => "{input}"])->textInput([
        'placeholder' => $model->getAttributeLabel('walletString'),
      ]) ?>
      <?=$form->field($model, 'contactString', ['template' => "{input}"])->textInput([
        'placeholder' => $model->getAttributeLabel('contactString'),
      ]) ?>
      <?= Html::submitButton(Yii::_t('users.filter.submit'), ['class' => 'btn btn-info']) ?>

    </div>

    <?php $form->end() ?>
  </div>

<?php $this->registerJs("$('#usersGrid').on('kvexprow.loaded', function (event, ind, key, extra) {
  initEditablePopover(key + '-status-editable-targ');
  initEditablePopover(key + '-comment-editable-targ');
});")?>
<?= AdminGridView::widget([
  'id' => 'usersGrid',
  'dataProvider' => $dataProvider,
  'filterModel' => $model,
  'export' => false,
  'rowOptions' => function ($model) {
    switch ($model->status) {
      case $model::STATUS_ACTIVE: return ['class' => ''];
      case $model::STATUS_ACTIVATION_WAIT_HAND:
      case $model::STATUS_ACTIVATION_WAIT_EMAIL:
        return ['class' => 'warning'];
      case $model::STATUS_DELETED:
      case $model::STATUS_BLOCKED:
      case $model::STATUS_INACTIVE:
        return ['class' => 'danger'];
      default:
        return ['class' => ''];
    }
  },
  'columns' => ArrayHelper::merge($gridColumns, [
    // TRICKY приходится строки с фильтрациями типа select2, datepicker мержить таким образом,
    // иначе не работает переинициализация этих виджетов после pjax обновления.
    // Можно решить если pjax начать до ExportMenu, но тогда при фулскрине грида фильтрация сворачивает фулскрин.
    // поэтому ничего лучше текущего варианта не придумали
    'manager_id' => [
      'filter' => UserSelect2::widget(
        [
          'model' => $model,
          'attribute' => 'manager_id',
          'initValueUserId' => $model->manager_id,
          'roles' => Yii::$app->getModule('users')->getManagerRoles(),
          'options' => [
            'id' => 'user-select2-id',
            'placeholder' => '',
          ],
        ]
      ),
    ],
    'created_at' => [
      'filter' => DatePicker::widget([
        'model' => $model,
        'attribute' => 'createdFrom',
        'attribute2' => 'createdTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar kv-dp-icon"></i>',
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom', 'autoclose' => true]
      ]),
    ],
    'online_at' => [
      'filter' => DatePicker::widget([
        'model' => $model,
        'attribute' => 'onlineFrom',
        'attribute2' => 'onlineTo',
        'type' => DatePicker::TYPE_RANGE,
        'separator' => '<i class="glyphicon glyphicon-calendar kv-dp-icon"></i>',
        'pluginOptions' => ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom', 'autoclose' => true]
      ]),
    ],
  ])
]); ?>
<?php Pjax::end(); ?>
<?php ContentViewPanel::end();