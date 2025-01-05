<?php

namespace mcms\common\storage;

interface PaginationInterface
{
  public function getModels();
  public function getPages();
}