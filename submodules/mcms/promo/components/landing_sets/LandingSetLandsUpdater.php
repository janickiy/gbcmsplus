<?php

namespace mcms\promo\components\landing_sets;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\events\landing_sets\LandingsAddedToSet;
use mcms\promo\components\events\landing_sets\LandingsRemovedFromSet;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingSet;
use mcms\promo\models\LandingSetItem;
use mcms\promo\models\Operator;
use Yii;
use yii\base\Object;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;


/**
 * Class LandingSetLandsUpdater
 * @package mcms\promo\components\landing_sets
 */
class LandingSetLandsUpdater extends Object
{
  /** @var  LandingSet */
  public $landingSet;

  public $isForceUpdate = false;

  /**
   * LandingSetLandsUpdater constructor.
   * @param LandingSet $landingSet
   * @param array|null $config
   */
  public function __construct(LandingSet $landingSet, array $config = null)
  {
    $this->landingSet = $landingSet;
    parent::__construct($config);
  }


  public function run()
  {
    self::log('LANDING_SET: ' . json_encode(ArrayHelper::toArray($this->landingSet)));

    // новые ленды и ленды, которые включены статусом для autoupdate=true добавляем.
    ($this->landingSet->autoupdate || $this->isForceUpdate) && $this->addItems();
    // удаляем у ВСЕХ наборов ленды, у которых удалилась связка лендинг-оператор.
    // удаляем у наборов autoupdate=true ленды, у которых лендинг выключился статусом.
    $this->deleteItems();
    // ленды, которые включены статусом для autoupdate=false делаем включенными.
    // ленды, которые были отключены статусом autoupdate=false делаем выключенными.
    !$this->landingSet->autoupdate && $this->updateItems();
  }

  /**
   * @return ActiveQuery
   */
  public function getLandingOperatorsQuery()
  {
    return LandingOperator::findActiveLandingOperators()
      ->where([
        Landing::tableName() . '.category_id' => $this->landingSet->category_id,
      ]);
  }

  /**
   * @return Query
   */
  private function getAddedLandsQuery()
  {
    return (new Query)
      ->from(['lo' => LandingOperator::tableName()])
      ->leftJoin(
        ['lsi' => LandingSetItem::tableName()],
        'lsi.landing_id = lo.landing_id AND lsi.operator_id = lo.operator_id AND lsi.set_id = :set_id',
        [':set_id' => $this->landingSet->id]
      )
      ->leftJoin(['ln' => Landing::tableName()], 'ln.id = lo.landing_id')
      ->leftJoin(['op' => Operator::tableName()], 'op.id = lo.operator_id')
      ->leftJoin(['cn' => Country::tableName()], 'cn.id = op.country_id')
      ->where([
        'and',
        ['lsi.id' => null],
        ['=', 'ln.category_id', $this->landingSet->category_id],
        ['=', 'ln.status', Landing::STATUS_ACTIVE],
        ['=', 'op.status', Operator::STATUS_ACTIVE],
        ['=', 'cn.status', Country::STATUS_ACTIVE],
        ['=', 'lo.is_deleted', 0],
      ]);
  }

  /**
   * @return Query
   */
  private function getOnByStatusLandsQuery()
  {
    return $this->getItemsQuery()->andWhere([
        'and',
        ['=', 'ln.category_id', $this->landingSet->category_id ?: new Expression('ln.category_id')],
        ['=', 'ln.status', Landing::STATUS_ACTIVE],
        ['=', 'op.status', Operator::STATUS_ACTIVE],
        ['=', 'cn.status', Country::STATUS_ACTIVE],
      ]);
  }

  /**
   * @return Query
   */
  private function getDeletedLandsQuery()
  {
    return $this->getItemsQuery()->andWhere([
      'or',
      ['IS', 'lo.landing_id', null],
      ['=', 'lo.is_deleted', 1],
    ]);
  }

  /**
   * @return Query
   */
  private function getOffByStatusLandsQuery()
  {
    $orWhere = [
      'or',
      ['<>', 'ln.status', Landing::STATUS_ACTIVE],
      ['<>', 'op.status', Operator::STATUS_ACTIVE],
      ['<>', 'cn.status', Country::STATUS_ACTIVE],
    ];

    if ($this->landingSet->category_id) {
      $orWhere[] = ['<>', 'ln.category_id', $this->landingSet->category_id];
    }

    return $this->getItemsQuery()->andWhere($orWhere);
  }

  /**
   * @return Query
   */
  private function getItemsQuery()
  {
    return (new Query)
      ->from(['lsi' => LandingSetItem::tableName()])
      ->leftJoin(['lo' => LandingOperator::tableName()], 'lo.landing_id = lsi.landing_id AND lo.operator_id = lsi.operator_id')
      ->leftJoin(['ln' => Landing::tableName()], 'ln.id = lsi.landing_id')
      ->leftJoin(['op' => Operator::tableName()], 'op.id = lsi.operator_id')
      ->leftJoin(['cn' => Country::tableName()], 'cn.id = op.country_id')
      ->where(['lsi.set_id' => $this->landingSet->id]);
  }

  /**
   * @param $msg
   */
  protected static function log($msg)
  {
    Yii::warning($msg, 'landing_sets_new_lands'); // искать в console.log по [warning][landing_sets_new_lands]
  }

  /**
   * @param array $addItems
   */
  private function addSetItems(array $addItems)
  {
    $addItems && Yii::$app->db->createCommand()->batchInsert(
      LandingSetItem::tableName(),
      [
        'set_id',
        'landing_id',
        'operator_id',
      ],
      $addItems
    )->execute();
  }

  /**
   * @param array $itemsIds
   */
  private function deleteSetItems(array $itemsIds)
  {
    $itemsIds && Yii::$app->db->createCommand()->delete(
      LandingSetItem::tableName(),
      ['id' => $itemsIds]
    )->execute();
  }

  /**
   * @param Query $landingsToAddQuery
   * @return array
   */
  private function getItemsToAdd(Query $landingsToAddQuery)
  {
    return array_map(function ($landOperator) {
      /** @var LandingOperator $landOperator */
      return [
        'set_id' => $this->landingSet['id'],
        'landing_id' => $landOperator['landing_id'],
        'operator_id' => $landOperator['operator_id'],
      ];
    }, $landingsToAddQuery->select(['lo.landing_id', 'lo.operator_id'])->all());
  }

  private function addItems()
  {
    $landingsOperatorsToAddQuery = $this->getAddedLandsQuery();
    $setItemsIdsBeforeSync = $this->landingSet->getItems()->select(['id'])->column();
    $setItemsToAdd = $this->getItemsToAdd($landingsOperatorsToAddQuery);

    self::log('newSetItems: ' . json_encode($setItemsToAdd));

    $this->addSetItems($setItemsToAdd);

    $landingSetItems = LandingSetItem::find()
      ->where(['NOT IN', 'id', $setItemsIdsBeforeSync])
      ->andWhere(['set_id' => $this->landingSet->id]);

    if ($landingSetItems->count() > 0) {
      (new LandingsAddedToSet(
        $this->landingSet,
        $landingSetItems
      ))->trigger();
    }
  }

  private function deleteItems()
  {
    // удалились landing_operators
    $setItemsToDelete = $this->getDeletedLandsQuery()->select(['lsi.id', 'lsi.operator_id', 'lsi.landing_id'])->all();

    if ($this->landingSet->autoupdate) {
      $setItemsToDelete = array_merge(
        $setItemsToDelete,
        // выключился статус
        $this->getOffByStatusLandsQuery()->select(['lsi.id', 'lsi.operator_id', 'lsi.landing_id'])->all()
      );
    }

    $setItemsToDeleteIds = ArrayHelper::getColumn($setItemsToDelete, 'id');

    self::log('deleteSetItems: ' . json_encode($setItemsToDelete));

    $this->deleteSetItems($setItemsToDeleteIds);

    if (!empty($setItemsToDeleteIds)) {
      (new LandingsRemovedFromSet(
        $this->landingSet,
        LandingSetItem::find()->where(['id' => $setItemsToDeleteIds])->all()
      ))->trigger();
    }
  }

  private function updateItems()
  {
    // отключаем выключенные
    $setItemsToOff = $this->getOffByStatusLandsQuery()->select(['lsi.id', 'lsi.operator_id', 'lsi.landing_id'])->all();
    $this->updateSetItemsStatus($setItemsToOff, false);

    // включаем включенные
    $setItemsToOn = $this->getOnByStatusLandsQuery()
      ->select(['lsi.id', 'lsi.operator_id', 'lsi.landing_id'])
      ->andWhere(['is_disabled' => 1])
      ->all();

    $this->updateSetItemsStatus($setItemsToOn, true);
  }

  /**
   * @param array $itemsIds
   * @param bool $setEnabled
   */
  private function updateSetItemsStatus(array $itemsIds, $setEnabled)
  {
    $itemsIds && Yii::$app->db->createCommand()->update(
      LandingSetItem::tableName(),
      ['is_disabled' => !$setEnabled],
      ['id' => ArrayHelper::getColumn($itemsIds, 'id')]
    )->execute();
  }
}
