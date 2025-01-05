<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>
<?php foreach ($data as $page): ?>
  <div class="maps-country maps-country--<?= $page->code ?>">
    <div class="maps-country-bg"></div>
    <div class="maps-country-message">
      <h5><?= $page->name ?></h5>
      <h4><?= ArrayHelper::getValue($page->getPropByCode('roi'), 'multilang_value', 'ROI 50%') ?></h4>
      <p><?= ArrayHelper::getValue($page->getPropByCode('write_off'), 'multilang_value', '') ?></p>
    </div>
  </div>
<?php endforeach ?>