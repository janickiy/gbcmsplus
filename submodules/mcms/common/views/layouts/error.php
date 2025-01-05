<?php

/* @var $this \yii\web\View */
/* @var $content string */

use mcms\common\assets\ErrorAsset;
use yii\helpers\Html;

ErrorAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <?= Html::csrfMetaTags() ?>
  <title><?= Html::encode($this->title) ?></title>
  <?php $this->head() ?>
</head>
<body class="<?= Yii::$app->user->getIdentity()->color ? Yii::$app->user->getIdentity()->color : 'cerulean'?>">
<?php $this->beginBody() ?>

  <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
