<?php

namespace mcms\promo\components\provider_instances_sync\dto;

class Stream implements \JsonSerializable
{
  /** @var int */
  public $id;

  /** @var string */
  public $name;

  /** @var string */
  public $hash;

  /** @var string */
  public $url;

  /**
   * @param int $id
   * @return Stream
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @param string $name
   * @return Stream
   */
  public function setName($name)
  {
    $this->name = $name;
    return $this;
  }

  /**
   * @param string $hash
   * @return Stream
   */
  public function setHash($hash)
  {
    $this->hash = $hash;
    return $this;
  }

  /**
   * @param string $url
   * @return Stream
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