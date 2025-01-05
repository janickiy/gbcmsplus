<?php
use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\widget\AjaxButtons;
use mcms\common\widget\modal\Modal;
use mcms\pages\models\PartnerCabinetStyle;
use mcms\pages\models\PartnerCabinetStyleCategory;
use mcms\pages\models\PartnerCabinetStyleField;
use mcms\common\grid\ContentViewPanel;
use mcms\common\helpers\Html;
use yii\bootstrap\Html as BHtml;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \yii\web\View $this */
/** @var PartnerCabinetStyle|null $styleModel */
/** @var PartnerCabinetStyle[] $styles */
/** @var PartnerCabinetStyleCategory[] $categories */

AjaxButtons::widget();
?>
<?php Pjax::begin(['id' => 'styles-pjax']) ?>
  <div class="row">
    <!-- Управление оформлениями -->
    <div class="col-sm-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title pull-left"><?= Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'styles') ?></h3>
          <div class="clearfix"></div>
        </div>
        <div class="list-group">
          <?php /** @var PartnerCabinetStyle $style */ ?>
          <?php foreach ($styles as $style) { ?>
            <?php
            $name = $style->name;
            $isActive = $style->status == $style::STATUS_ACTIVE;
            $isPreview = PartnerCabinetStyle::getPreview() == $style->id;
            if ($isActive || $isPreview) {
              $badge = null;
              if ($isActive) $badge = Html::tag('span', $style->getStatusName($style->status), ['class' => 'badge pull-right']);
              else if ($isPreview) {
                $badge .= Html::tag(
                  'span',
                  Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'preview'),
                  ['class' => 'badge pull-right']
                );
              }
              $fullName = Html::tag('div', $name, ['class' => 'col-sm-6']) . Html::tag('div', $badge, ['class' => 'col-sm-6']);
              $fullName = Html::tag('div', $fullName, ['class' => 'row']);
            } else $fullName = $name;
            ?>
            <?= Html::a(
              $fullName,
              ['', 'styleId' => $style->id],
              ['class' => 'list-group-item' . ($styleModel && $style->id == $styleModel->id ? ' active' : null)]) ?>
          <?php } ?>

          <div class="list-group-item">
            <?php $styleCreateForm = AjaxActiveKartikForm::begin([
              'action' => ['create-style'],
              'ajaxSuccess' => <<<JS
                function (event, jqXHR, ajaxOptions, data) {
                    $.pjax.reload({container: "#styles-pjax", url: event.data.styleUrl, timeout: 5000});
                }
JS
            ]) ?>
            <?php if (Html::hasUrlAccess(['partner-cabinet-styles/create-style/'])) { ?>
            <?php $styleCreateModel = new PartnerCabinetStyle; ?>
            <?= $styleCreateForm->field(
              $styleCreateModel,
              'name',
              [
                'inputOptions' => ['placeholder' => $styleCreateModel->getAttributeLabel('name')],
                'template' =>
                  Html::tag('div',
                    "{input}\n"
                    . Html::tag('span', Html::submitButton(BHtml::icon('plus'), ['class' => 'btn btn-success']), ['class' => 'input-group-btn']),
                    ['class' => 'input-group']
                  )
                  . "\n{hint}\n{error}",
              ]
            ) ?>
            <?php } ?>
            <?php AjaxActiveKartikForm::end() ?>
          </div>
        </div>
      </div>
    </div>
    <!-- /Управление оформлениями -->

    <?php if (Yii::$app->user->can('PagesCanUpdatePartnerCabinetStyle')) { ?>
      <div class="col-sm-9">
        <?php Pjax::begin(['id' => 'style-categories-pjax']) ?>

        <?php if ($styleModel) { ?>
          <?php ContentViewPanel::begin([
            'header' => Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'style_title', ['name' => $styleModel->name]),
            'buttons' => [],
            'toolbar' =>
              Html::beginTag('div', ['class' => 'btn-group']) .
              Modal::widget([
                'toggleButtonOptions' => [
                  'tag' => 'a',
                  'label' => BHtml::icon('plus') . ' ' . Yii::_t('pages.partner_cabinet_style_categories.create-category'),
                  'title' => Yii::t('yii', 'Update'),
                  'class' => 'btn btn-xs btn-success',
                  'data-pjax' => 0,
                ],
                'url' => ['create-category-modal'],
              ]) .
              ($categories ? Modal::widget([
                'url' => ['create-field-modal'],
                'title' => BHtml::icon('plus') . ' ' . PartnerCabinetStyleField::translate('create'),
                'size' => Modal::SIZE_LG,
                'toggleButtonOptions' => [
                  'class' => 'btn btn-xs btn-success',
                  'data-pjax' => 0,
                ]
              ]) : '') .
              Html::endTag('div')
          ]) ?>
          <?php $styleForm = AjaxActiveKartikForm::begin(['ajaxSuccess' => Modal::ajaxSuccess('#styles-pjax')]) ?>
          <div class="row">
            <!-- Параметры оформления -->
            <div class="col-sm-3">
              <?= $styleCreateForm->field($styleModel, 'name') ?>
              <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-primary btn-block']) ?>
              <?php if ($styleModel->status == $styleModel::STATUS_INACTIVE) { ?>
                <?= Html::a(
                  PartnerCabinetStyle::getPreview() == $styleModel->id
                    ? Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'disable_preview')
                    : Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'enable_preview'),
                  ['toggle-style-preview', 'id' => $styleModel->id],
                  [
                    'class' => 'btn btn-default btn-block',
                    AjaxButtons::AJAX_ATTRIBUTE => 1,
                    AjaxButtons::SUCCESS_ATTRIBUTE => /** @lang JavaScript */
                      'function(data){
                      $.pjax.reload({container: "#styles-pjax", timeout: 5000, push: false});
                  }',
                    AjaxButtons::RELOAD_ATTRIBUTE => 0,
                    'data-pjax' => 0,
                  ]
                ) ?>
              <?php } ?>
              <?= Html::a(
                $styleModel->status == $styleModel::STATUS_INACTIVE
                  ? Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'activate')
                  : Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'deactivate'),
                ['toggle-style-activity', 'id' => $styleModel->id],
                [
                  AjaxButtons::CONFIRM_ATTRIBUTE => Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'style_toggle_activity_confirm', ['name' => $styleModel->name]),
                  'class' => 'btn btn-default btn-block',
                  AjaxButtons::AJAX_ATTRIBUTE => 1,
                  AjaxButtons::SUCCESS_ATTRIBUTE => /** @lang JavaScript */
                    'function(data){
                      $.pjax.reload({container: "#styles-pjax", timeout: 5000, push: false});
                  }',
                  AjaxButtons::RELOAD_ATTRIBUTE => 0,
                  'data-pjax' => 0,
                ]
              ) ?>
              <?= Html::a(
                Yii::_t('app.common.Delete'),
                ['delete-style', 'id' => $styleModel->id],
                [
                  'class' => 'btn btn-default btn-block',
                  AjaxButtons::CONFIRM_ATTRIBUTE => Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'style_delete_confirm', ['name' => $styleModel->name]),
                  AjaxButtons::AJAX_ATTRIBUTE => 1,
                  AjaxButtons::SUCCESS_ATTRIBUTE => /** @lang JavaScript */
                    'function(data){
                      $.pjax.reload({container: "#styles-pjax", url: data.data.url, timeout: 5000});
                  }',
                  AjaxButtons::RELOAD_ATTRIBUTE => 0,
                  'data-pjax' => 0,
                ]
              ) ?>
            </div>
            <!-- /Параметры оформления -->

            <!-- Поля оформления -->
            <div class="col-sm-9" style="border-left: 3px solid #e3e3e3;">
              <?= $this->render('_categories', [
                'categories' => $categories,
                'form' => $styleForm,
                'styleId' => $styleModel->id,
              ]); ?>
            </div>
            <!-- /Поля оформления -->
          </div>
          <?php AjaxActiveKartikForm::end() ?>
          <?php ContentViewPanel::end() ?>
        <?php } else { ?>
          <div class="alert alert-warning"
               role="alert"><?= Yii::_t(PartnerCabinetStyle::LANG_PREFIX . 'create_style') ?></div>
        <?php } ?>
        <?php Pjax::end() ?>
      </div>
    <?php } ?>
  </div>
<?php Pjax::end() ?>