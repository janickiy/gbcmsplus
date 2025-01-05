<?php

use kartik\builder\Form;
use kartik\form\ActiveForm;
use mcms\common\grid\ContentViewPanel;
use mcms\common\module\settings\SettingsAbstract;
use mcms\common\helpers\Html as CustomHtml;
use yii\helpers\Html;

// Открывает там и панель с ошибкой
$js = <<< JS
  $('#modules-grouped-settings-form').on('afterValidate', function() {
    var error = $('div.has-error').first();
    if (error.length === 0) return;
    
    var tabName = error.closest('.tab-pane').attr('id');
    $('.nav-tabs a[href="#' + tabName + '"]').tab('show');
    var panel = error.closest('.panel-collapse');
    if (panel.length > 0) {
      panel.addClass('in');
      panel.siblings('.panel-heading').find('a[data-toggle="collapse"]').removeClass('collapsed');
    }
  });
JS;
$this->registerJs($js);
?>

    <p>
        <?= CustomHtml::a(
            Yii::_t('app.common.old_module_settings'),
            ['/modmanager/modules/index/'],
            [],
            [Yii::$app->getModule('changelog')->modulePermission]
        ) ?>
    </p>

<?php ContentViewPanel::begin([
    'class' => 'well',
]) ?>
<?php $form = ActiveForm::begin([
    'options' => [
        'id' => 'modules-grouped-settings-form',
        'enctype' => 'multipart/form-data',
    ]
]) ?>

    <div class="tab-pane">
        <div class="tabs-left">
            <ul class="nav nav-tabs">
                <?php foreach (array_keys($groupedFormSettings) as $key => $group): ?>
                    <li class="<?= ($key === 0) ? 'active' : '' ?>">
                        <a href="#group_settings_tab_<?= $key ?>" data-toggle="tab"><?= Yii::_t($group) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content padding-10">
                <?php foreach (array_keys($groupedFormSettings) as $key => $group): ?>
                    <div class="tab-pane<?= ($key === 0) ? ' active' : '' ?>" id="group_settings_tab_<?= $key ?>">
                        <?php
                        if (isset($groupedFormSettings[$group][SettingsAbstract::NO_FORM_GROUP])) {
                            foreach ($groupedFormSettings[$group][SettingsAbstract::NO_FORM_GROUP] as $attribute => $setting) {
                                echo Form::widget([
                                    'model' => $moduleDynamicModels[$setting['module_id']],
                                    'form' => $form,
                                    'attributes' => [$attribute => $setting],
                                ]);
                            }
                        }
                        ?>
                        <?php if ($groupedFormSettings[$group] > 1): ?>
                            <div class="panel-group smart-accordion-default" id="accordion<?= $key ?>">
                                <?php foreach ($groupedFormSettings[$group] as $formGroup => $settings): ?>
                                    <?php if ($formGroup != SettingsAbstract::NO_FORM_GROUP): ?>
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a role="button" data-toggle="collapse" data-parent="#accordion"
                                                       href="#collapse-<?= str_replace('.', '_', $formGroup) ?>"
                                                       class="collapsed">
                                                        <i class="fa fa-lg fa-angle-down pull-right"></i>
                                                        <i class="fa fa-lg fa-angle-up pull-right"></i>
                                                        <?= Yii::_t($formGroup) ?>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="collapse-<?= str_replace('.', '_', $formGroup) ?>"
                                                 class="panel-collapse collapse">
                                                <div class="panel-body">
                                                    <?php
                                                    foreach ($settings as $attribute => $setting) {
                                                        echo Form::widget([
                                                            'model' => $moduleDynamicModels[$setting['module_id']],
                                                            'form' => $form,
                                                            'attributes' => [$attribute => $setting],
                                                        ]);
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <hr/>
    <div class="row">
        <div class="col-md-12">
            <?= Html::submitButton(Yii::_t('app.common.Save'), ['class' => 'btn btn-success pull-right']) ?>
            <?= Html::resetButton(Yii::_t('app.common.Reset'), ['class' => 'btn btn-danger']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
<?php ContentViewPanel::end() ?>