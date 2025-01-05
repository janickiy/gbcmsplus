<?php

namespace mcms\common\widget;

use mcms\user\components\api\User;
use mcms\user\Module;
use Yii;
use mcms\common\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class UserSelect2
 * @package mcms\common\widget
 */
class UserSelect2 extends Select2
{
  const USER_ROW_FORMAT = "#:id: - :email:";

  /** @var string  */
  public $userRowFormat;
  /** @var array  */
  public $roles;
  /** @var array  */
  public $initValueUserId;
  /** @var array  */
  public $ignoreIds;
  /** @var bool */
  public $skipCurrentUser;
  /** @var bool  */
  public $isActiveUsers = false;

  public $showToggleAll = false;
  /**
   * @inheritDoc
   */
  public function __construct($config = [])
  {
    $this->roles = ArrayHelper::getValue($config, 'roles');
    $this->initValueUserId = ArrayHelper::getValue($config, 'initValueUserId');
    $this->userRowFormat = ArrayHelper::getValue($config, 'userRowFormat', self::USER_ROW_FORMAT);
    $this->ignoreIds = ArrayHelper::getValue($config, 'ignoreIds');
    $this->isActiveUsers = ArrayHelper::getValue($config, 'isActiveUsers', false);
    $this->skipCurrentUser = ArrayHelper::getValue($config, 'skipCurrentUser', false);
    $url = ArrayHelper::getValue($config, 'url', ['/users/users/find-user/']);

    unset($config['url']);

    $data = json_encode(array_filter([
      'roles' => $this->roles ? $this->roles : null,
      'format' => $this->userRowFormat,
      'ignoreIds' => $this->ignoreIds ? $this->ignoreIds : null,
      'isActiveUsers' => $this->isActiveUsers,
      'skipCurrentUser' => $this->skipCurrentUser,
    ]));

    $ajaxOptions = [
      'url' => $url,
      'dataType' => 'json',
      'data' => new \yii\web\JsExpression('function(params) {return {q:params.term, data:' . $data . '}; }')
    ];

    $config = ArrayHelper::merge([
      'data' => $this->getInitData($this->initValueUserId),
      'readonly' => true,
      'options' => [
        'placeholder' => ''
      ],
      'pluginOptions' => [
        'allowClear' => true,
        'ajax' => $ajaxOptions
      ]
    ], $config);
    parent::__construct($config);
  }

  /**
   * Если селект находится в модалке, закрываем его вместе с ней
   */
  public function init()
  {
    parent::init();

    $js = <<<JS
    $('.modal').on('hidden.bs.modal', function () {
      $("#{$this->options['id']}").select2("close");
    });
JS;
    $this->view->registerJs($js);
  }

  public static function format($data, $format = UserSelect2::USER_ROW_FORMAT)
  {
    return strtr($format, [
      ':id:' => ArrayHelper::getValue($data, 'id'),
      ':username:' => ArrayHelper::getValue($data, 'username'),
      ':email:' => ArrayHelper::getValue($data, 'email'),
    ]);
  }

  /**
   * @param $id
   * @return array
   */
  private function getInitData($id)
  {
    if (empty($id)) return [];

    /** @var Module $module */
    $module = Yii::$app->getModule('users');
    /** @var User $api */
    $api = $module->api('user', ['skipCurrentUser' => $this->skipCurrentUser]);
    $users = $api->search([
      ['id' => $id]
    ]);

    $mappedUsers = ArrayHelper::map($users, 'id', function($user) {
      return $this->userRowFormat($user);
    });

    return !empty($mappedUsers) ? $mappedUsers : [];
  }

  /**
   * @param $user
   * @return string
   */
  private function userRowFormat($user)
  {
    return static::format([
      'id' => ArrayHelper::getValue($user, 'id'),
      'username' => ArrayHelper::getValue($user, 'username'),
      'email' => ArrayHelper::getValue($user, 'email'),
    ], $this->userRowFormat);
  }
}