<?php

namespace mcms\promo\components\provider_instances_sync\dto;

class Provider implements \JsonSerializable
{
  /** @var int */
  public $id;

  /** @var string */
  public $name;

  /** @var string */
  public $code;

  /** @var string */
  public $url;

  /**
   * @param int $id
   * @return Provider
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @param string $name
   * @return Provider
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @param string $code
   * @return Provider
   */
  public function setCode($code)
  {
    $this->code = $code;
    return $this;
  }

  /**
   * @param string $url
   * @return Provider
   */
  public function setUrl($url)
  {
    $this->url = $url;
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