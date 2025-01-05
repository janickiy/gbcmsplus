<?php

namespace mcms\common\storage;

use mcms\common\storage\PaginationInterface;
use yii\web\Linkable;

class Pagination implements PaginationInterface
{
  private $models;
  private $pages;

  /**
   * @param mixed $models
   * @return $this
   */
  public function setModels(array $models)
  {
    $this->models = $models;
    return $this;
  }

  /**
   * @param mixed $pages
   * @return $this
   */
  public function setPages(Linkable $pages)
  {
    $this->pages = $pages;
    return $this;
  }

  public function getModels()
  {
    return $this->models;
  }

  public function getPages()
  {
    return $this->pages;
  }
}