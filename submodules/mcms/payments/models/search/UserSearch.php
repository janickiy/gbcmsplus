<?php

namespace mcms\payments\models\search;

use Yii;
use yii\base\Model;
use mcms\common\traits\Translate;
use yii\data\ActiveDataProvider;

class UserSearch extends Model
{
  use Translate;

  const LANG_PREFIX = 'payments.user-search.';

  public $username;
  public $permittedRoles;
  public $role;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['username', 'role'], 'string'],
      ['role', 'in', 'range' => $this->permittedRoles],
      [['username', 'role'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return [
      self::SCENARIO_DEFAULT => [
        'username', 'role'
      ]
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'username' => static::translate('username'),
      'role' => static::translate('role'),
    ];
  }

  /**
   *
   * @param array $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $this->load($params);
    $filters = $this->validate() ? [
      ['like', 'username', $this->username],
    ] : [
    ];

    $roles = $this->role ? [$this->role] : $this->permittedRoles;
    $dataProvider = Yii::$app->getModule('users')
      ->api('user', $params)
      ->setResultTypeDataProvider()
      ->search($filters, true, 0, true, $roles);
    $dataProvider->sort = false;

    return $dataProvider;
  }
}