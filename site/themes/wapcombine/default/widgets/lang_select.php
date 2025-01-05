<?php
/**
 * @var \mcms\pages\models\Page[] $data
 * @var \mcms\pages\models\Category $category
 * @var \mcms\pages\Module $pagesModule
 */

use mcms\common\SystemLanguage;
use yii\helpers\Url;

$currentLang = (new SystemLanguage())->getCurrent();

$js = <<<JS
  $('#language').change(function(e){
    var url = $('#' + this.id + ' option:selected').attr('data-url');
    e.preventDefault();
    if(url) {
      window.location.href = url;
    }
  });
JS;
$this->registerJs($js);

?>

<select name="" id="language">
  <?php foreach ($data as $page): ?>
    <option
      class="<?= ($css = $page->getPropByCode('css_class')) ? $css->multilang_value : '' ?>"
      value="<?= $page->code ?>" <?= ($page->code == $currentLang) ? 'selected' : '' ?>
      data-url="<?= Url::to(['users/site/lang', 'language' => $page->code]); ?>"
    >
      <?= $page->name ?>
    </option>
  <?php endforeach ?>
</select>
