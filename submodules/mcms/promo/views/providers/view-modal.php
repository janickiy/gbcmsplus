<?php

use mcms\promo\models\AbstractProviderSettings;
use mcms\promo\models\Provider;

/** @var Provider $model Провайдер */
/** @var AbstractProviderSettings|null $settings Настройки провайдера */
/** @var bool $canViewAllFields */
/** @var bool $canViewSecretKey */
?>

<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal">&times;</button>
  <h4 class="modal-title"><?= $this->title ?></h4>
</div>
<div class="modal-body">
  <?= $this->render('_view', [
    'model' => $model,
    'settings' => $settings,
    'canViewAllFields' => $canViewAllFields,
  ]);?>
</div>
