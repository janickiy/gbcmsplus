<?php

namespace mcms\promo\components;

use mcms\promo\models\PrelandDefaults;
use mcms\promo\models\Source;
use Yii;
use yii\base\Exception;
use yii\base\Object;
use yii\db\Query;

/**
 * Обновляем преленды у ссылки
 * @package mcms\promo\components
 */
class PrelandDefaultsSync extends Object
{
  /* @var integer|array типы прелендов включены/выключены */
  public $type;
  /* @var integer ID ссылки */
  public $sourceId;
  /* @var integer ID пользователя ccылки */
  public $userId;
  /* @var integer ID потока ссылки */
  public $streamId;

  //таблица с операторам ссылки для которых включены преленды
  const SOURCE_ADD_PRELAND_OPERATORS_TABLE = 'source_add_preland_operators';
  //таблица с операторам ссылки для которых выключены преленды
  const SOURCE_OFF_PRELAND_OPERATORS_TABLE = 'source_off_preland_operators';

  /**
   * @throws Exception
   */
  public function init()
  {
    if (!$this->sourceId)
      throw new Exception('Source ID is required');

    if (!is_array($this->type) && !in_array($this->type, [PrelandDefaults::TYPE_ADD, PrelandDefaults::TYPE_OFF]))
      throw new Exception('Wrong default preland type');

    if (
      is_array($this->type)
      && (
        !in_array(PrelandDefaults::TYPE_ADD, $this->type) || !in_array(PrelandDefaults::TYPE_ADD, $this->type)
      ))
      throw new Exception('Wrong default preland types');
  }

  /*
   * Алгоритм работы:
   * Находим оператов из правил прелендов подходящих для этой ссылки
   * Если в одном из этих правил не указаны операторы, но ищем всех операторов похдодящих для этой ссылки
   * Достаем всех операторов для которых включены преленды и находим те которые нужно удалить и удаляем
   * Находим новых операторов которые еще не добавлены и добавляем их
   */
  public function run()
  {
    if (!is_array($this->type)) {
      $this->type = [$this->type];
    }

    foreach ($this->type as $type) {

      $table = $type == PrelandDefaults::TYPE_ADD
        ? self::SOURCE_ADD_PRELAND_OPERATORS_TABLE
        : self::SOURCE_OFF_PRELAND_OPERATORS_TABLE;


      //Операторы для прелендов, подходящие под этот источник
      $defaultPrelands = (new Query())
        ->select(['operators'])
        ->from(PrelandDefaults::tableName())
        ->andwhere([
          'or', ['user_id' => $this->userId], ['user_id' => null]
        ])
        ->andWhere([
          'or', ['stream_id' => $this->streamId], ['stream_id' => null]
        ])
        ->andWhere([
          'or', ['source_id' => $this->sourceId], ['source_id' => null]
        ])
        ->andWhere([
          'status' => PrelandDefaults::STATUS_ACTIVE,
          'type' => $type
        ])
        ->each();

      $operators = [];
      $defaultPrelandsWithoutOperators = false;
      foreach ($defaultPrelands as $defaultPreland) {
        if ($defaultPreland['operators']) {
          $operators = array_merge($operators, @unserialize($defaultPreland['operators']));
        } else {
          //Если у правила не указаны операторы
          $defaultPrelandsWithoutOperators = true;
        }
      }

      if ($defaultPrelandsWithoutOperators) {
        //Ищем всех возможных операторов для данного источника
        $sourcesOperators = (new Query())
          ->select([
            'sol.operator_id',
          ])
          ->from(Source::tableName() . ' s')
          ->innerJoin('sources_operator_landings sol', 's.id = sol.source_id')
          ->where(['s.status' => Source::STATUS_APPROVED])
          ->andWhere(['s.id' => $this->sourceId])
          ->distinct();
        $operators = array_merge($operators, $sourcesOperators->column());
      }
      $operators = array_unique(array_filter($operators));

      //Операторы для прелендов текущего источника
      $sourcePrelandOperators = (new Query())
        ->select(['operator_id'])
        ->from($table)
        ->where(['source_id' => $this->sourceId])
        ->column();

      $operatorsToDelete = [];
      foreach ($sourcePrelandOperators as $sourcePrelandOperator) {
        //Удаляем только тех операторов для прелендов, которых нет в подходящих правилах к этому источнику
        if (!in_array($sourcePrelandOperator, $operators)) {
          $operatorsToDelete[] = $sourcePrelandOperator;
        }
      }

      if ($operatorsToDelete) {
        Yii::trace('Remove operators ' . implode(', ', $operatorsToDelete) . ' from source ' . $this->sourceId . PHP_EOL);
        Yii::$app->db->createCommand()
          ->delete(
            $table,
            [
              'source_id' => $this->sourceId,
              'operator_id' => $operatorsToDelete,
            ]
          )
          ->execute();
      }

      $insertPrelandOperators = [];
      $operatorsToInsert = [];
      foreach ($operators as $addOperator) {
        //Добавляем оператор для преленда только, если его еще нет для текущего источника
        if (!in_array($addOperator, $sourcePrelandOperators)) {
          $insertPrelandOperators[] = [$this->sourceId, $addOperator];
          $operatorsToInsert[] = $addOperator;
        }
      }

      if ($insertPrelandOperators) {
        Yii::trace('Add operators ' . implode(', ', $operatorsToInsert) . ' to source ' . $this->sourceId . PHP_EOL);
        Yii::$app->db->createCommand()
          ->batchInsert(
            $table,
            ['source_id', 'operator_id'],
            $insertPrelandOperators
          )
          ->execute();
      }
    }

    ApiHandlersHelper::clearCache('SourceDataById' . $this->sourceId);
  }


}