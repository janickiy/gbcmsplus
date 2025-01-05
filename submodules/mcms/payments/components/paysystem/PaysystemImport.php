<?php
namespace mcms\payments\components\paysystem;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\lib\mgmp\PaysystemsCodeCaster;
use mcms\payments\models\wallet\Wallet;
use rgk\utils\interfaces\ExecutableInterface;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\Json;

/**
 * Импорт данных о платежной системе
 */
class PaysystemImport extends Object implements ExecutableInterface
{
  /** @var array Данные платежной системы */
  public $mgmpPaysystem;
  /** @var bool Доступен ли процессинг выплат через эту ПС */
  public $isMgmpPaymentsAvailable;

  /**
   * @inheritdoc
   * @throws \yii\base\InvalidParamException
   */
  public function execute()
  {
    if (!$this->mgmpPaysystem || $this->isMgmpPaymentsAvailable === null) {
      throw new InvalidParamException('Параметры mgmpPaysystem и isMgmpPaymentsAvailable обязательны');
    }

    $paysystemCode = PaysystemsCodeCaster::mgmp2mcms(ArrayHelper::getValue($this->mgmpPaysystem, 'code'));
    if (!$paysystemCode) {
      Yii::error('Отсутствует значение PaysystemImport::mgmpPaysystem[code] ' . Json::encode($this->mgmpPaysystem), __METHOD__);
      return false;
    }

    $paysystem = Wallet::findOne(['code' => $paysystemCode]);
    if (!$paysystem) {
      Yii::error('Не удалось найти платежную систему по коду ПС из MGMP ' . Json::encode($this->mgmpPaysystem), __METHOD__);
      return false;
    }

    $paysystem->is_mgmp_payments_enabled = $this->isMgmpPaymentsAvailable;

    if (!$paysystem->save()) {
      Yii::error('Не удалось сохранить ПС. '
        . 'Mgmp data: ' . Json::encode($this->mgmpPaysystem)
        . 'Model: ' . Json::encode($paysystem->toArray())
        . 'Errors: ' . Json::encode($paysystem->getErrors()), __METHOD__);
      return false;
    }

    return true;
  }
}