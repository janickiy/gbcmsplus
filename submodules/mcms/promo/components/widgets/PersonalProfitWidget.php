<?php

namespace mcms\promo\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\api\UserPromoSettings;
use mcms\promo\models\search\PersonalProfitSearch;
use mcms\promo\Module;
use yii\base\Widget;
use Yii;

/**
 * Class PersonalProfitWidget
 * @package mcms\promo\components\widgets
 */
class PersonalProfitWidget extends Widget {

  private $userId;

  public $options;

  private $queryParams = [];

  private $renderCreateButton = true;

  private $enableSort = true;

  private $enableFilters = true;

  private $renderActions = true;

  private $searchModel;

  private $emptyHeader;

  private $layout;
  private $ignoreIds;

  const DEFAULT_LAYOUT = '{items}';

  public function init()
  {
    $this->userId = ArrayHelper::getValue($this->options, 'userId');
    $this->queryParams = Yii::$app->request->getQueryParams();
    $this->layout = ArrayHelper::getValue($this->options, 'layout', self::DEFAULT_LAYOUT);
    $this->enableSort = ArrayHelper::getValue($this->options, 'enableSort', $this->enableSort);
    $this->renderActions = ArrayHelper::getValue($this->options, 'renderActions', $this->renderActions);
    $this->renderCreateButton = ArrayHelper::getValue($this->options, 'renderCreateButton', $this->renderCreateButton);
    $this->enableFilters = ArrayHelper::getValue($this->options, 'enableFilters', $this->enableFilters);
    $this->ignoreIds = ArrayHelper::getValue($this->options, 'ignoreIds', []);
    $this->emptyHeader = ArrayHelper::getValue($this->options, 'emptyHeader', $this->emptyHeader);
    parent::init();
  }
  /**
   * @inheritDoc
   */
  public function run()
  {
    /** @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');
    if ($this->userId && !$paymentsModule::canUserHaveBalance($this->userId)) {
      return;
    }

    /** @var Module $module */
    $module = Yii::$app->getModule('promo');
    /** @var UserPromoSettings $api */
    $api = $module->api('userPromoSettings');

    return $this->render('@mcms/promo/views/personal-profits/personal_profit', [
      'dataProvider' => $this->getDataProvider(),
      'userId' => $this->userId,
      'renderCreateButton' => $this->renderCreateButton,
      'searchModel' => $this->searchModel,
      'layout' => $this->layout,
      'renderActions' => $this->renderActions,
      'enableSort' => $this->enableSort,
      'enableFilters' => $this->enableFilters,
      'ignoreIds' => $this->ignoreIds,
      'emptyHeader' => $this->emptyHeader,
      'userPartnerProgramId' => $api->getUserPartnerProgramId($this->userId),
      'userPartnerProgramAutosync' => $api->getUserPartnerProgramAutosync($this->userId),
    ]);
  }

  /**
   * @return \yii\data\ActiveDataProvider
   */
  protected function getDataProvider()
  {
    $this->searchModel = (new PersonalProfitSearch([
      'user_id' => $this->userId,
    ]));

    $dataProvider = $this->searchModel->search($this->queryParams);

    return $dataProvider;
  }
}