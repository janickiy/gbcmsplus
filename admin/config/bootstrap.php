<?php

use mcms\common\output\FakeOutput;
use mcms\common\output\OutputInterface;
use yii\base\Event;
use yii\web\Application;
use yii\widgets\Pjax;

Yii::$container->set('mcms\user\components\storage\UserInterface', [
    'class' => 'mcms\user\components\storage\User'
]);

// Пришлось добавлять меню через события, иначе не работают лейблы для пунктов меню
Event::on(Application::class, Application::EVENT_BEFORE_ACTION, function () {
    Yii::$app->params['menu'] = require(__DIR__ . '/menu.php');
    Yii::$app->params['tabs'] = require(__DIR__ . '/tabs.php');
});

Yii::$container->set(\rgk\theme\smartadmin\widgets\menu\MenuItem::class, [
    'class' => \rgk\theme\smartadmin\widgets\menu\MenuItem::class,
    'template' => '<i class="{iconCls}"></i> <span class="menu-item-parent smartadmin-my-menu-item">{label}</span> <span class="smartadmin-my-badge">{badge}</span>'
]);

Yii::$container->set(
    \rgk\utils\widgets\alert\Alert::class,
    \rgk\theme\smartadmin\widgets\alert\Alert::class
);

// Html::tag('span', $totalCount, ['class' => 'badge pull-right inbox-badge' . ($is_parent ? ' margin-right-13' : '')])

// пока фейковый, чтобы заглушить консольный. Если нужен вывод в браузере, надо писать новую реализацию интерфейса
Yii::$container->set(OutputInterface::class, FakeOutput::class);

Yii::$container->set(Pjax::class, [
    'class' => Pjax::class,
    'timeout' => false
]);
