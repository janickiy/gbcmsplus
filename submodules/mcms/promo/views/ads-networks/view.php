<?php

use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\helpers\Link;
?>

<?php $this->beginBlock('actions'); ?>

<?php $this->endBlock() ?>

<?= $this->render('_view', ['model' => $model]);?>


