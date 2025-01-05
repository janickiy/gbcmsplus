<?php
/**
 * @package   yii2-export
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2016
 * @version   1.2.4
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * @var array $options
 * @var array $batchToggle
 * @var array $columnSelector
 * @var array $hiddenColumns
 * @var array $selectedColumns
 * @var array $disabledColumns
 * @var array $noExportColumns
 * @var array $menuOptions
 * @var array $attributes
 */

$label = ArrayHelper::remove($options, 'label');
$icon = ArrayHelper::remove($options, 'icon');
$showToggle = ArrayHelper::remove($batchToggle, 'show', true);
if (!empty($icon)) {
    $label = $icon . ' ' . $label;
}
echo Html::beginTag('div', ['class' => 'btn-group', 'role' => 'group']);
echo Html::button($label . ' <span class="caret"></span>', $options);
foreach ($columnSelector as $value => $label) {
    if (in_array($value, $hiddenColumns)) {
        $checked = in_array($value, $selectedColumns);
        echo Html::checkbox('export_columns_selector[]', $checked, ['data-key' => $value, 'style' => 'display:none']);
        unset($columnSelector[$value]);
    }
    if (in_array($value, $noExportColumns)) {
        unset($columnSelector[$value]);
    }
}
echo Html::beginTag('ul', ArrayHelper::merge($menuOptions, ['style' => 'margin-left:-700px;']));
echo Html::beginTag('ul', ['class' => 'list-inline']);
?>

<?php if ($showToggle): ?>
    <?php
    $toggleOptions = ArrayHelper::remove($batchToggle, 'options', []);
    $toggleLabel = ArrayHelper::remove($batchToggle, 'label', Yii::_t('statistic.statistic.select_all'));
    Html::addCssClass($toggleOptions, 'kv-toggle-all');
    ?>
    <li>
        <div class="checkbox" style="padding: 0 8px">
            <label>
                <?= Html::checkbox('export_columns_toggle', true, ['class' => 'checkbox']) ?>
                <?= Html::tag('span', $toggleLabel, $toggleOptions) ?>
            </label>
        </div>
    </li>
    <li class="divider"></li>
<?php endif; ?>

<?php
echo Html::beginTag('div', ['class' => 'export-attributes']);
foreach ($columnSelector as $value => $label) {
    $checked = in_array($value, $selectedColumns);
    $disabled = in_array($value, $disabledColumns);
    $labelTag = $disabled ? '<label class="disabled">' : '<label>';
    echo '<li><div class="checkbox" style="padding: 0 8px; margin: 0 8px">' . $labelTag .
        Html::checkbox('export_columns_selector[]', $checked, ['class' => 'checkbox', 'data-key' => $value, 'data-attribute' => ArrayHelper::getValue($attributes, $value, ''), 'disabled' => $disabled]) .
        "\n<span class='kv-toggle-all'>" . $label . '</span></label></div></li>';
}
echo Html::endTag('div');
echo Html::endTag('ul');
echo Html::endTag('ul');
echo Html::endTag('div');
?>
