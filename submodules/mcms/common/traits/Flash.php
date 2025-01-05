<?php

namespace mcms\common\traits;

use mcms\common\widget\alert\Alert;
use Yii;

trait Flash
{
  private function setFlash($type, $langString, $params = [])
  {
    Yii::$app->session->setFlash($type, Yii::_t($langString, $params));
  }

  public function flashRawSuccess($message)
  {
    Yii::$app->session->setFlash(Alert::TYPE_SUCCESS, $message);
  }

  public function flashRawError($message)
  {
    Yii::$app->session->setFlash(Alert::TYPE_DANGER, $message);
  }

  public function flashSuccess($langString, $params = [])
  {
    $this->setFlash(Alert::TYPE_SUCCESS, $langString, $params);
  }

  public function flashFail($langString, $params = [])
  {
    $this->setFlash(Alert::TYPE_DANGER, $langString, $params);
  }
}