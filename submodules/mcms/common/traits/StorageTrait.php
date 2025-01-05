<?php

namespace mcms\common\traits;

use yii\data\Pagination;

trait StorageTrait{

  /** @var  \yii\db\ActiveRecord  */
  private $_model;

  public function findOne(array $attributes)
  {
    return $this->_model->findOne($attributes);
  }

  public function findMany(array $attributes)
  {
    return $this->_model->findAll($attributes);
  }

  /**
   * @param $attributes
   * @param int $limit
   * @return \mcms\common\storage\Pagination
   */
  public function paginate(array $attributes = [], $limit = 20)
  {
    $query = $this->_model->find();

    if (empty($attributes)) {
      $query->where($attributes);
    } else {
      $query->where($attributes[0], $attributes[1]);
    }

    $countQuery = clone $query;

    $pages = new Pagination(['totalCount' => $countQuery->count()]);

    $models = $query
      ->offset($pages->offset)
      ->limit($limit)
      ->all();

    return (new \mcms\common\storage\Pagination())
      ->setModels($models)
      ->setPages($pages);
  }
}