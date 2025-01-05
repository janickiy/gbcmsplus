<?php

namespace mcms\partners\components\widgets;

use Yii;
use yii\bootstrap\Widget;

/**
 * Class ConfirmWidget
 * @package mcms\partners\assets\basic
 */
class ConfirmWidget extends Widget
{
  public function init()
  {
    parent::init();

    $js = <<<JS
(function() {
  var confirmModal = $('#confirmModal'),
      confirmOk = function() {},
      confirmCancel = function() {};
  
  $('#confirmAccept').on('click', function (e) {
    confirmOk();
    confirmModal.modal('hide');
  });
  
  $('#confirmDecline').on('click', function () {
    confirmCancel();
    confirmModal.modal('hide');
  });
  
  confirmModal.find('.close').on('click', function () {
    confirmCancel();
    confirmModal.modal('hide');
  });
  
  yii.confirm = function (message, ok, cancel, header) {
    header && $('#confirmModalHeader').text(header);
    $('#confirmModalBody').text(message);
    
    if (typeof(ok) === 'function') {
      confirmOk = ok;
    }
    if (typeof(cancel) === 'function') {
      confirmCancel = cancel;
    }
  
    confirmModal.modal('show');
  };
})();

JS;

    Yii::$app->view->registerJs($js);
  }

  public function run()
  {
    return $this->render('confirm', [
      'yes' => Yii::t('yii', 'Yes'),
      'no' => Yii::t('yii', 'No'),
      'defaultHeader' => Yii::_t('main.confirm_action'),
    ]);
  }
}
