<?php

namespace mcms\payments\models\forms;

use admin\widgets\mass_operation\ModelInterface;
use mcms\common\traits\Translate;
use mcms\payments\models\AutoPayout;
use mcms\payments\models\UserPaymentForm;
use Yii;
use yii\base\Model;

class MassPayoutForm extends Model implements ModelInterface
{
  use Translate;

  const LANG_PREFIX = 'payments.user-payments.';
  const TYPE_CANCEL = 'cancel';
  const TYPE_ANNUL = 'annul';
  const TYPE_MANUAL = 'manual';
  const TYPE_AUTO = 'auto';

  public $selected_id_list;
  public $type;
  public $reason;

  public function rules()
  {
    return [
      [['selected_id_list', 'reason'], 'required'],
      ['selected_id_list', 'each', 'rule' => ['integer']],
      ['type', 'in', 'range' => [self::TYPE_ANNUL, self::TYPE_CANCEL, self::TYPE_MANUAL, self::TYPE_AUTO]],
      [['reason'], 'required', 'when' => function ($model) {
        return $model->type !== self::TYPE_MANUAL;
      }, 'whenClient' => 'function(attribute, value) {
        return $("#masspayoutform-type").val() != "manual";
      }']
    ];
  }

  public function load($data, $formName = null)
  {
    if (!parent::load($data, $formName)) return false;
    $this->selected_id_list = explode(',', $data[$this->formName()]['selected_id_list']);

    return true;
  }

  public function attributeLabels()
  {
    return [
      'type' => self::translate('attribute-type'),
      'reason' => self::translate('attribute-description'),
    ];
  }


  /**
   * @return bool
   */
  public function save()
  {
    $processed = 0;
    foreach ($this->selected_id_list as $paymentId) {
      $model = UserPaymentForm::findOne($paymentId);
      if ($model === null) continue;
      if (!$model->isPayable()) continue;
      if (!$this->process($model)) continue;

      $processed++;
    }

    return $processed === count($this->selected_id_list) ;
  }

  protected function process(UserPaymentForm $model)
  {
    $success = false;

    switch ($this->type) {
      case self::TYPE_CANCEL:
        $success = $model->cancel($this->reason);
        break;
      case self::TYPE_ANNUL:
        $success = $model->annul($this->reason);
        break;
      case self::TYPE_MANUAL: // todo удалить, но проверить выполнение из форма с файлами и через конфирм
        $success = $model->updateProcessToManual();
        break;
      case self::TYPE_AUTO:
        $autoPayout = new AutoPayout($model);
        $success = $autoPayout->pay();
      default:
    }

    return $success;
  }

  public function getTypes()
  {
    return [
      self::TYPE_AUTO => Yii::_t('user-payments.process-auto'),
      self::TYPE_MANUAL => Yii::_t('user-payments.process-manual'),
      self::TYPE_ANNUL => Yii::_t('user-payments.process-annul'),
      self::TYPE_CANCEL => Yii::_t('user-payments.process-cancel'),
    ];
  }

  /**
   * @return string[]
   */
  public function getReasonRequiredTypes()
  {
    return [self::TYPE_ANNUL, self::TYPE_CANCEL];
  }
}
