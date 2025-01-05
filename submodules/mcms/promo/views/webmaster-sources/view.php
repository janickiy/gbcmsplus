<?php

use mcms\common\helpers\Link;
use yii\bootstrap\Html;

$this->title = $model->name;

?>

<?= $this->render('_view', ['model' => $model, 'sourceOperatorLandings' => $sourceOperatorLandings]);?>

