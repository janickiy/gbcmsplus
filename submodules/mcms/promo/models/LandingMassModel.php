<?php

namespace mcms\promo\models;

use mcms\common\models\AbstractMassModel;
use mcms\common\traits\Translate;
use mcms\promo\Module;
use Yii;
use yii\base\Model;

/**
 *
 */
class LandingMassModel extends AbstractMassModel
{
  use Translate;

  public $status;
  public $access_type;
  public $category_id;
  public $offer_category_id;
  public $local_currency_id;
  public $buyout_price_rub;
  public $buyout_price_eur;
  public $buyout_price_usd;
  public $local_currency_rebill_price;
  public $rebill_price_rub;

  /**
   * @var Landing
   */
  public $model;

  const LANG_PREFIX = 'promo.landings.';

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return array_merge(self::translateAttributeLabels([
      'status',
      'access_type',
      'category_id',
      'offer_category_id',
    ]), [
      'local_currency_id' => Yii::_t('promo.landings.operator-attribute-local_currency_id'),
      'local_currency_rebill_price' => Yii::_t('promo.landings.operator-attribute-local_currency_rebill_price'),
      'buyout_price_usd' => Yii::_t('promo.landings.operator-attribute-buyout_price_usd'),
      'buyout_price_eur' => Yii::_t('promo.landings.operator-attribute-buyout_price_eur'),
      'buyout_price_rub' => Yii::_t('promo.landings.operator-attribute-buyout_price_rub'),
      'rebill_price_rub' => Yii::_t('promo.landings.operator-attribute-rebill_price_rub'),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function ownFields()
  {
    $fields = [
      'status' => 'status',
      'access_type' => 'access_type',
      'category_id' => 'category_id',
      'offer_category_id' => 'offer_category_id',
      'local_currency_id' => 'local_currency_id',
      'buyout_price_rub' => 'buyout_price_rub',
      'buyout_price_eur' => 'buyout_price_eur',
      'buyout_price_usd' => 'buyout_price_usd',
      'local_currency_rebill_price' => 'local_currency_rebill_price',
      'rebill_price_rub' => 'rebill_price_rub',
    ];

    return $fields;
  }

  /**
   * @param null $attributeNames
   * @param bool $clearErrors
   * @return bool
   */
  public function validate($attributeNames = null, $clearErrors = true)
  {
    return Model::validate($attributeNames, $clearErrors);
  }

  public function save(array $selection)
  {
    $toUpdate = [];
    foreach ($this->edit as $attr) {
      if ($attr) {
        $toUpdate[$attr] = $this->{$attr};
      }
    }

    $transaction = Yii::$app->db->beginTransaction();
    try {
//      call_user_func([get_class($this->model), 'updateAll'], $toUpdate, ['id' => $selection]);

      $modelClass = $this->model;
      $models = $modelClass::findAll(['id' => $selection]);
      foreach ($models as $model) {
        /** @var Landing $model */
        $model->setAttributes($toUpdate, false);
        $model->save();
        foreach ($model->landingOperator as $landingOperator) {
          /** @var LandingOperator $landingOperator */
          $landingOperator->setAttributes($toUpdate, false);
          $landingOperator->save();
        }
      }

      $transaction->commit();
    } catch (\yii\db\Exception $e) {
      $transaction->rollBack();

      return false;
    }

    return true;
  }
}
