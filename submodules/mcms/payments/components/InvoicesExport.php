<?php

namespace mcms\payments\components;

use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use yii\base\Model;

/**
 * Экспорт инвойсов
 */
class InvoicesExport extends Model
{
  /** @var int[] */
  public $types;
  /** @var int */
  public $dateFrom;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['types'], 'required'],
      ['dateFrom', 'integer'],
      ['types', 'each', 'rule' => ['integer']],
    ];
  }

  /**
   * Получить список инвойсов
   * @return array|bool
   */
  public function getInvoices()
  {
    if (!$this->validate()) return false;

    // TRICKY Важно осторожно менять этот запрос, иначе могут синхронизироваться лишние инвойсы
    // Если не следовать этому принципу, получится крайне сложный в обнаружении баг и финансовые потери
    return (array)UserBalanceInvoice::find()
      ->andWhere(['type' => $this->types, 'user_id' => UserPayment::getResellerId()])
      ->andFilterWhere(['>=', 'updated_at', $this->dateFrom ?: null])
      ->asArray()
      ->all();
  }
}