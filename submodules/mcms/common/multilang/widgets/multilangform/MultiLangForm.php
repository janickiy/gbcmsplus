<?php

namespace mcms\common\multilang\widgets\multilangform;

use kartik\builder\BaseForm;
use kartik\builder\Form;

use yii\helpers\ArrayHelper;

/**
 * Class MultiLangForm - виджет для создания мультиязычных форм
 * @package mcms\common\multilang\widgets\multilangform
 */
class MultiLangForm extends BaseForm
{

  /**
   * @var Model the model used for the form
   */
  public $model;

  /**
   * @var array the multilang model attributes
   */
  public $multilangAttributes = [];

  public function init()
  {
    // инициализируем мультилендинг аттрибуты из модели
    if (!empty($this->model->multilangAttributes) && is_array($this->model->multilangAttributes)) {
      $this->multilangAttributes = $this->model->multilangAttributes;
    }
  }

  public function run()
  {
    MultiLangFormAsset::register($this->getView());

    return $this->render('form', [
      'tabForms' => $this->getTabsForms(),
      'model' => $this->model,
      'languages' => $this->getLanguages(),
      'attributes' => $this->attributes
    ]);
  }

  private function getLanguages()
  {
    return \Yii::$app->params['languages'];
  }

  /**
   * getMultilangFields - группирует данные для разделения по табам, создает атрибуты на основе языков   *
   * @return array
   */
  private function getMultilangFields()
  {

    $languages = $this->getLanguages();
    $langAttributes = [];
    $unsetAttrs = [];

    foreach ($languages as $lang) {

      foreach ($this->attributes as $attribute => $settings) {

        if (!in_array($attribute, $this->multilangAttributes)) continue;

        $value = $this->model->$attribute;
        if (!empty($value) && is_object($value)) {
          $this->model->setAttribute($attribute, ArrayHelper::toArray($value));
        }

        $unsetAttrs[$attribute] = $settings;
        $attribute .= '[' . $lang . ']';
        $langAttributes[$lang][$attribute] = $settings;

      }
    }

    // оставляем в списке атрибутов только не мультиязычные
    $this->attributes = array_diff_key($this->attributes, $unsetAttrs);

    return $langAttributes;

  }

  private function getTabsForms()
  {

    $langAttributes = $this->getMultilangFields();
    $languages = $this->getLanguages();
    $tabForm = [];

    foreach ($languages as $lang) {

      $tabForm[$lang] = Form::widget([
        'model' => $this->model,
        'form' => $this->form,
        'attributes' => ArrayHelper::merge($langAttributes[$lang], $this->attributes)
      ]);

      // сбрасываем не мультиязычные атрибуты после добавления в первую форму
      $this->attributes = [];

    }

    return $tabForm;

  }


}