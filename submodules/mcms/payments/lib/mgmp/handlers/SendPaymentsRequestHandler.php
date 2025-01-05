<?php

namespace mcms\payments\lib\mgmp\handlers;

use mcms\common\web\AjaxResponse;
use mcms\payments\components\mgmp\send\MgmpSenderInterface;
use mcms\payments\lib\mgmp\TypeCaster;
use mcms\payments\models\UserPayment;
use Yii;
use yii\data\ArrayDataProvider;

/**
 * Class SendPaymentsRequestHandler
 * Формирование ответа для api получения выплат
 * @package mcms\payments\lib\mgmp\handlers
 */
class SendPaymentsRequestHandler
{
  /**
   * @param $request
   */
  public function handle($request)
  {
    $resellers = [];
    foreach (Yii::$app->getModule('users')->api('usersByRoles', ['reseller'])->getResult() as $user) {
      $resellers[] = $user['id'];
    }

    $models = UserPayment::find()->where([
      'or',
      ['status' => UserPayment::STATUS_PROCESS],
      ['user_id' => $resellers, 'status' => [UserPayment::STATUS_PROCESS, UserPayment::STATUS_AWAITING, UserPayment::STATUS_DELAYED]],
    ])->with(['user', 'userWallet'])->andWhere(
      ['>=', 'updated_at', $request['from_time']]
    )->andWhere(['processing_type' => UserPayment::PROCESSING_TYPE_EXTERNAL])
      ->each();

    $result = [];
    foreach ($models as $model) {
      /* Крайняя дата выполнения расчитывается именно при отправке в МП, что бы не было ситуации когда рес создал выплату,
      * через две недели отправил в МП и в МП она сразу же появилась как просроченная */
      if ($model->user_id === UserPayment::getResellerId()) {
        /** @var \mcms\payments\components\mgmp\send\MgmpSenderInterface $mgmp */
        $mgmp = Yii::createObject(MgmpSenderInterface::class);
        $model->pay_period_end_date = $mgmp->getResellerPayPeriodEndDate();
      }

      /** @var UserPayment $model */
      $attributes = $model->attributes;
      $attributes['account'] = $model->userWallet ? $model->userWallet->getAccountAssoc() : [];
      $attributes['status'] = TypeCaster::mcms2mgmp($attributes['status']);
      if ($model->user->hasRole('partner')) {
        $attributes['partner_id'] = $attributes['user_id'];
      }
      unset($attributes['user_id']);
      unset($attributes['amount']);
      unset($attributes['invoice_amount']);
      unset($attributes['invoice_currency']);
      unset($attributes['info']);

      // сохраняем статус в процессе, когда мгмп запросил у нас эту выплату
      // tricky возможно тут что-то сломал, пока чинил MGMP-93
      // tricky иначе updated_at у выплаты постоянно обновлялся после запроса выплат. А это капец какой-то
      if ($model->status != $model::STATUS_PROCESS) {
        $model->status = $model::STATUS_PROCESS;
        $model->save();
      }

      $result[] = $attributes;
    }


    $data = new ArrayDataProvider([
      'allModels' => $result,
    ]);

    return AjaxResponse::success($data->getModels());
  }
}
