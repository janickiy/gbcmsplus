<div class="form-group">
  <?php \yii\bootstrap\Modal::begin([
    'header' => Yii::_t('notifications.replacements_modal_header'),
    'toggleButton' => [
      'label' => Yii::_t('notifications.replacements_show_button'),
      'class' => 'btn btn-default btn-replacement',
      'data-width' => '800px',
    ]
  ]) ?>

  <?= $this->render('_replacements', ['replacementsDataProvider' => $replacementsDataProvider]) ?>

  <?php \yii\bootstrap\Modal::end(); ?>
</div>