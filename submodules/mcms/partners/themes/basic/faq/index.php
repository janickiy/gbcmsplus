<?php
use mcms\partners\assets\FaqAsset;

/**
 * @var \mcms\pages\models\FaqCategory[] $faqList
 */
FaqAsset::register($this)
?>

<div class="container">
  <div class="bgg">
    <div class="title">
      <h2><?= Yii::_t('partners.main.content'); ?></h2>
    </div>
    <div class="content__position">
      <ul class="faq_list faq_content">
        <?php foreach($faqList as $category):?>
          <li>
            <a href="#scroll_<?= $category->id; ?>"><?= Yii::$app->getFormatter()->asText($category->name); ?></a>
            <ul>
              <?php foreach($category->faqs as $faq): ?>
                <li>
                  <a href="#scroll_<?= $category->id . '_' . $faq->id; ?>">
                    <?= Yii::$app->getFormatter()->asText($faq->question); ?>
                  </a>
                </li>
              <?php endforeach;?>
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="title">
      <h2><?= Yii::_t('partners.main.help'); ?></h2>
    </div>
    <div class="content__position">
      <ul class="faq_list faq_text">
        <?php foreach($faqList as $category):?>
          <li>
            <h3 id="scroll_<?= $category->id; ?>"><?= Yii::$app->getFormatter()->asText($category->name); ?>:</h3>
            <ul>
              <?php foreach($category->faqs as $faq): ?>
                <li>
                  <span id="scroll_<?= $category->id . '_' . $faq->id; ?>"><?= Yii::$app->getFormatter()->asText($faq->question); ?></span>
                  <p><?= $faq->answer; ?></p>
                </li>
              <?php endforeach;?>
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
