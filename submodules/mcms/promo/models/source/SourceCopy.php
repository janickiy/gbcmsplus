<?php
namespace mcms\promo\models\source;

use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;
use Yii;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class SourceCopy
 * @package mcms\promo\models\source
 */
class SourceCopy extends Source
{

  const POSTFIX_NAME = ' copy';

  /** @var  Source */
  public $donor;

  /**
   * @return array
   */
  public function rules()
  {
    return [
      ['user_id', 'compare', 'compareValue' => $this->donor->user_id],
      ['user_id', 'compare', 'compareValue' => Yii::$app->user->id],
    ];
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    // TRICKY скопировано из BaseActiveRecord
    $this->trigger($insert ? self::EVENT_AFTER_INSERT : self::EVENT_AFTER_UPDATE, new AfterSaveEvent([
      'changedAttributes' => $changedAttributes
    ]));
  }

  /**
   * @return SourceCopy|null
   */
  public function makeCopy()
  {
    $this->copySource();
    $this->id && $this->copyLands();

    return $this->getErrors() ? null : $this;
  }

  /**
   * @param ActiveRecord $model
   * @return array
   */
  private static function getModelAttributes(ActiveRecord $model)
  {
    $attributes = $model->attributes();
    return array_combine($attributes, $attributes);
  }

  private function copySource()
  {
    $this->scenario = self::SCENARIO_PARTNER_COPY;
    $this->setAttributes($this->getSourceAttributes());
    $this->insert();
  }

  private function copyLands()
  {
    $sourceLandOpTable = SourceOperatorLanding::tableName();

    $attributes = $this->getSourceLandOpAttributes();

    $columns = implode(', ', array_keys($attributes));

    $selectQuery = (new Query())
      ->select($attributes)
      ->from($sourceLandOpTable)
      ->where(['source_id' => $this->donor->id])
      ->createCommand()
      ->getRawSql();

    Yii::$app->db->createCommand("INSERT INTO $sourceLandOpTable ($columns) $selectQuery")->execute();
  }

  /**
   * @return array
   */
  private function getSourceAttributes()
  {
    $attributes = $this->donor->getAttributes();

    unset($attributes['id']);

    $attributes['created_at'] = time();
    $attributes['updated_at'] = time();
    $attributes['hash'] = Source::generateHash();
    $attributes['name'] .= self::POSTFIX_NAME;

    return $attributes;
  }

  /**
   * @return array
   */
  private function getSourceLandOpAttributes()
  {
    $attributes = self::getModelAttributes(new SourceOperatorLanding());

    unset($attributes['id']);

    $attributes['source_id'] = new Expression($this->id);

    return $attributes;
  }


}