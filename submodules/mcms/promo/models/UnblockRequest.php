<?php

namespace mcms\promo\models;

use mcms\user\models\User;
use yii\db\Query;

/**
 * Модель для создания запросов на разблокировку из админки c возможность создавать заявки на все ленды оператора
 */
class UnblockRequest extends LandingUnblockRequest
{
  /**
   * @var
   */
  public $operatorId;

  /**
   * @var
   */
  public $providerId;

  /**
   * tricky: новые правила продублировать в модель LandingUnblockRequest
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'status'], 'required'],
      ['landing_id', 'required', 'when' => function ($model) {
        return $model->operatorId === null && $this->providerId === null;
      }],
      ['operatorId', 'required', 'when' => function ($model) {
        return $model->landing_id === null && $this->providerId === null;
      }],
      [['landing_id', 'user_id'], 'unique', 'targetAttribute' => ['landing_id', 'user_id']],
      ['description', 'required'],
      [['description', 'reject_reason'], 'string'],
      [['status', 'landing_id', 'user_id', 'operatorId', 'providerId'], 'integer'],
      [['landing_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landing::class, 'targetAttribute' => ['landing_id' => 'id']],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      ['traffic_type', 'safe']
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return array_merge(parent::attributeLabels(), $this->translateAttributeLabels([
      'operatorId',
      'providerId',
    ]));
  }

  /**
   * Сохраняет заявки по всем лендингам оператора со статусом по запросу
   * @return int
   */
  public function saveByOperator()
  {
    $landings = (new Query())
      ->select(['l.id'])
      ->from(Landing::tableName() . ' l')
      ->leftJoin(LandingOperator::tableName() . ' lo', 'l.id = lo.landing_id')
      ->leftJoin(
        LandingUnblockRequest::tableName() . ' lur',
        'lur.landing_id = lo.landing_id AND lur.user_id = :user_id',
        [':user_id' => $this->user_id]
      )
      ->where([
        'lo.operator_id' => $this->operatorId,
        'lo.is_deleted' => 0,
        'l.status' => Landing::STATUS_ACTIVE,
        'l.access_type' => [Landing::ACCESS_TYPE_BY_REQUEST, Landing::ACCESS_TYPE_HIDDEN],
        'lur.id' => null,
      ])
      ->andFilterWhere(['l.provider_id' => $this->providerId])
      ->groupBy('l.id')
      ->each();

    $errors = [];
    foreach ($landings as $landing) {
      $landingUnblockRequest = new LandingUnblockRequest([
        'traffic_type' => json_encode($this->traffic_type),
        'description' => $this->description,
        'status' => $this->status,
        'landing_id' => $landing['id'],
        'user_id' => $this->user_id,
      ]);

      if (!$landingUnblockRequest->save()) {
        $errors[] = $landingUnblockRequest->getErrors();
      }
    }

    if (!empty($errors)) {
      return false;
    }

    return true;
  }

  /**
   * @return bool
   */
  public function saveByProvider()
  {
    $landings = (new Query())
      ->select(['l.id'])
      ->from(Landing::tableName() . ' l')
      ->leftJoin(LandingOperator::tableName() . ' lo', 'l.id = lo.landing_id')
      ->leftJoin(
        LandingUnblockRequest::tableName() . ' lur',
        'lur.landing_id = lo.landing_id AND lur.user_id = :user_id',
        [':user_id' => $this->user_id]
      )
      ->where([
        'l.status' => Landing::STATUS_ACTIVE,
        'l.access_type' => [Landing::ACCESS_TYPE_BY_REQUEST, Landing::ACCESS_TYPE_HIDDEN],
        'lur.id' => null,
        'l.provider_id' => $this->providerId
      ])
      ->andFilterWhere([
        'lo.operator_id' => $this->operatorId,
        'lo.is_deleted' => $this->operatorId ? 0 : null, // если оператор не указан, не учитываем
      ])
      ->groupBy('l.id')
      ->each();

    $errors = [];
    foreach ($landings as $landing) {
      $landingUnblockRequest = new LandingUnblockRequest([
        'traffic_type' => json_encode($this->traffic_type),
        'description' => $this->description,
        'status' => $this->status,
        'landing_id' => $landing['id'],
        'user_id' => $this->user_id,
      ]);

      if (!$landingUnblockRequest->save()) {
        $errors[] = $landingUnblockRequest->getErrors();
      }
    }

    if (!empty($errors)) {
      return false;
    }

    return true;
  }
}
