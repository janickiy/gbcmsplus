<?php
use mcms\common\helpers\Html as McmsHtml;
use yii\bootstrap\Html;
use yii\helpers\Url;
use mcms\common\helpers\Html as HtmlLink;

/** @var $templates \mcms\promo\models\BannerTemplate[] */
/** @var $form \yii\widgets\ActiveForm */
/** @var $model \yii\db\ActiveRecord */
/** @var $attribute string */
/** @var $languages array */
$panelId = McmsHtml::getUniqueId();
?>

<div class="panel panel-info">
  <div class="panel-heading" role="tab" id="headingOne">
    <h4 class="panel-title">
      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#<?= $panelId ?>" aria-expanded="false" aria-controls="<?= $panelId ?>">
        <?= Yii::_t('banners.pick_banner') ?>
      </a>
    </h4>
  </div>
  <div id="<?= $panelId ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
    <div class="panel-body" style="max-height: 200px; overflow-y: scroll">
      <?php $this->registerJs(/** @lang JavaScript */ '(function () {
  var $panel = $("#' .$panelId. '");

  function updateInputs() {
    var $inputs = $panel.find("input");
    var $defaultInput = $inputs.filter(".default-variant");
    var $otherInputs = $inputs.filter(":not(.default-variant)");
    if ($defaultInput.prop("checked")) {
      $otherInputs.attr("disabled", "disabled");
    } else {
      $otherInputs.removeAttr("disabled");
    }
  }

  updateInputs();
  $panel.find(".default-variant").on("change", updateInputs);
})();
      '); ?>
      <div class="radio">
        <?= Html::checkbox(
          Html::getInputName($model, $attribute),
          empty($model->{$attribute}),
          ['label' => Yii::_t('app.common.default'), 'value' => '', 'class' => 'default-variant']
        ) ?>
      </div>
      <?php foreach($templates as $template):?>
        <p><?= $template->name ?></p>
        <?php foreach($template->activeBanners as $banner):?>
          <div class="radio">
            <?= Html::checkbox(
              Html::getInputName($model, $attribute) . '[]',
              (in_array($banner->id, $model->{$attribute})),
              ['label' => $banner->name, 'value' => $banner->id]
            ) ?>

            <div class="btn-group" role="group">
              <?php foreach($languages as $language):?>
                <?=
                HtmlLink::a(
                  Html::icon('eye-open') . ' ' . $language,
                  ['banners/view', 'id' => $banner->id, 'language' => $language],
                  [
                    'class' => 'btn btn-default btn-xs',
                    'target' => '_blank'
                  ]
                );
                ?>
              <?php endforeach; ?>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>
