<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use mcms\common\SystemLanguage;
use yii\helpers\Url;

$currentLang = (new SystemLanguage())->getCurrent();


?>
<?php foreach ($data as $page): ?>
  <?php if ($page->code == $currentLang) {
    continue;
  } ?>
  <a href="<?= Url::to(['users/site/lang', 'language' => $page->code]); ?>" class="lang">
    <span><?= $page->name ?></span>
  </a>
<?php endforeach ?>