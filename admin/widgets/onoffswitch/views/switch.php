<?php

use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/** @var string $label */
/** @var string $onLabel */
/** @var string $offLabel */
/** @var array $options */
/** @var string $attribute */
/** @var Model $model */
?>

<div class="onoffswitch-container">
  <span class="onoffswitch-title"><?= $label ?: $model->getAttributeLabel($attribute) ?></span>
  <span class="onoffswitch">
    <?= $model
      ? Html::activeCheckbox($model, $attribute, ArrayHelper::merge(['class' => 'onoffswitch-checkbox', 'label' => null], $options))
      : Html::checkbox($options['name'], false, ArrayHelper::merge(['class' => 'onoffswitch-checkbox'], $options))
    ?>
    <label class="onoffswitch-label" for="<?= $options['id'] ?>">
      <span class="onoffswitch-inner" data-swchon-text="<?= $onLabel ?>" data-swchoff-text="<?= $offLabel ?>"></span>
      <span class="onoffswitch-switch"></span>
    </label>
  </span>
</div>