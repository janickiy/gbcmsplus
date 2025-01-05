<?php
namespace mcms\common\widget\alert;


use mcms\common\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * Class Alert
 * @package mcms\common\widget\alert
 *
 * @method static string success(string $title, string $body = '', int $timeout = 4000)
 * @method static string danger(string $title, string $body = '', int $timeout = 4000)
 * @method static string info(string $title, string $body = '', int $timeout = 4000)
 * @method static string warning(string $title, string $body = '', int $timeout = 4000)
 */
class Alert extends Widget
{
  const TYPE_INFO = 'info';
  const TYPE_DANGER = 'danger';
  const TYPE_SUCCESS = 'success';
  const TYPE_WARNING = 'warning';

  public $type = self::TYPE_INFO;

  public $title = '';

  public $timeout = 4000;

  public $body = '';

  public $sound = false;

  public $icon = '';

  public $useSmallIcon = true;
  public $colortime = '';
  public $colors = '';

  protected $_colors = [
    self::TYPE_INFO => 'rgb(50, 118, 177)',
    self::TYPE_DANGER => 'rgb(196, 106, 105)',
    self::TYPE_SUCCESS => 'rgb(115, 158, 115)',
    self::TYPE_WARNING => 'rgb(199, 145, 33)',
  ];

  protected $_icons = [
    self::TYPE_INFO => 'fa fa-bell swing animated',
    self::TYPE_DANGER => 'miniPic fa fa-warning shake animated',
    self::TYPE_SUCCESS => 'miniPic fa fa-check-circle bounce animated',
    self::TYPE_WARNING => 'fa fa-exclamation-triangle fadeInLeft animated',
  ];

  public function init()
  {
    parent::init();
    AlertAsset::register($this->view);
  }

  public function run()
  {
    $this->view->registerJs($this->renderJs());
  }

  public static function __callStatic($name, $args)
  {
    if (!in_array($name, [self::TYPE_WARNING, self::TYPE_SUCCESS, self::TYPE_DANGER, self::TYPE_INFO])) {
      return false;
    }
    if (empty($args)) {
      throw new InvalidConfigException();
    }

    $timeout = ArrayHelper::getValue($args, 2);

    $options['title'] = ArrayHelper::getValue($args, 0);
    $options['body'] = ArrayHelper::getValue($args, 1);
    $timeout && $options['timeout'] = $timeout;
    $options['type'] = $name;

    return static::getScript($options);
  }

  protected function renderJs()
  {
    $options = [
      'color' => $this->color,
    ];

    $this->title && $options['title'] = $this->title;
    $this->body && $options['content'] = $this->body;
    $this->timeout && $options['timeout'] = $this->timeout;
    $options['sound'] = $this->sound;
    $this->colortime && $options['colortime'] = $this->colortime;
    $this->colors && $options['colors'] = $this->colors;

    if ($this->useSmallIcon) {
      $options['iconSmall'] = $this->icon ?: $this->getIcon();
    } else {
      $options['icon'] = $this->icon ?: $this->getIcon();
    }

    $options = Json::encode($options);

    return new JsExpression("$.smallBox($options);");
  }

  protected function getColor()
  {
    return $this->_colors[$this->type];
  }

  protected function getIcon()
  {
    return $this->_icons[$this->type];
  }

  public function getJs()
  {
    return $this->renderJs();
  }

  /**
   * @param $options
   * @return string
   */
  public static function getScript(array $options)
  {
    $options['class'] = static::class;

    return \Yii::createObject($options)->js;
  }
}