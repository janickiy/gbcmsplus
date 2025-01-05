<?php

foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
  $success = $key === 'success';

  $this->registerJs('notifyInit(null, "' . $message . '", ' . ($success ? 'true' : 'false') . ' );', $this::POS_READY);
}