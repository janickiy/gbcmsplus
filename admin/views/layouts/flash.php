<?php
/**
 * @var \mcms\common\web\View $this
 */
foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
    \mcms\common\widget\alert\Alert::widget([
        'type' => $key,
        'title' => $message,
    ]);
}