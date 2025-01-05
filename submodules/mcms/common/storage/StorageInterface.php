<?php

namespace mcms\common\storage;

interface StorageInterface
{
  public function findOne(array $attributes);
  public function findMany(array $attributes);
}