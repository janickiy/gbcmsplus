<?php

namespace mcms\partners\components\widgets;

use Yii;
use yii\base\Widget;
use mcms\partners\models\DomainForm;

class DomainCreateFormWidget extends Widget
{
  /**
   *
   * @var type
   */
  public $model;
  /** @var string A-запись домена */
  private $aDomainIp;

  public function init()
  {
    parent::init();
    $this->aDomainIp = Yii::$app->getModule('promo')->getSettingsDomainIp();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    return $this->render('domain_create_form', [
      'model' => $this->model ? : new DomainForm(['url' => 'http://']),
      'aDomainIp' => $this->aDomainIp
    ]);
  }
}