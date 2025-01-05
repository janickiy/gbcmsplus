<?php
use mcms\common\helpers\Html;
?>
<div class="form-group">
  <?php $replacementsId = Html::getUniqueId() ?>
  <?= \yii\helpers\Html::a(
    Yii::_t('notifications.replacements_show_button') .' <span class="caret"></span>',
    '#' . $replacementsId,
    ['data-toggle' => 'collapse', 'class' => 'dashed'])?>

  <?= $this->render('_replacements', [
    'options' => ['id' => $replacementsId, 'class' => 'collapse'],
    'replacementsDataProvider' => $replacementsDataProvider,
  ]) ?>
</div>