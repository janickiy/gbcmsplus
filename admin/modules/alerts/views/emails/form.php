<?php

use admin\modules\alerts\models\Event;
use mcms\common\form\AjaxActiveForm;
use yii\bootstrap\Html;
use mcms\common\widget\modal\Modal;

/** @var \admin\modules\alerts\models\Event $model */
?>

<?php $form = AjaxActiveForm::begin([
    'ajaxSuccess' => Modal::ajaxSuccess('#emailsPjaxGrid'),
]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?= $this->title ?></h4>
</div>
<div class="modal-body">
    <?= $form->field($model, 'priority')->dropDownList(Event::getPriorities(), ['prompt' => Yii::_t('app.common.choose')]) ?>
    <?= $form->field($model, 'email') ?>
</div>
<div class="modal-footer">
    <div class="row">
        <div class="col-md-12">
            <?= Html::submitButton(
                '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Create'), ['class' => 'pull-right btn btn-success']
            ) ?>
        </div>
    </div>
</div>
<?php AjaxActiveForm::end(); ?>
