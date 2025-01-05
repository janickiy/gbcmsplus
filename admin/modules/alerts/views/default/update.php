<?php

use mcms\common\form\AjaxActiveKartikForm;
use mcms\common\widget\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var \admin\modules\alerts\models\Event $model */
/** @var yii\data\ActiveDataProvider $filtersDataProvider */
?>

<?php $form = AjaxActiveKartikForm::begin(); ?>


<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title pull-left"><?= Yii::_t('alerts.main.rule') ?></h3>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <?= $this->render('_form', [
                    'form' => $form,
                    'model' => $model
                ]) ?>
                <?= $form->field($model, 'is_active')->checkbox() ?>
                <?= Html::submitButton(
                    '<i class="fa fa-save"></i> ' . Yii::_t('app.common.Save'), ['class' => 'pull-right btn btn-success']
                ) ?>
            </div>
        </div>
    </div>
    <?php Pjax::begin(['enablePushState' => false, 'id' => 'filtersPjax']); ?>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="clearfix">
                    <h3 class="panel-title pull-left"><?= Yii::_t('alerts.main.filters') ?></h3>
                    <?= Modal::widget([
                        'toggleButtonOptions' => [
                            'tag' => 'a',
                            'label' => Html::icon('plus') . ' ' . Yii::_t('alerts.event_filter.filter-add'),
                            'class' => 'btn-xs btn-success pull-right',
                            'data-pjax' => 0,
                        ],
                        'url' => Url::to(['filter/create', 'eventId' => $model->id]),
                    ]) ?>
                </div>
            </div>
            <?= $this->render('_filters', [
                'form' => $form,
                'model' => $model,
                'filtersDataProvider' => $filtersDataProvider,
            ]) ?>
        </div>
    </div>
    <?php Pjax::end(); ?>
</div>
<?php AjaxActiveKartikForm::end(); ?>
