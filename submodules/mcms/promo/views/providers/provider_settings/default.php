<?= $form->field($model, 'transaction_type_alias') ?>
<?= $form->field($model, 'custom_postback_status_on') ?>
<?= $form->field($model, 'custom_postback_status_off') ?>
<?= $form->field($model, 'custom_postback_status_onetime') ?>
<?= $form->field($model, 'custom_postback_status_rebill') ?>
<?= $form->field($model, 'custom_postback_status_complaint') ?>
<?= $form->field($model, 'custom_postback_status_refund') ?>
<?= $form->field($model, 'parse_raw_data')->checkbox() ?>