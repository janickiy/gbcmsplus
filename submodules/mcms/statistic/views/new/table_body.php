<?php

use mcms\statistic\components\newStat\DataProvider;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\PartialGrid;

/** @var DataProvider $dataProvider */
/** @var FormModel $formModel */
/** @var int|null $selectedTemplateId */
?>

<?= PartialGrid::widget([
  'dataProvider' => $dataProvider,
  'statisticModel' => $formModel,
  'templateId' => $selectedTemplateId
]);