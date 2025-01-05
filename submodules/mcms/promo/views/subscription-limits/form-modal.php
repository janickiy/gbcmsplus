<?php

use mcms\api\models\Country;
use mcms\common\form\AjaxActiveForm;
use mcms\common\widget\Select2;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\widgets\OperatorsDropdown;
use mcms\promo\models\SubscriptionsLimit;
use rgk\utils\widgets\modal\Modal;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/** @var SubscriptionsLimit $model */
/** @var View $this */

$this->title = $model->isNewRecord ? SubscriptionsLimit::t('create_title') : SubscriptionsLimit::t('update_title') . " #{$model->id}";
/** @var \mcms\user\Module $userModule */
$userModule = Yii::$app->getModule('users');
?>

<?php $form = AjaxActiveForm::begin([
  'ajaxSuccess' => Modal::ajaxSuccess('#subscription-limits-grid'),
]) ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?= $this->title ?></h4>
    </div>

    <div class="modal-body">
      <?= $form->field($model, 'country_id')->widget(Select2::class, [
        'data' => Country::getDropdownItems(),
        'options' => [
          'prompt' => SubscriptionsLimit::t('enter_country_name'),
        ],
        'pluginEvents' => [
          'change' => 'function() {
            $("#' . Html::getInputId($model, 'operator_id') . '").val("").change();
            
            $("#sublimits_operator_id").show(); 
           }'
        ],
      ]) ?>

      <?= $form->field($model, 'operator_id', ['options' => [
        'id' => 'sublimits_operator_id',
        'style' => $model->country_id ? null : 'display:none'
      ]])
        ->widget(OperatorsDropdown::class, [
          'options' => [
            'prompt' => SubscriptionsLimit::t('enter_operator_name'),
          ],
          'pluginOptions' => [
            'allowClear' => true,
            'ajax' => [
              'url' => Url::to(['operators/select2']),
              'dataType' => 'json',
              'data' => new JsExpression('function(params) {
                var countryId = $("#' . Html::getInputId($model, 'country_id') . '").val();
                return {
                  countryRequired: 0,
                  q: params.term ? params.term : "",
                  country_id: countryId
                };
          }')
            ]
          ],
          'useSelect2' => true,
        ]) ?>

      <?= $form->field($model, 'user_id')->widget(UserSelect2::class, [
        'initValueUserId' => $model->user_id,
        'roles' => [$userModule::PARTNER_ROLE],
        'options' => ['placeholder' => SubscriptionsLimit::t('enter_partner_name')]
      ]) ?>

      <?= $form->field($model, 'subscriptions_limit') ?>
    </div>

    <div class="modal-footer">
        <div class="row">
            <div class="col-md-12">
              <?= Html::submitButton(
                '<i class="fa fa-save"></i> ' . ($model->isNewRecord ? Yii::_t('app.common.Create') : Yii::_t('app.common.Save')),
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
              ) ?>
            </div>
        </div>
    </div>
<?php AjaxActiveForm::end(); ?>