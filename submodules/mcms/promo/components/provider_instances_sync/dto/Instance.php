<?php

namespace mcms\promo\components\provider_instances_sync\dto;

/**
 * Обьект инстанца
 * Class Instance
 * @package mcms\promo\components\provider_instances_sync\dto
 */
class Instance implements \JsonSerializable
{
  /** @var int */
  public $id;

  /** @var string */
  public $name;

  /** @var string */
  public $domain;

  /**
   * @param int $id
   * @return Instance
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @param string $name
   * @return Instance
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @param string $domain
   * @return Instance
   */
  public function setDomain($domain)
  {
    $this->domain = $domain;
    return $this;
  }

  /**
   * Specify data which should be serialized to JSON
   * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
   * @return mixed data which can be serialized by <b>json_encode</b>,
   * which is a value of any type other than a resource.
   * @since 5.4.0
   */
  public function jsonSerialize()
  {
    return get_object_vars($this);
  }
}