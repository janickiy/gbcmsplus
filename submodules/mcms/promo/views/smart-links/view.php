<?php

use mcms\promo\assets\ArbitrarySourcesViewAssets;

/**
 * @var mcms\promo\models\Source $model
 * @var yii\data\ArrayDataProvider $sourceOperatorLandings
 */

$this->title = $model->name;
?>

<?= $this->render('_view', ['model' => $model]);?>
