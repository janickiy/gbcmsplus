<?php
/** @var \yii\bootstrap\ActiveForm $form */
/** @var \mcms\promo\models\ProviderSettingsKp $model */
/** @var \mcms\common\web\View $this */


use kartik\widgets\DepDrop;
use yii\bootstrap\Html;
use yii\helpers\Url; ?>
<?= $form->field($model, 'instanceId')->dropDownList($model->getInstancesDropdown(), [
  'prompt' => ''
]) ?>

<?= $form->field($model, 'providerId')->widget(DepDrop::class, [
  'type' => DepDrop::TYPE_DEFAULT,
  'data' => $model->getProvidersDropdown(),
  'pluginOptions' => [
    'depends' => ['providersettingskp-instanceid'],
    'placeholder' => Yii::_t('alerts.event_filter.value-choose'),
    'url' => Url::to(['get-providers']),
  ],
]) ?>
<?= $form->field($model, 'streamId', [
  'template'=>"{label}\n<div class=\"input-group\">{input}\n<span class=\"input-group-btn\"><button id=\"create-stream\" class=\"btn btn-default\" type=\"button\" disabled='disabled'><span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span> ". $model::t('kp_create_stream') . "</button></span></div>\n{hint}\n{error}"
])->widget(DepDrop::class, [
  'type' => DepDrop::TYPE_DEFAULT,
  'data' => $model->getStreamsDropdown(),
  'pluginOptions' => [
    'depends' => ['providersettingskp-instanceid'],
    'placeholder' => Yii::_t('alerts.event_filter.value-choose'),
    'url' => Url::to(['get-streams']),
    'params' => ['createdStreamId']
  ],
]) ?>

<?= $form->field($model, 'api_url') ?>
<?= $form->field($model, 'hash') ?>
<?= $form->field($model, 'email') ?>
<?= $form->field($model, 'language') ?>
<?= Html::hiddenInput('createdStreamId', 0)?>
<?= Html::hiddenInput('selectedProviderId', 0)?>

<?php
$createUrl = Url::to(['create-stream']);
$collectUrl = Url::to(['collect-kp-form-data']);
$js = <<<JS
  $(function() {
    
    function depdropErrorHandler() {
      console.log(arguments);
      $('#provider-form').yiiActiveForm('updateAttribute', $(this).attr('id'), ['Api error. Can not get elements']);
    }
    
    $('#providersettingskp-providerid').on('change', function() {
      var selectedProviderId = $(this).val();
      if (selectedProviderId) {
        $('[name="selectedProviderId"]').val(selectedProviderId);
      }
    }).on('depdrop.afterChange', function() {
      var selectedProviderId = parseInt($('[name="selectedProviderId"]').val());
      if (selectedProviderId) {
        $(this).val(selectedProviderId);
      }
    }).on('depdrop.error', depdropErrorHandler);
    
    $('#providersettingskp-providerid,#providersettingskp-streamid').on('depdrop.afterChange', function() {
      if ($(this)[0].hasAttribute('disabled')) {
        $('#provider-form').yiiActiveForm('updateAttribute', $(this).attr('id'), ['Api error. Can not get elements']);
      }
    });
    
    $('#providersettingskp-instanceid').on('depdrop.afterChange', function() {
      console.log(arguments);
      var createdStreamId = parseInt($('[name="createdStreamId"]').val());
      if (createdStreamId) {
        $(this).val(createdStreamId);
      }
    }).on('change', function() {
      if ($(this).val()) {
        $('#create-stream').removeAttr('disabled');
      }
    });
    
    if ($('#providersettingskp-instanceid').val()) {
      $('#create-stream').removeAttr('disabled');
    }
    
    $('#providersettingskp-streamid').on('depdrop.error', depdropErrorHandler);
    
    $(document).on('change', '#providersettingskp-streamid,#providersettingskp-providerid', function() {
      var instanceId = $('#providersettingskp-instanceid').val()
        , providerId = $('#providersettingskp-providerid').val()
        , streamId = $('#providersettingskp-streamid').val()
        ;
      
      if (!instanceId || !providerId || !streamId) {
        return ;
      }
      
      
      
      $
        .getJSON('{$collectUrl}?instanceId=' + instanceId + '&providerId=' + providerId + '&streamId=' + streamId)
        .done(function(res) {
          if (res.success) {
            $('#provider-code').val(res.data.providerCode).trigger('change');
            $('#provider-url').val(res.data.tdsUrl).trigger('change');
            $('#providersettingskp-api_url').val(res.data.providerUrl).trigger('change');
          } else {
            $('#provider-form').yiiActiveForm('updateAttribute', 'providersettingskp-streamid', [res.data.message]);
          }
        })
        .error(function(err) {
          $('#provider-form').yiiActiveForm('updateAttribute', 'providersettingskp-streamid', [err]);
        })
      ;
      // сделать запрос на сервер
      // передать все параметры
      // сформировать на сервере все поля
      // подставить в нужные из респонса
    });
    
    $(document).on('click', '#create-stream', function() {
      $(this).attr('disabled', 'disabled');
      var instanceId = $('#providersettingskp-instanceid').val();
      $
        .getJSON('{$createUrl}?instanceId=' + instanceId)
        .done(function(res) {
          if (res.success) {
            $('[name="createdStreamId"]').val(res.data.id);
            $('#providersettingskp-instanceid').trigger('change');
          } else {
            $('#provider-form').yiiActiveForm('updateAttribute', 'providersettingskp-streamid', [res.data.message]);
          }
        })
        .error(function() {
          $('#provider-form').yiiActiveForm('updateAttribute', 'providersettingskp-streamid', ['Api Error: Can not create stream']);
        })
        .always(function() {
          $(this).removeAttr('disabled');
        }.bind(this))
      ;
    });
  })

JS;

$this->registerJs($js, \mcms\common\web\View::POS_READY);

?>
