<?php
/**
 * @var string $verifyToken
 */
?>

<table class="table table-striped table-bordered">
  <tr>
    <td><code>verify_token</code><sup>*</sup></td>
    <td><?= $verifyToken ?></td>
  </tr>
  <tr>
    <td><code>transaction_id</code><sup>*</sup></td>
    <td><?= Yii::_t('providers.default_pb_parameters_transaction_id') ?></td>
  </tr>
  <tr>
    <td><code>hit_id</code><sup>*</sup></td>
    <td><?= Yii::_t('providers.default_pb_parameters_hit_id') ?></td>
  </tr>
  <tr>
    <td><code>transaction_type</code><sup>*</sup></td>
    <td><?= Yii::_t('providers.default_pb_parameters_transaction_type') ?></td>
  </tr>
  <tr>
    <td><code>sum</code></td>
    <td><?= Yii::_t('providers.default_pb_parameters_sum') ?></td>
  </tr>
  <tr>
    <td><code>currency</code></td>
    <td><?= Yii::_t('providers.default_pb_parameters_currency') ?></td>
  </tr>
  <tr>
    <td><code>phone</code></td>
    <td><?= Yii::_t('providers.default_pb_parameters_phone') ?></td>
  </tr>
  <tr>
    <td><code>action_time</code><sup>*</sup></td>
    <td><?= Yii::_t('providers.default_pb_parameters_action_time') ?></td>
  </tr>

</table>
<small style="color: #d45b7a;margin: 0 18px 18px;display: inline-block;font-weight: 100;">
  * <?= Yii::_t('provider_settings.required_fields_message') ?>
</small>
