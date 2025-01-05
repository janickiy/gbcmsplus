<?php

namespace mcms\promo\components\widgets;


use kartik\form\ActiveForm;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * Class MultipleLandingsInsertWidget
 * @package mcms\promo\components\widgets
 */
class MultipleLandingsInsertWidget extends Widget
{
  /**
   * @var ActiveRecord
   */
  public $model;

  /**
   * @var ActiveForm
   */
  public $form;

  /**
   * @var string
   */
  public $attribute = 'landings';

  /**
   * @var string
   */
  public $isMultipleAttribute = 'isMultiple';

  /**
   * @var string
   */
  public $baseAttribute = 'landing_id';

  /**
   * @inheritdoc
   */
  public function init()
  {
    $this->isValid() && $this->registerJs();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    if (!$this->isValid()) {
      return;
    }

    $model = $this->model;

    echo Html::activeHiddenInput($model, $this->isMultipleAttribute);
    echo $this->form->field($this->model, 'landings', ['options' => [
      'style' => 'display:none;',
      'class' => 'form-group',
    ]])->textarea([
      'placeholder' => $model::translate('multiple-landings-hint')
    ]);

    echo Html::a($model::translate('choose-one'), '#', [
      'id' => 'add-landings-multiple',
      'class' => 'change-landing-insert-type',
      'style' => 'display: none;'
    ]);
    echo Html::a($model::translate('add-few'), '#', [
      'id' => 'add-single-landing',
      'class' => 'change-landing-insert-type',
    ]);
  }

  /**
   * @return bool
   */
  protected function isValid()
  {
    return $this->model->isNewRecord;
  }

  /**
   * @inheritdoc
   */
  protected function registerJs()
  {
    $landingInputName = Html::getInputName($this->model, $this->baseAttribute);
    $landingsMultipleInputName = Html::getInputName($this->model, $this->attribute);
    $isMultipleInputName = Html::getInputName($this->model, $this->isMultipleAttribute);

    $this->view->registerJs(<<<JS
      var oldInputWrapper = $('[name="$landingInputName"]').parents('.form-group');
      var multipleInputWrapper = $('[name="$landingsMultipleInputName"]').parents('.form-group');
      var isMultipleInput = $('[name="$isMultipleInputName"]');
      $('.change-landing-insert-type').click(function (e) {
        e.preventDefault();
        
        $('#add-landings-multiple').toggle();
        $('#add-single-landing').toggle();
        oldInputWrapper.toggle();
        multipleInputWrapper.toggle();
        var isMultiple = parseInt(isMultipleInput.val());
        isMultipleInput.val(isMultiple ? 0 : 1);
      });
JS
    );
  }
}