<?php

namespace mcms\common\validators;

use Yii;
use yii\base\Model;
use yii\validators\Validator;

/**
 * Валидатор сравнения диапазона дат с датой когортов в фильтре статы
 */
class DateCompareValidator extends Validator
{
  /**
   * @var string
   */
  public $dateRange;

  /**
   * Нужен только JS-валидатор
   * @param Model $model
   * @param string $attribute
   */
  public function validateAttribute($model, $attribute)
  {

  }

  /**
   * @param Model $model
   * @param string $attribute
   * @param \yii\web\View $view
   * @return string
   */
  public function clientValidateAttribute($model, $attribute, $view)
  {
    $dateRangeId = strtolower(sprintf('%s-%s', $model->formName(), $this->dateRange));
    $message = Yii::_t('commonMsg.main.date-compare-error', [$model->getAttributeLabel($attribute), $model->getAttributeLabel($this->dateRange)]);

    return <<<JS
    var dateCompare = function(value) {
      var dateMore = new Date(value);
      var dateLess = \$('#$dateRangeId').data('daterangepicker').startDate;
      
      return !value || dateMore >= dateLess;
    };
    if (!dateCompare(value)) {
        messages.push('$message');
    }
JS;
  }

}
