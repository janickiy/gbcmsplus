<?php

namespace mcms\notifications\components\event\driver;

use mcms\user\models\User;

interface DriverInterface
{
  public function send(User $receiver);
  public function sendHandler(User $receiver);
}