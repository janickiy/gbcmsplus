<?php

namespace admin\widgets\mass_operation;

use mcms\common\helpers\Html;
use yii\base\Model;
use yii\base\Widget as BaseWidget;
use yii\bootstrap\Html as BHtml;
use yii\web\View;

class Widget extends BaseWidget
{
  /**
   * View которую надо зарендерить в модалке
   * @var string
   */
  public $formView;
  /**
   * Модель формы
   * @var Model
   */
  public $formModel;

  /**
   * Экшен формы
   * @var array
   */
  public $formAction;
  /**
   * Контейнер, который надо обновить после выполнения запроса
   * @var string
   */
  public $updatePjaxId;

  /**
   * Селектор формы, куда засетить выбранные id
   * @var string
   */
  public $selectionFormAttributeDom;

  /**
   * Селектор dom, откуда взять выбранные элементы
   * @var string
   */
  public $selectedElementsDom;

  /**
   * @var string
   */
  public $selectedAllElementsDom;
  /**
   * Заголовок модалки
   * @var string
   */
  public $modalHeader;

  /**
   * Тайтл для кнопки по массовому действию
   * @var string
   */
  public $buttonActionTitle;

  protected $modalOptions = [];

  protected $containerId;

  public function init()
  {
    parent::init();

    $this->containerId = Html::getUniqueId();

    $this->modalOptions = [
      'header' => $this->modalHeader,
      'toggleButton' => [
        'label' => BHtml::icon('edit') . ' ' . $this->buttonActionTitle . ' ' . \yii\helpers\Html::tag('span', '', ['class' => $this->containerId . '-items-count']),
        'class' => 'btn btn-xs btn-default ' . $this->containerId . '-modal-button',
        'disabled' => true
      ],
      'options' => [
        'class' => $this->containerId . '-modal custom-modal'
      ],
      'clientEvents' => [
        'shown.bs.modal' => $this->onShowModal(),
      ]
    ];

    $this->registerJs();
  }

  protected function onShowModal()
  {
    return <<<JS
function (e) {
  var selection = [];
  $('{$this->selectedElementsDom}').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  $('{$this->selectionFormAttributeDom}').val(selection.join(','));
}
JS;
  }

  protected function registerJs()
  {
    $js = <<<JS
$(document).on('change', '{$this->selectedElementsDom}, {$this->selectedAllElementsDom}', function(e) {
  var selection = [],
      massButton = $('.{$this->containerId}-modal-button');
  
  $('{$this->selectedElementsDom}').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  
  if (selection.length) {
    massButton.find('.{$this->containerId}-items-count').html('(' + selection.length + ')');
  } else {
    massButton.find('.{$this->containerId}-items-count').html('');
  }
  
  massButton.prop('disabled', !selection.length);
});

$('{$this->updatePjaxId}').on('pjax:complete', function() {
  var massButton = $('.{$this->containerId}-modal-button');
  massButton.find('.{$this->containerId}-items-count').html('');
  massButton.prop('disabled', true);
});
JS;

    $this->view->registerJs($js, View::POS_READY);
  }


  public function run()
  {
    return $this->render('mass_operation', [
      'formAction' => $this->formAction,
      'formModel' => $this->formModel,
      'formView' => $this->formView,
      'updatePjaxId' => $this->updatePjaxId,
      'modalOptions' => $this->modalOptions,
      'containerId' => $this->containerId,
    ]);
  }
}
