<?php

namespace mcms\payments\components\widgets;

use yii\widgets\ActiveForm;

interface IFormBuildeModel
{
  /**
   * @return array
   */
  public function getFormFields();

  public function getAdminFormFields();

  /**
   * @param ActiveForm $form
   * @param array $options
   * @param string $submitButtonSelector
   * @return array
   */
  public function getCustomFields($form, $options = [], $submitButtonSelector = '[type="submit"]');


  /**
   * @param ActiveForm $form
   * @param array $options
   * @return array
   */
  public function getAdminCustomFields($form, $options = []);

  public function formName();
}