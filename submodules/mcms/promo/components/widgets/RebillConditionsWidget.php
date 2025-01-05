<?php
namespace mcms\promo\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\search\RebillConditionsSearch;
use mcms\promo\Module;
use yii\base\Widget;
use Yii;

class RebillConditionsWidget extends Widget
{
  private $partnerId;
  public $options;
  private $queryParams = [];
  private $renderCreateButton = true;
  private $enableSort = true;
  private $enableFilters = true;
  private $renderActions = true;
  private $searchModel;
  private $layout;
  const DEFAULT_LAYOUT = '{items}';

  public function init()
  {
    $this->partnerId = ArrayHelper::getValue($this->options, 'partnerId');
    $this->queryParams = Yii::$app->request->getQueryParams();
    $this->layout = ArrayHelper::getValue($this->options, 'layout', self::DEFAULT_LAYOUT);
    $this->enableSort = ArrayHelper::getValue($this->options, 'enableSort', $this->enableSort);
    $this->renderActions = ArrayHelper::getValue($this->options, 'renderActions', $this->renderActions);
    $this->renderCreateButton = ArrayHelper::getValue($this->options, 'renderCreateButton', $this->renderCreateButton);
    $this->enableFilters = ArrayHelper::getValue($this->options, 'enableFilters', $this->enableFilters);
    parent::init();
  }

  /**
   * @inheritDoc
   */
  public function run()
  {
    if (!Yii::$app->user->can(Module::PERMISSION_CAN_VIEW_REBILL_CONDITIONS_WIDGET)) return null;

    $userModule = Yii::$app->getModule('users');
    if ($this->partnerId) {
      $userRoles = ArrayHelper::getColumn($userModule->api('rolesByUserId', ['userId' => $this->partnerId])->getResult(), 'name');
      if (!in_array($userModule::PARTNER_ROLE, $userRoles)) return null;
    }

    return $this->render('@mcms/promo/views/rebill-conditions/rebill-conditions', [
      'dataProvider' => $this->getDataProvider(),
      'partnerId' => $this->partnerId,
      'renderCreateButton' => $this->renderCreateButton,
      'searchModel' => $this->searchModel,
      'layout' => $this->layout,
      'renderActions' => $this->renderActions,
      'enableSort' => $this->enableSort,
      'enableFilters' => $this->enableFilters,
      'userModule' => $userModule,
    ]);
  }

  protected function getDataProvider()
  {
    $this->searchModel = (new RebillConditionsSearch([
      'partner_id' => $this->partnerId
    ]));
    $dataProvider = $this->searchModel->search($this->queryParams);
    return $dataProvider;
  }
}