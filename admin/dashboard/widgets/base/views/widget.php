<?php
/** @var string $id */

use mcms\common\helpers\Html;

/** @var string $titleColor */
/** @var string $icon */
/** @var string $title */
/** @var string $content */
/** @var string $toolbarContent */
/** @var bool $padding */
/** @var array $options */
?>

<div class="statbox <?= $blockClass ?>">
    <div class="statbox__header">
        <div class="statbox__header_title"><?= $title ?></div>
        <?php if ($toolbarContent): ?>
            <?= $toolbarContent ?>
        <?php endif; ?>
    </div>
    <div class="statbox__body <?= $padding ? ' statbox__body-padding' : '' ?>">
        <?= $content ?>
    </div>
</div>