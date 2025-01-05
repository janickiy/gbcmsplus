<?php
namespace admin\dashboard\widgets\base;

use admin\dashboard\common\base\BaseBlock;
use mcms\payments\components\api\ExchangerPartnerCourses;
use Yii;

/**
 * Базовый класс для виджетов
 */
abstract class BaseWidget extends BaseBlock
{
  /** @const string Слева. На половину блока */
  const POSITION_LEFT = 'left';
  /** @const Справа. На половину блока */
  const POSITION_RIGHT = 'right';

  /** @var string Позиция по умолчанию */
  public $position = BaseWidget::POSITION_LEFT;
  /** @var bool Отступ в контенте виджета */
  public $padding = true;

  /** @var string Блок автоматически обновляемый Pjax */
  public $pjaxContainer;
  /** @var string URL для обновления Pjax */
  public $pjaxUrl;
  /**
   * @var array предсказания
   */
  private static $predictedData = [];

  private $_canViewRevenue;

  /**
   * @inheritdoc
   */
  public function runInternal()
  {
    $content = $this->getContent();

    if (!$content) {
      return null;
    }

    if ($this->pjaxContainer && $this->pjaxUrl) {
      $js = <<<JS
      $.pjax.reload({container: '#$this->pjaxContainer', timeout: false, url: '$this->pjaxUrl', method: 'post', replace: false});
JS;
      $this->view->registerJs($js);
    }

    return $this->render('@app/dashboard/widgets/base/views/widget', [
      'blockClass' => $this->getBlockClass(),
      'title' => $this->getTitle(),
      'content' => $content,
      'toolbarContent' => $this->getToolbarContent(),
      'padding' => $this->padding,
    ]);
  }

  /**
   * Содержимое тулбара
   * @return string
   */
  public function getToolbarContent()
  {
    return null;
  }

  /**
   * Содержимое виджета
   * @return string
   */
  abstract protected function getContent();

  public function getBlockClass()
  {
    return '';
  }

  /**
   * конвертация курса
   * @param $amount
   * @param $oldCur
   * @param $newCur
   * @return mixed
   */
  protected function convert($amount, $oldCur, $newCur)
  {
    /** @var \mcms\payments\Module $paymentsModule */
    $paymentsModule = Yii::$app->getModule('payments');
    /** @var ExchangerPartnerCourses $currencyConverter */
    $currencyConverter = $paymentsModule->api('exchangerPartnerCourses');

    if ($oldCur == $newCur) {
      return $amount;
    }

    return $currencyConverter->fromCourse($oldCur, $amount)[$newCur];
  }

  /**
   * может ли смотреть доход
   * @return bool
   */
  protected function canViewRevenue()
  {
    if (!isset($this->_canViewRevenue)) {
      $this->_canViewRevenue = $this->getPermissionChecker()->canViewAdminProfit()
        || $this->getPermissionChecker()->canViewResellerProfit();
    }

    return $this->_canViewRevenue;
  }

  /**
   * Получение прогноза на сегодняшний день
   * @param array $models
   * @return mixed
   */
  protected function getPredictedStatToday($models)
  {
    return Yii::$app->getModule('statistic')->api('predictedStatToday', [
      'models' => $models
    ])->getResult();
  }
}